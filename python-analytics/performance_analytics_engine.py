import mysql.connector
import pandas as pd
import numpy as np

from statsmodels.tsa.seasonal import seasonal_decompose
from sklearn.linear_model import LinearRegression


# =========================
# DATABASE CONNECTION
# =========================


def get_connection():

    return mysql.connector.connect(
        host="localhost", user="root", password="", database="rkd_cafe"
    )


# =========================
# PERIOD HELPER
# =========================


def get_days(period):

    mapping = {"today": 1, "7days": 7, "30days": 30}

    return mapping.get(period, 1)


# =========================
# QUERY HELPER
# =========================


def query_dataframe(query):

    conn = get_connection()

    df = pd.read_sql(query, conn)

    conn.close()

    return df


# =========================
# FILL MISSING DATE
# =========================


def fill_missing_dates(df):

    if df.empty:
        return df

    df["date"] = pd.to_datetime(df["date"])

    df = df.set_index("date").asfreq("D", fill_value=0).reset_index()

    return df


# =========================
# DASHBOARD KPI
# =========================


def get_dashboard_data(period="today"):

    days = get_days(period)

    query = f"""
    SELECT total, user_id
    FROM orders
    WHERE status='paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    """

    df = query_dataframe(query)

    if df.empty:

        return {
            "total_revenue": 0,
            "total_orders": 0,
            "active_customers": 0,
            "avg_order": 0,
        }

    return {
        "total_revenue": int(df["total"].sum()),
        "total_orders": int(len(df)),
        "active_customers": int(df["user_id"].nunique()),
        "avg_order": int(df["total"].mean()),
    }


# =========================
# SALES TREND
# =========================


def get_sales_trend(period="today"):

    days = get_days(period)

    query = f"""
    SELECT 
        DATE(created_at) as date,
        SUM(total) as revenue
    FROM orders
    WHERE status='paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
    """

    df = query_dataframe(query)
    df = fill_missing_dates(df)

    # generate insight
    insight = generate_sales_insight(df)

    # FIX FORMAT DATE
    df["date"] = pd.to_datetime(df["date"]).dt.strftime("%Y-%m-%d")

    return {"data": df.to_dict(orient="records"), "insight": insight}


# =========================
# CUSTOMER INSIGHT
# =========================


def get_customer_insight(period="today"):

    days = get_days(period)

    query = f"""
    SELECT 
        u.name,
        COUNT(o.id) AS orders,
        SUM(o.total) AS total_spend
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.status='paid'
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY o.user_id
    ORDER BY total_spend DESC
    LIMIT 5
    """

    df = query_dataframe(query)

    insight = generate_customer_insight(df)

    return {"data": df.to_dict(orient="records"), "insight": insight}


# =========================
# PRODUCT PROFIT
# =========================


def get_product_profit(period="today"):

    days = get_days(period)

    query = f"""
    SELECT 
        p.name,
        SUM(oi.subtotal) AS revenue,
        SUM(p.cost * oi.qty) AS cost,
        SUM(oi.subtotal - (p.cost * oi.qty)) AS profit
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status='paid'
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY oi.product_id
    ORDER BY profit DESC
    LIMIT 5
    """

    df = query_dataframe(query)

    insight = generate_profit_insight(df)

    return {"data": df.to_dict(orient="records"), "insight": insight}


# =========================
# PAYMENT DISTRIBUTION
# =========================


def get_payment_distribution(period="today"):

    days = get_days(period)

    query = f"""
    SELECT 
        payment_method,
        COUNT(id) AS orders,
        SUM(total) AS revenue
    FROM orders
    WHERE status='paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY payment_method
    """

    df = query_dataframe(query)

    insight = generate_payment_insight(df)

    return {"data": df.to_dict(orient="records"), "insight": insight}


# =========================
# CUSTOMER GROWTH
# =========================


def get_customer_growth(period="7days"):

    days = get_days(period)

    query = f"""
    SELECT 
        DATE(created_at) AS date,
        COUNT(id) AS new_customers
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
    """

    df = query_dataframe(query)

    if df.empty:
        return {"data": [], "insight": "Belum ada pertumbuhan pelanggan."}

    df = fill_missing_dates(df)

    insight = generate_growth_insight(df)

    df["date"] = pd.to_datetime(df["date"]).dt.strftime("%Y-%m-%d")

    return {"data": df.to_dict(orient="records"), "insight": insight}


# =========================
# TOP MENU
# =========================


