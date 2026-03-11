<?php

function fetchAnalyticsAPI($endpoint)
{

    $url = "http://localhost:5001/" . $endpoint;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    curl_close($ch);

    return json_decode($response, true);
}


/* ==========================
   DASHBOARD METRICS
========================== */

function getDashboardAnalytics()
{
    return fetchAnalyticsAPI("analytics");
}


/* ==========================
   TOP MENU
========================== */

function getTopMenu()
{
    return fetchAnalyticsAPI("top-menu");
}


/* ==========================
   SALES TREND
========================== */

function getSalesTrend()
{
    return fetchAnalyticsAPI("sales-trend");
}


/* ==========================
   CUSTOMER INSIGHT
========================== */

function getCustomerInsight()
{
    return fetchAnalyticsAPI("customer-insight");
}


/* ==========================
   PRODUCT PROFIT
========================== */

function getProductProfit()
{
    return fetchAnalyticsAPI("product-profit");
}


/* ==========================
   SALES PREDICTION
========================== */

function getSalesPrediction()
{
    return fetchAnalyticsAPI("sales-prediction");
}


/* ==========================
   SALES HOURLY
========================== */

function getSalesHourly()
{
    return fetchAnalyticsAPI("sales-hourly");
}


/* ==========================
   SALES DAILY
========================== */

function getSalesDaily()
{
    return fetchAnalyticsAPI("sales-daily");
}


/* ==========================
   PAYMENT DISTRIBUTION
========================== */

function getPaymentDistribution()
{
    return fetchAnalyticsAPI("payment-distribution");
}


/* ==========================
   CUSTOMER GROWTH
========================== */

function getCustomerGrowth()
{
    return fetchAnalyticsAPI("customer-growth");
}


/* ==========================
   CUSTOMER LIFETIME VALUE
========================== */

function getCustomerLifetime()
{
    return fetchAnalyticsAPI("customer-lifetime");
}
