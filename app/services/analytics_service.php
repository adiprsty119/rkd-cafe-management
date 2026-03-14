<?php

/* ==========================
   BASE CONFIG
========================== */

function analyticsApiUrl($endpoint, $params = [])
{
    $url = "http://localhost:5001/" . $endpoint;

    if (!empty($params)) {
        $url .= "?" . http_build_query($params);
    }

    return $url;
}


/* ==========================
   GENERIC API FETCH
========================== */

function fetchAnalyticsAPI($endpoint, $params = [])
{

    $url = analyticsApiUrl($endpoint, $params);

    $ch = curl_init();

    curl_setopt_array($ch, [

        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,

    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {

        error_log("Analytics API Curl Error: " . $error);

        return [];
    }

    if ($httpCode !== 200) {

        error_log("Analytics API HTTP Error: " . $httpCode);

        return [];
    }

    $data = json_decode($response, true);

    return $data ?? [];
}


/* ==========================
   DASHBOARD KPI
========================== */

function getDashboardAnalytics($period = "today")
{
    return fetchAnalyticsAPI("analytics", ["period" => $period]);
}


/* ==========================
   TOP MENU
========================== */

function getTopMenu($period = "today")
{
    return fetchAnalyticsAPI("top-menu", ["period" => $period]);
}


/* ==========================
   SALES TREND
========================== */

function getSalesTrend($period = "today")
{
    return fetchAnalyticsAPI("sales-trend", ["period" => $period]);
}


/* ==========================
   CUSTOMER INSIGHT
========================== */

function getCustomerInsight($period = "today")
{
    return fetchAnalyticsAPI("customer-insight", ["period" => $period]);
}


/* ==========================
   PRODUCT PROFIT
========================== */

function getProductProfit($period = "today")
{
    return fetchAnalyticsAPI("product-profit", ["period" => $period]);
}


/* ==========================
   SALES PREDICTION
========================== */

function getSalesPrediction($period = "today")
{
    return fetchAnalyticsAPI("sales-prediction", ["period" => $period]);
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

function getPaymentDistribution($period = "today")
{
    return fetchAnalyticsAPI("payment-distribution", ["period" => $period]);
}


/* ==========================
   CUSTOMER GROWTH
========================== */

function getCustomerGrowth($period = "today")
{
    return fetchAnalyticsAPI("customer-growth", ["period" => $period]);
}


/* ==========================
   CUSTOMER LIFETIME
========================== */

function getCustomerLifetime()
{
    return fetchAnalyticsAPI("customer-lifetime");
}
