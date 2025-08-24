<?php

require_once "config.php";

session_start();

if (!isset($_SESSION['user'])) {
	require_once "login.php";
	exit;
}

require_once "templates/header.php";

echo "<div class='btn'><a href='index.php'>Zpět</a></div>";

$idEvent = (int)$_GET['id'];
$query = $mysqli->query("SELECT * FROM Events WHERE id_event = $idEvent");

if (!$query) die("DB Error: " . $mysqli->error);
$result = $query->fetch_assoc();

if (!$result) die("Event not found");

if (!empty($result["password"])) {

    if (!isset($_SESSION['unlocked'][$idEvent])){

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            echo "zadané heslo: " . $_POST['password'] . "\n";
            echo "hash zadaného: " . password_hash($_POST['password'], PASSWORD_DEFAULT) . "\n";
            echo "hash aktuálního: " . $result['password'] . "\n";

			if (password_verify($_POST['password'], $result['password'])) {
				$_SESSION['unlocked'][$idEvent] = true;
				header("Location: event.php?id=$idEvent");
				exit;
			}
            else $error = "Nesprávné heslo";
		}

		if (!empty($error)) echo "<p>$error</p>";

		?>
        <form method="post">
            <label>Zadej heslo:</label>
            <input type="password" name="password">
            <button type="submit">OK</button>
        </form>
		<?php
        exit;
    }
}

echo "<p class='title'>$result[title]</p>
      <p class='description'>$result[description]</p>";

$participants = $mysqli->query("SELECT * FROM Expenses WHERE id_event = $idEvent");

if ($participants) {

	echo "<table>
			<thead>
				<tr>
					<td>Účastník</td>
					<td>Počet večerů</td>
					<td>Výdaje</td>
				<tr>
			</thead>
			<tbody>
			";

    while ($row = $participants->fetch_assoc()) {

        $nameQuery = $mysqli->query("SELECT name FROM Users WHERE id_user = $row[id_user]");
        $name = $nameQuery->fetch_assoc();

        echo "<tr>
                <td>$name</td>
                <td>$row[expense]</td>
                <td>$row[nights]</td>
              </tr>
              ";
    }

    echo "  </tbody>
          </table>
          ";
}

require_once "templates/footer.php";
