<?php

require_once "config.php";

session_start();

if (isset($_SESSION['user'])) {
	echo "Hello, " . htmlspecialchars($_SESSION['user']['name']);
	echo "<br><img src='" . htmlspecialchars($_SESSION['user']['picture']) . "'>";
}
else require_once "login.php";
