from flask import Flask, jsonify, request
import pandas as pd
from performance_analytics_engine import (
    get_dashboard_data,
    get_top_menu,
    get_sales_trend,
    get_customer_insight,
    get_product_profit,
    get_sales_prediction,
    get_sales_hourly,
    get_sales_daily,
    get_payment_distribution,
    get_customer_growth,
    get_customer_lifetime,
    generate_business_insight,
)

app = Flask(__name__)


# =========================
# HELPER
# =========================


def get_period():
    """
    Ambil parameter period dari request.
    Default: today
    """
    return request.args.get("period", "today")


def response(data):
    """
    Standard JSON response
    """
    return jsonify(data)


# =========================
# DASHBOARD KPI
# =========================


@app.route("/analytics")
def analytics():

    period = get_period()

    data = get_dashboard_data(period)

    return response(data)


# =========================
# TOP MENU
# =========================


@app.route("/top-menu")
def top_menu():

    period = get_period()

    data = get_top_menu(period)

    return response(data)


# =========================
# SALES TREND
# =========================


@app.route("/sales-trend")
def sales_trend():

    period = get_period()

    data = get_sales_trend(period)

    return response(data)


# =========================
# CUSTOMER INSIGHT
# =========================


@app.route("/customer-insight")
def customer_insight():

    period = get_period()

    data = get_customer_insight(period)

    return response(data)


# =========================
# PRODUCT PROFIT
# =========================


@app.route("/product-profit")
def product_profit():

    period = get_period()

    data = get_product_profit(period)

    return response(data)


# =========================
# PAYMENT DISTRIBUTION
# =========================


@app.route("/payment-distribution")
def payment_distribution():

    period = get_period()

    data = get_payment_distribution(period)

    return response(data)


# =========================
# CUSTOMER GROWTH
# =========================


@app.route("/customer-growth")
def customer_growth():

    period = request.args.get("period", "7days")

    data = get_customer_growth(period)

    return jsonify(data)


# =========================
# SALES PREDICTION (AI)
# =========================


@app.route("/sales-prediction")
def sales_prediction():

    period = get_period()

    data = get_sales_prediction(period)

    return response(data)


# =========================
# SALES HOURLY
# =========================


@app.route("/sales-hourly")
def sales_hourly():

    data = get_sales_hourly()

    return response(data)


# =========================
# SALES DAILY
# =========================


@app.route("/sales-daily")
def sales_daily():

    data = get_sales_daily()

    return response(data)


# =========================
# CUSTOMER LIFETIME VALUE
# =========================


@app.route("/customer-lifetime")
def customer_lifetime():

    data = get_customer_lifetime()

    return response(data)


@app.route("/business-insight")
def business_insight():

    period = request.args.get("period", "today")

    data = generate_business_insight(period)

    return jsonify(
        {
            "insights": data.get("insights", []),
            "generated_at": pd.Timestamp.now().strftime("%Y-%m-%d %H:%M:%S"),
            "engine": "RKD AI Analytics v2",
        }
    )


# =========================
# RUN SERVER
# =========================

if __name__ == "__main__":

    app.run(host="127.0.0.1", port=5001, debug=True)
