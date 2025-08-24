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
    <div class="btn" style="text-align: center">
        <a href="<?= htmlspecialchars($loginUrl) ?>">
            Skibidi přihlášení
            <img src="https://www.gstatic.com/images/branding/searchlogo/ico/favicon.ico">
        </a>
    </div>
<?php

require_once "templates/footer.php";
