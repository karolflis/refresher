<?php
require_once('includes/config.php');

$apiCall = 'GetUser';

//trace and debugging
//$client = new SOAPClient($wsdl_file, array('trace' => 1, 'exceptions' => true, 'location' => 'https://api.sandbox.ebay.com/wsapi?callname=' . $apiCall . '&appid=' . $appId . '&siteid='. $site_id .'&version='. $compat_level .'&routing=new'));

$client = new SOAPClient($wsdl_file, array('location' => 'https://api.sandbox.ebay.com/wsapi?callname=' . $apiCall . '&appid=' . $app_id . '&siteid='. $site_id .'&version='. $compat_level .'&routing=new'));

$requesterCredentials = new stdClass();
$requesterCredentials->eBayAuthToken = $auth_token;

$header = new SoapHeader('urn:ebay:apis:eBLBaseComponents', 'RequesterCredentials', $requesterCredentials);

// the API call parameters
$params = array(
    'Version' => $compat_level,
    'DetailLevel' => 'ReturnSummary',
    'UserID' => 'testuser_unnzowy'
);

$responseObj = $client->__soapCall($apiCall, array($params), null, $header);

echo 'abc';

