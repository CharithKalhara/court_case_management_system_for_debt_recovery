<?php

session_start();

require "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$error = "";

// GET record
$year = (int)($_GET['year'] ?? 0);
$company_case_no = (int)($_GET['company_case_no'] ?? 0);

$stmt = $db->prepare("
    SELECT *
    FROM nadu_duplicate_public
    WHERE year = :year
    AND company_case_no = :company_case_no
");

$stmt->execute([
    ':year' => $year,
    ':company_case_no' => $company_case_no
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Record not found");
}

/* UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $company_name = trim($_POST['company_name']);
    $date = $_POST['received_date'];

    $court_case_option = $_POST['court_case_option'] ?? '';
    $court_case_input = $_POST['court_case_no'] ?? '';

    $allowed = ['', 'pending', 'number'];

    if (!in_array($court_case_option, $allowed, true)) {

        $error = "Invalid selection";
        $court_case_no = null;

    } else {

        if ($court_case_option === '') {

            $court_case_no = null;

        } elseif ($court_case_option === 'pending') {

            $court_case_no = 'pending';

        } elseif ($court_case_option === 'number') {

            if (
                $court_case_input === '' ||
                !preg_match('/^[0-9]+$/', $court_case_input)
            ) {

                $error = "Enter a case number";
                $court_case_no = null;

            } else {

                $court_case_no = (int)$court_case_input;
            }
        }
    }

    if (empty($error)) {

        try {

            $update = $db->prepare("
                UPDATE nadu_duplicate_public
                SET
                    company_name = :company,
                    received_date = :date,
                    court_case_no = :court_case_no
                WHERE year = :year
                AND company_case_no = :company_case_no
            ");

            $update->bindValue(':company', $company_name);
            $update->bindValue(':date', $date);

            if ($court_case_no === null) {
                $update->bindValue(':court_case_no', null, PDO::PARAM_NULL);
            } else {
                $update->bindValue(':court_case_no', $court_case_no);
            }

            $update->bindValue(':year', $year);
            $update->bindValue(':company_case_no', $company_case_no);

            $update->execute();

            header("Location: index.php");
            exit();

        } catch (PDOException $e) {

            $error = $e->getMessage();
        }
    }
}
?>

<link rel="stylesheet" href="style.css">

<a href="index.php">Return to List</a>

<?php if (!empty($error)) : ?>

<div style="
    background:#ffebee;
    color:#b71c1c;
    padding:12px;
    margin-bottom:15px;
    border-radius:5px;
    font-weight:bold;
">

    <?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>

<div class="nadu-form">

<form method="POST">

    <div class="form-group">
        <label>Year</label>
        <input
            value="<?= htmlspecialchars($row['year']) ?>"
            disabled
        >
    </div>

    <div class="form-group">
        <label>Decision No.</label>
        <input
            value="<?= htmlspecialchars($row['company_case_no']) ?>"
            disabled
        >
    </div>

    <div class="form-group">
        <label>Company Name</label>

        <input
            name="company_name"
            value="<?= htmlspecialchars($row['company_name']) ?>"
            required
        >
    </div>

    <div class="form-group">
        <label>Received Date</label>

        <input
            type="date"
            name="received_date"
            value="<?= htmlspecialchars($row['received_date']) ?>"
            required
        >
    </div>

    <?php

    $currentOption = '';

    if ($row['court_case_no'] === null || $row['court_case_no'] === '') {
        $currentOption = '';
    }

    elseif (strtolower((string)$row['court_case_no']) === 'pending') {
        $currentOption = 'pending';
    }

    else {
        $currentOption = 'number';
    }

    ?>

    <div class="form-group">

        <label>Case Number Selection</label>

        <select
            name="court_case_option"
            id="court_case_option"
            onchange="toggleNaduInput(this.value)"
        >

            <option value=""
                <?= $currentOption === '' ? 'selected' : '' ?>>
                Clear
            </option>

            <option value="pending"
                <?= $currentOption === 'pending' ? 'selected' : '' ?>>
                Pending
            </option>

                <option value="number"
                <?= $currentOption === 'number' ? 'selected' : '' ?>>
                Case Number
            </option>

        </select>

    </div>

    <div class="form-group">

        <label>Case Number</label>

        <input
            type="number"
            name="court_case_no"
            id="court_case_no_input"
            value="<?= $currentOption === 'number'
                ? htmlspecialchars($row['court_case_no'])
                : '' ?>"
        >

    </div>

    <button type="submit">
        Update Record
    </button>

</form>

</div>

<script>

function toggleNaduInput(value) {

    const input =
        document.getElementById('court_case_no_input');

    if (value === 'number') {

        input.style.display = 'block';
        input.required = true;

    } else {

        input.style.display = 'none';
        input.required = false;

        if (value !== 'number') {
            input.value = '';
        }
    }
}

// initialize
toggleNaduInput(
    document.getElementById('court_case_option').value
);

</script>