def get_top_menu(period="today"):

    days = get_days(period)

    query = f"""
    SELECT 
        p.name,
        SUM(oi.qty) AS orders,
        SUM(oi.subtotal) AS revenue
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status='paid'
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY oi.product_id
    ORDER BY orders DESC
    LIMIT 5
    """

    df = query_dataframe(query)

    return df.to_dict(orient="records")


# =========================
# SALES PREDICTION INSIGHT
# =========================


def generate_prediction_insight(forecast):

    if not forecast:
        return "Belum cukup data untuk melakukan prediksi."

    first = forecast[0]
    last = forecast[-1]

    if last > first:
        return "Prediksi menunjukkan tren peningkatan penjualan."

    if last < first:
        return "Prediksi menunjukkan kemungkinan penurunan penjualan."

    return "Prediksi menunjukkan tren penjualan yang stabil."


# =========================
# SALES PREDICTION (AI)
# Seasonal Decomposition
# =========================


def get_sales_prediction(period="30days"):

    days = get_days(period)

    query = f"""
    SELECT 
        DATE(created_at) as date,
        SUM(total) as revenue
    FROM orders
    WHERE status='paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
    """

    df = query_dataframe(query)

    if df.empty:
        return []

    df = fill_missing_dates(df)

    df["date"] = pd.to_datetime(df["date"])
    df = df.sort_values("date")
    df = df.set_index("date")

    revenue = df["revenue"]

    history_len = len(revenue)

    future_days = 7

    # =========================
    # CASE 1 : DATA SANGAT SEDIKIT
    # =========================
    if history_len <= 2:

        last_value = int(revenue.iloc[-1])

        forecast = [last_value] * future_days

    # =========================
    # CASE 2 : DATA KECIL
    # Moving Average
    # =========================
    elif history_len < 7:

        avg = int(revenue.mean())

        forecast = [avg] * future_days

    # =========================
    # CASE 3 : DATA MENENGAH
    # Linear Regression
    # =========================
    elif history_len < 14:

        X = np.arange(history_len).reshape(-1, 1)

        model = LinearRegression()
        model.fit(X, revenue.values)

        future_index = np.arange(history_len, history_len + future_days).reshape(-1, 1)

        forecast = model.predict(future_index)
        forecast = [max(0, int(v)) for v in forecast]

    # =========================
    # CASE 4 : DATA BESAR
    # Seasonal Decomposition
    # =========================
    else:

        period = 7

        decomposition = seasonal_decompose(revenue, model="additive", period=period)

        trend = decomposition.trend.bfill().ffill().dropna()

        X = np.arange(len(trend)).reshape(-1, 1)

        model = LinearRegression()
        model.fit(X, trend)

        future_index = np.arange(len(trend), len(trend) + future_days).reshape(-1, 1)

        forecast_trend = model.predict(future_index)

        seasonal_pattern = decomposition.seasonal.tail(period)

        forecast = []

        for i in range(future_days):

            seasonal_value = seasonal_pattern.iloc[i % period]

            predicted = forecast_trend[i] + seasonal_value

            forecast.append(max(0, int(predicted)))

    # =========================
    # BUILD RESULT
    # =========================

    last_date = df.index.max()

    future_dates = pd.date_range(last_date, periods=future_days + 1)[1:]

    result = []

    for i in range(future_days):

        result.append(
            {"date": future_dates[i].strftime("%Y-%m-%d"), "revenue": forecast[i]}
        )

    insight = generate_prediction_insight(forecast)

    return {"data": result, "insight": insight}


# =========================
# SALES HOURLY
# =========================


def get_sales_hourly(period="today"):

    days = get_days(period)

    query = f"""
    SELECT 
        HOUR(created_at) AS hour,
        COUNT(id) AS orders,
        SUM(total) AS revenue
    FROM orders
    WHERE status='paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY HOUR(created_at)
    ORDER BY hour
    """

    df = query_dataframe(query)

    return df.to_dict(orient="records")


# =========================
# SALES DAILY
# =========================


def get_sales_daily(period="today"):

    days = get_days(period)

    query = f"""
    SELECT 
        DAYNAME(created_at) AS day,
        COUNT(id) AS orders,
        SUM(total) AS revenue
    FROM orders
    WHERE status='paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY DAYNAME(created_at)
    ORDER BY FIELD(
        DAYNAME(created_at),
        'Monday','Tuesday','Wednesday','Thursday',
        'Friday','Saturday','Sunday'
    )
    """

    df = query_dataframe(query)

    return df.to_dict(orient="records")


