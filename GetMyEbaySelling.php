<?php
require_once('includes/config.php');
$time = microtime(true);
$apiCall = 'GetMyeBaySelling';

//trace and debugging
//$client = new SOAPClient($wsdl_file, array('trace' => 1, 'exceptions' => true, 'location' => 'https://api.sandbox.ebay.com/wsapi?callname=' . $apiCall . '&appid=' . $appId . '&siteid='. $site_id .'&version='. $compat_level .'&routing=new'));

$clientGet = new SOAPClient($wsdl_file, array('location' => $api_url . '/wsapi?callname=' . $apiCall . '&appid=' . $app_id . '&siteid='. $site_id .'&version='. $compat_level .'&routing=new'));

$requesterCredentials = new stdClass();
$requesterCredentials->eBayAuthToken = $auth_token;

$headerGet = new SoapHeader('urn:ebay:apis:eBLBaseComponents', 'RequesterCredentials', $requesterCredentials);

// the API call parameters
$params = array(
    'Version' => $compat_level,
    'DetailLevel' => 'ReturnSummary',
    'UserID' => $user_id,
    'ErrorLanguage' => $error_lang,
    'ActiveList' => '',
    'ActiveList.Sort' => 'QuantityAvailable',
);

$responseObj = $clientGet->__soapCall('GetMyeBaySelling', array($params), null, $headerGet);

if (isset($responseObj->ActiveList->ItemArray)) {
    $itemArray = $responseObj->ActiveList->ItemArray->Item;
} else {
    throw new Exception('Couldnt get response object from GetMyEbaySelling');
}

$toUpdate = [];

foreach ($itemArray as $item) {
    $itemId = $item->ItemID;

//    var_dump($item);

    foreach ($item->Variations->Variation as $variation) {

        if ($variation->Quantity < 10) {
            $toUpdate[] = [
                'ItemID' => $itemId,
                'SKU' => $variation->SKU,
                'Quantity' => '10',
            ];
        }
    }
}

$client = new SOAPClient($wsdl_file, array('location' => $api_url . '/wsapi?callname=ReviseInventoryStatus' . '&appid=' . $app_id . '&siteid='. $site_id .'&version='. $compat_level .'&routing=new'));
$header = new SoapHeader('urn:ebay:apis:eBLBaseComponents', 'RequesterCredentials', $requesterCredentials);

$chunks = array_chunk($toUpdate, 4);

$responses = [];

foreach ($chunks as $chunk) {
    $params = array(
        'Version' => $compat_level,
        'DetailLevel' => 'ReturnSummary',
        'ErrorLanguage' => $error_lang,
        'WarningLevel' => 'High',
        'InventoryStatus' => $chunk
    );

    $responses[] = $client->__soapCall('ReviseInventoryStatus', array($params), null, $header);

    break;
}

$fintime = microtime(true) - $time;

// logs

$log = var_export($responses, true) . PHP_EOL .
    'Execution time: ' . $fintime;

$logname = 'logs/log_' . time() . '.log';

file_put_contents($logname, $log);

