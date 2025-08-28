<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$apiClient = new Client();

// Divide acc prefix
$prefixDelimiter = strpos($paymentTarget, "-");

if ($prefixDelimiter != false) {
	$accountPrefix = substr($paymentTarget, 0, $prefixDelimiter);
	$accountNumber = substr($paymentTarget, $prefixDelimiter+1);
}
else {
	$accountPrefix = "";
	$accountNumber = $paymentTarget;
}

// Get bank code
$bankCodeDelimiter = strpos($accountNumber, "/");

if ($bankCodeDelimiter != false) {
	$bankCode = substr($accountNumber, $bankCodeDelimiter+1);
	$accountNumber = substr($accountNumber, 0, $bankCodeDelimiter);
}
else $bankCode = "";

$paymentUrl = 	"https://api.paylibo.com/paylibo/generator/czech/image?" .
				"accountPrefix=" . $accountPrefix . "&" .
				"accountNumber=" . $accountNumber . "&" .
				"bankCode=" . $bankCode . "&" .
				"amount=" . $amount . "&" .
				"currency=CZK&" .
				"size=300";

try {
	$response = $apiClient->get($paymentUrl);
	$contentType = $response->getHeaderLine('Content-Type');
	$body = (string)$response->getBody();

	if (strpos($contentType, 'image/') !== false) {
		// API returned an image
		$base64 = base64_encode($body);
		echo "<img src=$paymentUrl width='300' height='300'/>";
	}
}
catch (RequestException $e) {}
