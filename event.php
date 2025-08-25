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

$dbId = $_SESSION["user"]["dbId"];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$nights = $_POST['nights'];
	$expense = $_POST['expense'];

	$mysqli->query("  UPDATE Expenses
                            SET nights = $nights, expense = $expense
                            WHERE id_user = $dbId AND id_event = $idEvent   ");
}

$mysqli->query("INSERT INTO Expenses (id_event, id_user) VALUES ($idEvent, $dbId)");
$participants = $mysqli->query("SELECT * FROM Expenses WHERE id_event = $idEvent");

if ($participants) {

	echo "<form method='post'>
            <table class='tbl'>
			    <thead>
				    <tr>
					    <td>Účastník</td>
					    <td>Počet večerů</td>
					    <td>Výdaje</td>
				    <tr>
			    </thead>
			    <tbody>
			    ";

    $sum = 0;

    class Score {
        public $name;
        public $debt;

        public function __construct($name, $debt) {
            $this->name = $name;
            $this->debt = $debt;
        }
    }

    $scores = [];

    while ($row = $participants->fetch_assoc()) {
        $nameQuery = $mysqli->query("SELECT name FROM Users WHERE id_user = $row[id_user]");
        $name = $nameQuery->fetch_assoc();

        $sum += $row["expense"];

        echo "<tr>
                <td>$name[name]</td>
             ";

        $scores[] = new Score($name["name"], $row['expense']);

        if ($row[id_user] == $dbId) echo "<td><input type='number' name='nights' value=$row[nights]></td>";
        else echo "<td>$row[nights]</td>";

		if ($row[id_user] == $dbId) echo "<td><input type='number' name='expense' value=$row[expense]></td>";
		else echo "<td>$row[expense]</td>";
    }

    echo "    </tbody>
            </table>
            <button class='btn' type='submit'>Upravit</button>
          </form>
          ";

    $avg = $sum / $participants->num_rows;

    foreach ($scores as $score) {
        $score->debt -= $avg;
	}
}

require_once "templates/footer.php";
