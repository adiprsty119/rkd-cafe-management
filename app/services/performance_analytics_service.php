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
   NORMALIZE ANALYTICS
========================== */

function normalizeAnalytics($response)
{
    return [
        "data" => $response["data"] ?? [],
        "insight" => $response["insight"] ?? ""
    ];
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
    $response = fetchAnalyticsAPI("sales-trend", ["period" => $period]);
    return normalizeAnalytics($response);
}


/* ==========================
   CUSTOMER INSIGHT
========================== */

function getCustomerInsight($period = "today")
{
    $response = fetchAnalyticsAPI("customer-insight", ["period" => $period]);
    return normalizeAnalytics($response);
}


/* ==========================
   PRODUCT PROFIT
========================== */

function getProductProfit($period = "today")
{
    $response = fetchAnalyticsAPI("product-profit", ["period" => $period]);
    return normalizeAnalytics($response);
}


/* ==========================
   SALES PREDICTION
========================== */

function getSalesPrediction($period = "today")
{
    $response = fetchAnalyticsAPI("sales-prediction", ["period" => $period]);
    return normalizeAnalytics($response);
}


/* ==========================
   PAYMENT DISTRIBUTION
========================== */

function getPaymentDistribution($period = "today")
{
    $response = fetchAnalyticsAPI("payment-distribution", ["period" => $period]);
    return normalizeAnalytics($response);
}


/* ==========================
   CUSTOMER GROWTH
========================== */

function getCustomerGrowth($period = "today")
{
    $response = fetchAnalyticsAPI("customer-growth", ["period" => $period]);
    return normalizeAnalytics($response);
}


/* ==========================
   BUSINESS INSIGHT
========================== */

function getBusinessInsight($period = "today")
{
    $response = fetchAnalyticsAPI("business-insight", ["period" => $period]);

    return [
        "insights" => $response["insights"] ?? [],
        "generated_at" => $response["generated_at"] ?? null,
        "engine" => $response["engine"] ?? null
    ];
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
   CUSTOMER LIFETIME
========================== */

function getCustomerLifetime()
{
    return fetchAnalyticsAPI("customer-lifetime");
}
