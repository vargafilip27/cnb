<?php

require_once "config.php";

session_start();

if (!isset($_SESSION['user'])) {
	require_once "login.php";
	exit;
}

require_once "templates/header.php";

$events = $mysqli->query("SELECT * FROM Events");

echo "<div class='btn'><a href='newEvent.php'>Přidat událost</a></div>";

if (!$events) echo "<p>Žádné události</p>";
else {
	while ($row = $events->fetch_assoc()) {
		echo "
			<div class='event'>
				<p class='title'>$row[title]</p>
				<p class='description'>$row[description]</p>
			</div>";
	}
}

require_once "templates/footer.php";
