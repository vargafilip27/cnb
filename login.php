<?php
session_start();

$googleClient = new Google\Client();
$googleClient->setClientId($_ENV["GOOGLE_CLIENT_ID"]);
$googleClient->setClientSecret($_ENV["GOOGLE_CLIENT_SECRET"]);
$googleClient->setRedirectUri('https://filipvarga.cz/cnb/redirect.php');
$googleClient->addScope('email');
$googleClient->addScope('profile');

// Generate Google login URL
$loginUrl = $googleClient->createAuthUrl();

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
