<?php

require $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/app/services/analytics_service.php';


/* ==========================
   PERIOD PARAMETER
========================== */

$period = $_GET['period'] ?? 'today';


/* ==========================
   WHITELIST SECURITY
========================== */

$allowed = ['today', '7days', '30days'];

if (!in_array($period, $allowed)) {
   $period = 'today';
}


/* ==========================
   FETCH ANALYTICS DATA
========================== */

$data = [

   "kpi" => getDashboardAnalytics($period),
   "salesTrend" => getSalesTrend($period),
   "customerInsight" => getCustomerInsight($period),
   "productProfit" => getProductProfit($period),
   "salesPrediction" => getSalesPrediction($period),
   "paymentDistribution" => getPaymentDistribution($period),
   "customerGrowth" => getCustomerGrowth($period),
   "businessInsight" => getBusinessInsight($period)

];


/* ==========================
   JSON RESPONSE
========================== */

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

echo json_encode(
   $data,
   JSON_UNESCAPED_UNICODE |
      JSON_NUMERIC_CHECK
);
