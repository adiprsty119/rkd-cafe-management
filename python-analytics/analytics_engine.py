import mysql.connector
import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression


def get_connection():

    return mysql.connector.connect(
        host="localhost", user="root", password="", database="rkd_cafe"
    )


# =========================
# DASHBOARD ANALYTICS
# =========================


def get_dashboard_data():

    conn = get_connection()

    query = """
    SELECT total, user_id
    FROM orders
    WHERE status = 'paid'
    """

    df = pd.read_sql(query, conn)

    if df.empty:

        conn.close()

        return {
            "total_revenue": 0,
            "total_orders": 0,
            "active_customers": 0,
            "avg_order": 0,
        }

    total_revenue = df["total"].sum()
    total_orders = len(df)
    avg_order = df["total"].mean()
    active_customers = df["user_id"].nunique()

    conn.close()

    return {
        "total_revenue": int(total_revenue),
        "total_orders": int(total_orders),
        "active_customers": int(active_customers),
        "avg_order": int(avg_order),
    }


# =========================
# TOP SELLING PRODUCTS
# =========================


def get_top_menu():

    conn = get_connection()

    query = """
    SELECT 
        p.name,
        SUM(oi.qty) AS orders,
        SUM(oi.subtotal) AS revenue
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status = 'paid'
    GROUP BY oi.product_id
    ORDER BY orders DESC
    LIMIT 5
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")


# =========================
# SALES TREND
# =========================


def get_sales_trend():

    conn = get_connection()

    query = """
    SELECT 
    DATE(created_at) as date,
    SUM(total) as revenue
    FROM orders
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")


# =========================
# CUSTOMER INSIGHT
# =========================


def get_customer_insight():

    conn = get_connection()

    query = """
    SELECT 
        u.name,
        COUNT(o.id) AS orders,
        SUM(o.total) AS total_spend
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.status = 'paid'
    GROUP BY o.user_id
    ORDER BY total_spend DESC
    LIMIT 5
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")


# =========================
# PRODUCT PROFIT ANALYSIS
# =========================


def get_product_profit():

    conn = get_connection()

    query = """
    SELECT 
        p.name,
        SUM(oi.subtotal) AS revenue,
        SUM(p.cost * oi.qty) AS cost,
        SUM(oi.subtotal - (p.cost * oi.qty)) AS profit
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status = 'paid'
    GROUP BY oi.product_id
    ORDER BY profit DESC
    LIMIT 5
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")


# =========================
# SALES PREDICTION (AI)
# =========================


def get_sales_prediction():

    conn = get_connection()

    query = """
    SELECT 
        DATE(created_at) as date,
        SUM(total) as revenue
    FROM orders
    WHERE status='paid'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
    """

    df = pd.read_sql(query, conn)

    conn.close()

    if df.empty:
        return []

    df["date"] = pd.to_datetime(df["date"])

    df["day_index"] = np.arange(len(df))

    X = df[["day_index"]]
    y = df["revenue"]

    model = LinearRegression()

    model.fit(X, y)

    future_days = 7

    last_index = df["day_index"].max()

    future_index = np.arange(last_index + 1, last_index + future_days + 1).reshape(
        -1, 1
    )

    predictions = model.predict(future_index)

    future_dates = pd.date_range(df["date"].max(), periods=future_days + 1)[1:]

    result = []

    for i in range(future_days):

        result.append(
            {
                "date": future_dates[i].strftime("%Y-%m-%d"),
                "revenue": int(predictions[i]),
            }
        )

    return result


# =========================
# SALES HOURLY
# =========================


def get_sales_hourly():

    conn = get_connection()

    query = """
    SELECT 
        HOUR(created_at) AS hour,
        COUNT(id) AS orders,
        SUM(total) AS revenue
    FROM orders
    WHERE status='paid'
    GROUP BY HOUR(created_at)
    ORDER BY hour
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")


# =========================
# SALES DAILY
# =========================


def get_sales_daily():

    conn = get_connection()

    query = """
    SELECT 
        DAYNAME(created_at) AS day,
        COUNT(id) AS orders,
        SUM(total) AS revenue
    FROM orders
    WHERE status='paid'
    GROUP BY DAYNAME(created_at)
    ORDER BY FIELD(
        DAYNAME(created_at),
        'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'
    )
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")


# =========================
# PAYMENT DISTRIBUTION
# =========================


def get_payment_distribution():

    conn = get_connection()

    query = """
    SELECT 
        payment_method,
        COUNT(id) AS orders,
        SUM(total) AS revenue
    FROM orders
    WHERE status='paid'
    GROUP BY payment_method
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")


# =========================
# CUSTOMER GROWTH
# =========================


def get_customer_growth():

    conn = get_connection()

    query = """
    SELECT 
        DATE(created_at) AS date,
        COUNT(id) AS new_customers
    FROM users
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")


# =========================
# CUSTOMER LIFETIME VALUE
# =========================


def get_customer_lifetime():

    conn = get_connection()

    query = """
    SELECT 
        u.name,
        COUNT(o.id) AS orders,
        SUM(o.total) AS total_spend
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.status = 'paid'
    GROUP BY o.user_id
    ORDER BY total_spend DESC
    LIMIT 10
    """

    df = pd.read_sql(query, conn)

    conn.close()

    return df.to_dict(orient="records")
