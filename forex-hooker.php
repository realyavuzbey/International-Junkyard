<?php
header('Content-Type: application/json');

if (!isset($_GET['year']) || !isset($_GET['month'])) {
    echo json_encode(['status' => false, 'error' => 'Invalid year or month', 'type' => $_SERVER['REQUEST_METHOD']]);
    exit;
}

$year = (int)$_GET['year'];
$month = str_pad((int)$_GET['month'], 2, '0', STR_PAD_LEFT);

$countries = [
    'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'SEK', 'NZD',
    'DKK', 'NOK', 'RUB', 'ZAR', 'BRL', 'IDR', 'INR', 'MXN', 'PLN', 'RON', 'SAR',
    'SGD', 'KRW', 'HUF', 'CZK', 'MYR', 'THB', 'PHP', 'HKD', 'BGN', 'IRR', 'PKR',
    'QAR', 'AZN', 'AED', 'KWD'
];
$forexBuyingRates = array_fill_keys($countries, null);
$forexSellingRates = array_fill_keys($countries, null);
$requestSuccessful = false;

$multiCurl = curl_multi_init();
$curlHandles = [];
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

foreach (range(1, $daysInMonth) as $day) {
    $dayPadded = str_pad($day, 2, '0', STR_PAD_LEFT);
    $url = "https://www.tcmb.gov.tr/kurlar/$year$month/$dayPadded$month$year.xml";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $curlHandles[$dayPadded] = $ch;
    curl_multi_add_handle($multiCurl, $ch);
}

do {
    $status = curl_multi_exec($multiCurl, $active);
    curl_multi_select($multiCurl);
} while ($active && $status == CURLM_OK);

foreach ($curlHandles as $day => $ch) {
    $xmlContent = curl_multi_getcontent($ch);
    curl_multi_remove_handle($multiCurl, $ch);
    curl_close($ch);

    if ($xmlContent !== FALSE && strpos($xmlContent, '<?xml') !== FALSE) {
        try {
            $xml = new SimpleXMLElement($xmlContent);

            foreach ($xml->Currency as $currency) {
                $currencyCode = (string)$currency['CurrencyCode'];
                $buyingRate = (float)$currency->ForexBuying;
                $sellingRate = (float)$currency->ForexSelling;

                if (in_array($currencyCode, $countries)) {
                    if ($forexBuyingRates[$currencyCode] === null || $buyingRate > $forexBuyingRates[$currencyCode]) {
                        $forexBuyingRates[$currencyCode] = $buyingRate;
                    }
                    if ($forexSellingRates[$currencyCode] === null || $sellingRate > $forexSellingRates[$currencyCode]) {
                        $forexSellingRates[$currencyCode] = $sellingRate;
                    }
                    $requestSuccessful = true;
                }
            }
        } catch (Exception $e) {
            continue;
        }
    } else {
        error_log("Invalid or empty XML content for date: $year-$month-$dayPadded");
    }
}

curl_multi_close($multiCurl);

$response = [
    'status' => $requestSuccessful,
    'type' => $_SERVER['REQUEST_METHOD'],
    'forex_buying' => $forexBuyingRates,
    'forex_selling' => $forexSellingRates
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
