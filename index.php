<?php

require_once "config.php";

session_start();

if (!isset($_SESSION['user'])) {
	require_once "login.php";
	exit;
}

echo htmlspecialchars($_SESSION['user']['name']);
echo "<br><img src='" . htmlspecialchars($_SESSION['user']['picture']) . "'>";
