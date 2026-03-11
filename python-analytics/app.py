from flask import Flask, jsonify
from analytics_engine import (
    get_dashboard_data,
    get_top_menu,
    get_sales_trend,
    get_customer_insight,
    get_product_profit,
    get_sales_prediction,
)

app = Flask(__name__)


@app.route("/analytics")
def analytics():

    data = get_dashboard_data()

    return jsonify(data)


@app.route("/top-menu")
def top_menu():

    data = get_top_menu()

    return jsonify(data)


@app.route("/sales-trend")
def sales_trend():

    data = get_sales_trend()

    return jsonify(data)


@app.route("/customer-insight")
def customer_insight():

    data = get_customer_insight()

    return jsonify(data)


@app.route("/product-profit")
def product_profit():

    data = get_product_profit()

    return jsonify(data)


@app.route("/sales-prediction")
def sales_prediction():

    data = get_sales_prediction()

    return jsonify(data)


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5001, debug=True)
