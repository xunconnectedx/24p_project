<?php
require_once('VirusTotalApiV2.php');
//e4b1660e6c71e69ae8266510158b59e66884e9919cb805c3e39ba2ada9a96d50
/* Initialize the VirusTotalApi class. */
$api = new VirusTotalAPIV2('e4b1660e6c71e69ae8266510158b59e66884e9919cb805c3e39ba2ada9a96d50');

/* Scan an URL. */
$result = $api->scanURL($url);
$scanId = $api->getScanID($result); /* Can be used to check for the report later on. */
$api->displayResult($result);

/* Get an URL report. */
$report = $api->getURLReport('URL-to-check-for-a-report');
$api->displayResult($report);
print($api->getTotalNumberOfChecks($report) . '<br>');
print($api->getNumberHits($report) . '<br>');
print($api->getReportPermalink($report, FALSE) . '<br>');
?>