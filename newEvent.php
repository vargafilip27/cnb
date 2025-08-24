<?php

require_once "config.php";

session_start();

if (!isset($_SESSION['user'])) {
	require_once "login.php";
	exit;
}

require_once "templates/header.php";

?>

<div class="btn"><a href="index.php">Zpět</a></div>

<form method='post' style='max-width: 600px; margin: auto'>
	<h2>Přidat novou událost</h2>

	<div class='detail-row'>
		<label class='detail-label'>Název</label>
		<input class='detail-value' name='title'>
	</div>

	<div class='detail-row'>
		<label class='detail-label'>Popis</label>
		<textarea class='detail-value' name='description'></textarea>
	</div>

	<div class='detail-row'>
		<label class='detail-label'>Heslo</label>
		<input class='detail-value' name='password'>
	</div>

	<button type='submit' class='submit-btn'>Uložit</button>
</form>

<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$dbId = $_SESSION['user']['dbId'];
	$title = trim($_POST['title']);
	$description = trim($_POST['description']);
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

	var_dump($title, $description, $dbId, $password);
	sleep(10);

	if (!$mysqli->query("	INSERT INTO Events (id_user, title, description, password)
								VALUES ('$dbId', '$title', '$description', '$password')"	))
		die("DB Error: " . $mysqli->error);
}

require_once "templates/footer.php";