# =========================
# CUSTOMER LIFETIME
# =========================


def get_customer_lifetime(period="today"):

    days = get_days(period)

    query = f"""
    SELECT 
        u.name,
        COUNT(o.id) AS orders,
        SUM(o.total) AS total_spend
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.status='paid'
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL {days} DAY)
    GROUP BY o.user_id
    ORDER BY total_spend DESC
    LIMIT 10
    """

    df = query_dataframe(query)

    return df.to_dict(orient="records")


# =========================
# SALES INSIGHT
# =========================


def generate_sales_insight(df):

    if df.empty:
        return "Belum ada data penjualan untuk dianalisis."

    total = df["revenue"].sum()

    first = df["revenue"].iloc[0]
    last = df["revenue"].iloc[-1]

    growth = trend_percentage(first, last)

    avg_daily = int(df["revenue"].mean())

    peak_day = df.loc[df["revenue"].idxmax()]

    peak_date = pd.to_datetime(peak_day["date"]).strftime("%d %b %Y")

    if growth > 10:
        trend_text = f"Revenue meningkat {growth}% dalam periode ini."
    elif growth < -10:
        trend_text = f"Revenue menurun {abs(growth)}% dibanding awal periode."
    else:
        trend_text = "Revenue relatif stabil dalam periode ini."

    return (
        f"{trend_text} "
        f"Total revenue mencapai {rupiah(total)}. "
        f"Rata-rata penjualan harian sekitar {rupiah(avg_daily)}."
        f"Puncak penjualan terjadi pada {peak_date}."
    )


# =========================
# PRODUCT PROFIT INSIGHT
# =========================


def generate_profit_insight(df):

    if df.empty:
        return "Belum ada data profit produk."

    total_profit = df["profit"].sum()

    top = df.iloc[0]

    share = percent(top["profit"], total_profit)

    return (
        f"{top['name']} menghasilkan profit tertinggi "
        f"dengan kontribusi {share}% dari total profit produk."
    )


# =========================
# PAYMENT INSIGHT
# =========================


def generate_payment_insight(df):

    if df.empty:
        return "Belum ada data pembayaran."

    total_orders = df["orders"].sum()

    top = df.sort_values("orders", ascending=False).iloc[0]

    share = percent(top["orders"], total_orders)

    return f"{top['payment_method']} mendominasi {share}% transaksi pembayaran."


# =========================
# CUSTOMER INSIGHT
# =========================


def generate_customer_insight(df):

    if df.empty:
        return "Belum ada aktivitas pelanggan."

    top = df.iloc[0]

    return (
        f"{top['name']} merupakan pelanggan paling aktif "
        f"dengan {top['orders']} transaksi dan total belanja Rp{int(top['total_spend']):,}."
    )


# =========================
# CUSTOMER GROWTH INSIGHT
# =========================


def generate_growth_insight(df):

    if df.empty:
        return "Belum ada pertumbuhan pelanggan."

    total = int(df["new_customers"].sum())

    peak = df.loc[df["new_customers"].idxmax()]

    peak_date = pd.to_datetime(peak["date"]).strftime("%d %b %Y")

    return (
        f"Dalam periode ini terdapat {total} pelanggan baru. "
        f"Puncak pendaftaran terjadi pada {peak_date} "
        f"dengan {peak['new_customers']} pelanggan."
    )


# =========================
# HELPER ANALYTICS
# =========================


def percent(part, whole):
    if whole == 0:
        return 0
    return round((part / whole) * 100, 1)


def trend_percentage(first, last):
    if first == 0:
        return 0
    return round(((last - first) / first) * 100, 1)


def rupiah(n):
    return "Rp {:,}".format(int(n)).replace(",", ".")


# =========================
# BUSINESS INSIGHT SUMMARY
# =========================


def generate_business_insight(period="today"):

    insights = []

    sales = get_sales_trend(period)
    profit = get_product_profit(period)
    payment = get_payment_distribution(period)
    growth = get_customer_growth(period)

    for src in [sales, profit, payment, growth]:

        text = src.get("insight")

        if text and text not in insights:
            insights.append(text)

    return {"insights": insights}


# =========================
# HOURLY INSIGHT
# =========================
def generate_hourly_insight(df):

    if df.empty:
        return None

    peak = df.loc[df["orders"].idxmax()]

    hour = int(peak["hour"])

    return f"Puncak transaksi terjadi pada jam {hour:02d}:00 dengan {peak['orders']} transaksi."
