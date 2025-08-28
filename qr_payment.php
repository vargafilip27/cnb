<?php

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

if ($prefixDelimiter != false) {
	$bankCode = substr($accountNumber, $prefixDelimiter+1);
}
else $bankCode = $accountNumber;

$paymentUrl = 	"https://api.paylibo.com/paylibo/generator/czech/image?" .
				"accountPrefix=" . $accountPrefix . "&" .
				"accountNumber=" . $accountNumber . "&" .
				"bankCode=" . $bankCode . "&" .
				"amount=" . $amount . "&" .
				"currency=CZK&" .
				"message=" . $eventResult["title"] .
				"size=150";

echo "<img src=$paymentUrl />";
