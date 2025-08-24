<?php
session_start();

$client = new Google\Client();
$client->setClientId($_ENV["GOOGLE_CLIENT_ID"]);
$client->setClientSecret($_ENV["GOOGLE_CLIENT_SECRET"]);
$client->setRedirectUri('https://filipvarga.cz/cnb/redirect.php');
$client->addScope('email');
$client->addScope('profile');

// Generate Google login URL
$loginUrl = $client->createAuthUrl();

require_once "templates/header.php";

?>
<a href="<?= htmlspecialchars($loginUrl) ?>">Login with Google</a>
<?php

require_once "templates/footer.php";
