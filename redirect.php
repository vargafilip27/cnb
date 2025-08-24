<?php
require 'config.php';
require 'vendor/autoload.php';
session_start();

$client = new Google\Client();
$client->setClientId($_ENV["GOOGLE_CLIENT_ID"]);
$client->setClientSecret($_ENV["GOOGLE_CLIENT_SECRET"]);
$client->setRedirectUri('https://filipvarga.cz/cnb/redirect.php');

if (isset($_GET['code'])) {
	$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

	if (!isset($token['error'])) {
		$client->setAccessToken($token);

		// Get user profile info
		$oauth2 = new Google\Service\Oauth2($client);
		$userInfo = $oauth2->userinfo->get();

		// Store user info in session
		$_SESSION['user'] = [
			'id'    => $userInfo->id,
			'name'  => $userInfo->name,
			'email' => $userInfo->email
		];

		$user = $mysqli->query("SELECT * FROM Users WHERE google_id = '$userInfo->id'");

		if ($user->num_rows < 1)
			$mysqli->query("	INSERT INTO Users (google_id, email, name)
									VALUES ('$userInfo->id', '$userInfo->email', '$userInfo->name')	");

		// Redirect to homepage
		header('Location: index.php');
		exit;
	}
}
