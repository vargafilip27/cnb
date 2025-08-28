<?php

require_once "config.php";

session_start();

// Check login
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

// Authentication for event
if (!empty($result["password"])) {

    if (!isset($_SESSION['unlocked'][$idEvent])){

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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

// Check for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$nights = $_POST['nights'];
	$expense = $_POST['expense'];

	$mysqli->query("  UPDATE Expenses
                            SET nights = $nights, expense = $expense
                            WHERE id_user = $dbId AND id_event = $idEvent   ");
}

$mysqli->query("INSERT INTO Expenses (id_event, id_user, expense, nights) VALUES ($idEvent, $dbId, 0, 0)");
$participants = $mysqli->query("SELECT * FROM Expenses WHERE id_event = $idEvent");

// Generate expenses table
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
    $totalNights = 0;

    class Score {
        public $name;
        public $debt;
        public $nights;

        public function __construct(string $name, float $debt, int $nights) {
            $this->name = $name;
            $this->debt = $debt;
            $this->nights = $nights;
        }
    }

    $scores = [];

    while ($row = $participants->fetch_assoc()) {
        $nameQuery = $mysqli->query("SELECT name FROM Users WHERE id_user = $row[id_user]");
        $name = $nameQuery->fetch_assoc();

        $sum += $row["expense"];
        $totalNights += $row["nights"];

        echo "<tr>
                <td>$name[name]</td>
             ";

        $scores[] = new Score($name["name"], $row['expense'], $row["nights"]);

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

    // Calculate debts
    $avg = $sum / $totalNights;

    foreach ($scores as $score) {
        $score->debt -= ($avg*$score->nights);
	}

    usort($scores, function($a, $b) {
        return $a->debt <=> $b->debt;
    });

    $lo = 0;
    $hi = count($scores)-1;

    while ($lo < $hi) {

        while ($lo < $hi && abs($scores[$lo]->debt) < 1) ++$lo;
        while ($lo < $hi && abs($scores[$hi]->debt) < 1) --$hi;

        if ($lo >= $hi) break;

        if ($scores[$lo]->debt < $scores[$hi]->debt) {
            echo "<p>" . $scores[$lo]->name . " " . round(-$scores[$lo]->debt, 2) . " >>> " . $scores[$hi]->name . "</p>";

            $scores[$hi]->debt -= $scores[$lo]->debt;
			$scores[$lo++]->debt = 0;
        }
        else {
			echo "<p>" . $scores[$lo]->name . " " . round($scores[$hi]->debt, 2) . " >>> " . $scores[$hi]->name . "</p>";

			$scores[$lo]->debt += $scores[$hi]->debt;
			$scores[$hi--]->debt = 0;
        }
    }
}

require_once "templates/footer.php";
