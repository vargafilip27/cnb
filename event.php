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
$eventQuery = $mysqli->query("SELECT * FROM Events WHERE id_event = $idEvent");
$eventResult = $eventQuery->fetch_assoc();

if (!$eventResult) die("Event not found");

// Authentication for event
if (!empty($eventResult["password"])) {

    if (!isset($_SESSION['unlocked'][$idEvent])){

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if (password_verify($_POST['password'], $eventResult['password'])) {
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

echo "<p class='title'>$eventResult[title]</p>
      <p class='description'>$eventResult[description]</p>";

$dbId = $_SESSION["user"]["dbId"];

// Check for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$nights = $_POST['nights'];
	$expense = $_POST['expense'];

	$mysqli->query("  UPDATE Expenses
                            SET nights = $nights, expense = $expense
                            WHERE id_user = $dbId AND id_event = $idEvent   ");

    $bankAccount = $_POST['account'];

    $mysqli->query("  UPDATE Users
                            SET bank_account = '$bankAccount'
                            WHERE id_user = $dbId   ");
}

$mysqli->query("INSERT INTO Expenses (id_event, id_user, expense, nights) VALUES ($idEvent, $dbId, 0, 0)");
$participantsQuery = $mysqli->query("SELECT * FROM Expenses WHERE id_event = $idEvent");

// Generate expenses table
if ($participantsQuery) {

	echo "<form method='post'>
            <table class='tbl'>
			    <thead>
				    <tr>
					    <td>Účastník</td>
					    <td>Počet večerů</td>
					    <td>Výdaje</td>
					    <td>Bankovní účet</td>
				    </tr>
			    </thead>
			    <tbody>
			    ";

    // Class representing state of each participant in this event
    $sum = 0;
    $totalNights = 0;

    class Score {
        public $id;
        public $name;
        public $debt;
        public $nights;
        public $bankAccount;

        public function __construct(int $id, string $name, float $debt, int $nights, string $bankAccount = null) {
            $this->id = $id;
            $this->name = $name;
            $this->debt = $debt;
            $this->nights = $nights;
            $this->bankAccount = $bankAccount;
        }
    }

    $scores = [];

    while ($participantsRow = $participantsQuery->fetch_assoc()) {
        $nameQuery = $mysqli->query("SELECT * FROM Users WHERE id_user = $participantsRow[id_user]");
        $nameRow = $nameQuery->fetch_assoc();

        $sum += $participantsRow["expense"];
        $totalNights += $participantsRow["nights"];

        echo "<tr>
                <td>$nameRow[name]</td>
             ";

        // Add score of participant for calculating later
        $scores[] = new Score(  $participantsRow["id_user"],
                                $nameRow["name"],
                                $participantsRow["expense"],
                                $participantsRow["nights"],
                                $nameRow["bank_account"]    );

        // Fill table
        // If filling info about logged-in user, make it inputable
        if ($participantsRow[id_user] == $dbId) echo "<td><input type='number' name='nights' value=$participantsRow[nights]></td>";
        else echo "<td>$participantsRow[nights]</td>";

		if ($participantsRow[id_user] == $dbId) echo "<td><input type='number' name='expense' value=$participantsRow[expense]></td>";
		else echo "<td>$participantsRow[expense]</td>";

		if ($participantsRow[id_user] == $dbId) echo "<td><input name='account' value=$nameRow[bank_account]></td>";
		else echo "<td>$nameRow[bank_account]</td>";

        echo "</tr>";
    }

    echo "    </tbody>
            </table>
            <button class='btn' type='submit'>Potvrdit</button>
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
			$amount = round(-$scores[$lo]->debt, 2);
            $paymentTarget = $scores[$hi]->bankAccount;

            echo "<p>" . $scores[$lo]->name . " " . $amount . " >>> " . $scores[$hi]->name . "</p>";

			// Generate QR payment code eventually
			if ($scores[$lo]->id == $_SESSION["user"]["dbId"]) require_once "qr_payment.php";

            $scores[$hi]->debt -= $scores[$lo]->debt;
			$scores[$lo++]->debt = 0;
        }
        else {
			$amount = round(-$scores[$lo]->debt, 2);
			$paymentTarget = $scores[$hi]->bankAccount;

			echo "<p>" . $scores[$lo]->name . " " . $amount . " >>> " . $scores[$hi]->name . "</p>";

			// Generate QR payment code eventually
			if ($scores[$lo]->id == $_SESSION["user"]["dbId"]) require_once "qr_payment.php";

			$scores[$lo]->debt += $scores[$hi]->debt;
			$scores[$hi--]->debt = 0;
        }
    }
}

require_once "templates/footer.php";
