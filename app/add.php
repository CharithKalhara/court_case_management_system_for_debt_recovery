<?php

    include 'db.php';

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    $error = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $year = (int) $_POST['year'];

        $company_case_no = (int) $_POST['company_case_no'];

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
            }

            elseif ($court_case_option === 'pending') {
                $court_case_no = 'pending';
            }

            elseif ($court_case_option === 'number') {

                if ($court_case_input === '' || !preg_match('/^[0-9]+$/', $court_case_input)) {
                    $error = "Enter a case number";
                    $court_case_no = null;
                } else {
                    $court_case_no = (int)$court_case_input;
                }
            }
        }

        if (empty($error)) {
            try {

                $stmt = $db->prepare("
                    INSERT INTO nadu_duplicate_public (
                        year,
                        company_case_no,
                        company_name,
                        received_date,
                        court_case_no
                    )
                    VALUES (
                        :year,
                        :company_case_no,
                        :company,
                        :date,
                        :court_case_no
                    )
                ");

                $stmt->bindValue(':year', $year);

                $stmt->bindValue(':company_case_no', $company_case_no);

                $stmt->bindValue(':company', $company_name);

                $stmt->bindValue(':date', $date);

                if ($court_case_no === null) {
                    $stmt->bindValue(':court_case_no', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':court_case_no', $court_case_no);
                }

                $stmt->execute();

                header("Location: index.php");

                exit();

            } catch (PDOException $e) {

                if ($e->getCode() == '23505') {

                    $error = "A record with this case number already exists";

                } else {

                    $error = $e->getMessage();
                }
            }
        }
    }
    ?>

    <link rel="stylesheet" href="style.css">
    <div>
        <a href="index.php">Return to List</a>
        <br>
    </div>

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
            <input name="year" type="number" required>
        </div>

        <div class="form-group">
            <label>Decision No.</label>
            <input name="company_case_no" type="number" required>
        </div>

        <div class="form-group">
            <label>Company Name</label>
            <input name="company_name" required>
        </div>

        <div class="form-group">
            <label>Received Date</label>
            <input name="received_date" type="date" required>
        </div>

        <div class="form-group">
            <label>Case Number Selection</label>
            <select name="court_case_option" id="court_case_option" onchange="toggleNaduInput(this.value)">
                <option value="">Clear</option>
                <option value="pending">Pending</option>
                <option value="number">Case Number</option>
            </select>
        </div>

        <div class="form-group">
            <label>Case Number</label>
            <input type="number" name="court_case_no" id="court_case_no_input" style="display:none;">
        </div>

        <button type="submit">Save Record</button>

    </form>
    </div>

    <script>
    function toggleNaduInput(value) {
        const input = document.getElementById('court_case_no_input');

        if (value === 'number') {
            input.style.display = 'block';
            input.required = true;
        } else {
            input.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }
    </script>