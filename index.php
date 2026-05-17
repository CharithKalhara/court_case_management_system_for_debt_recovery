<?php
session_start();

require "db.php";

$start_time = $_SERVER["REQUEST_TIME_FLOAT"];

// total count
$total_records = $db->query("
    SELECT COUNT(*)
    FROM nadu_duplicate_public
    WHERE court_case_no IS NULL
        OR court_case_no = ''
        OR court_case_no = 'pending'
")->fetchColumn();

// login check
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// DB check
$start_db = microtime(true);

$result_check = $db->query("SELECT 1")->fetchColumn();

$end_db = microtime(true);

$db_latency = round(($end_db - $start_db) * 1000, 2);

$status = $result_check ? "🟢 Online" : "🔴 DB Error";
?>

<!DOCTYPE html>
<meta charset="UTF-8">
<html>
<head>
    <title>Court Case Management System</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-box {
            font-weight: bold;
            font-size: 18px;
            color: green;
        }

    </style>
</head>

<body>

<?php
$end_time = microtime(true);

$latency = round(($end_time - $start_time) * 1000, 2);
?>

<div class="status-bar">

    <div id="statusContent">

        <?php echo $status; ?> |

        Server: <span id="realLatency">...</span> |

        DB: <?php echo $db_latency; ?> ms |

        <span id="liveClock"></span>

    </div>

    <br>

</div>

<div id="messageBox" style="
    display:none;
    background:#ffebee;
    color:#b71c1c;
    padding:12px;
    margin:10px 0;
    border-radius:5px;
    font-weight:bold;
">
</div>

<div class="top-bar">

    <h2>Court Case Management System</h2>

    <div class="total-box">

        Total Records:
        <span id="recordCount">
            <?php echo $total_records; ?>
        </span>

        <br>

</div>

</div>

<a href="logout.php">Logout</a>

<form method="GET">

    <input type="text"
           name="company"
           placeholder="Company Name"
           value="<?= htmlspecialchars($_GET['company'] ?? '') ?>">

    <input type="number"
           name="year"
           placeholder="Year"
           value="<?= htmlspecialchars($_GET['year'] ?? '') ?>">

    <input type="number"
            name="company_case_no"
            placeholder="Company Case No"
            value="<?= htmlspecialchars($_GET['company_case_no'] ?? '') ?>">

    <input type="text"
            name="court_case_no"
            placeholder="Court Case No"
            value="<?= htmlspecialchars($_GET['court_case_no'] ?? '') ?>">

    <button type="submit">Filter</button>

</form>

<a href="add.php">Add New Record</a>

<table>

<tr>
    <th>Serial No</th>
    <th>Received Date</th>
    <th>Company Name</th>
    <th>Year</th>
    <th>Company Case No</th>
    <th>Court Case No</th>
    <th>Action</th>
</tr>

<?php

$company = trim($_GET['company'] ?? '');
$year = trim($_GET['year'] ?? '');
$company_case_no = trim($_GET['company_case_no'] ?? '');
$court_case_no = trim($_GET['court_case_no'] ?? '');

$sql = "
SELECT
    received_date,
    company_name,
    year,
    company_case_no,
    court_case_no
FROM nadu_duplicate_public
WHERE 1=1
";

$params = [];

// company filter
if ($company !== '') {

    $sql .= " AND company_name ILIKE :company";

    $params[':company'] = "%$company%";
}

// year filter
if ($year !== '') {

    $sql .= " AND year = :year";

    $params[':year'] = (int)$year;
}

// case filter
if ($company_case_no !== '') {

    $sql .= " AND company_case_no = :company_case_no";

    $params[':company_case_no'] = (int)$company_case_no;
}

// court_case_no filter
if ($court_case_no !== '') {

    $sql .= " AND court_case_no = :court_case_no";

    $params[':court_case_no'] = $court_case_no;
}

$sql .= " ORDER BY company_name ASC";

$stmt = $db->prepare($sql);

// bind parameters
foreach ($params as $key => $value) {

    $stmt->bindValue($key, $value);
}

$stmt->execute();

$found = false;
$row_number = 1;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $found = true;

    echo "

    <tr id='row-{$row['year']}-{$row['company_case_no']}'>

        <td>" . $row_number . "</td>

        <td>" . htmlspecialchars($row['received_date']) . "</td>

        <td>" . htmlspecialchars($row['company_name']) . "</td>

        <td>" . htmlspecialchars($row['year']) . "</td>

        <td>" . htmlspecialchars($row['company_case_no']) . "</td>

        <td style='display:flex; justify-content:space-between; align-items:center;'>

            <span>
                " . (
                    empty($row['court_case_no'])
                        ? ''
                        : (
                            strtolower((string)$row['court_case_no']) === 'pending'
                                ? 'pending'
                                : htmlspecialchars($row['court_case_no'])
                        )
                ) . "
            </span>

            <a href='#'
            onclick='window.location.href =
                    \"edit.php?year={$row['year']}&company_case_no={$row['company_case_no']}\";
            return false;'>

                Edit

            </a>

        </td>

        <td>

            <a href='#'
               onclick='deleteRecord(
                    {$row['year']},
                    {$row['company_case_no']}
               ); return false;'>

                Delete

            </a>

        </td>

    </tr>
    ";

    $row_number++;
}

if (!$found) {

    echo "
    <tr>
        <td colspan='7'>No records found</td>
    </tr>
    ";
}

?>

</table>

<script>

window.addEventListener("load", function () {

    let loadTime = performance.now();

    document.getElementById("realLatency").innerText =
        loadTime.toFixed(2) + " ms";
});

function updateClock() {
    let now = new Date();

    let formatted = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Colombo',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    }).format(now);

    document.getElementById("liveClock").innerText = formatted;
}

setInterval(updateClock, 1000);

updateClock();

function showMessage(message) {

    let box = document.getElementById("messageBox");

    box.innerText = message;

    box.style.display = "block";
}

function clearMessage() {

    let box = document.getElementById("messageBox");

    box.style.display = "none";

    box.innerText = "";
}

function deleteRecord(year, companyCaseNo) {

    if (!confirm("Delete this record?")) {
        return;
    }

    if (wasOffline) {

        showMessage("No internet connection");

        return;
    }

    fetch("delete.php", {

        method: "POST",

        headers: {
            "Content-Type":
            "application/x-www-form-urlencoded"
        },

        body:
            `year=${year}&company_case_no=${companyCaseNo}`

    })

    .then(response => response.text())

    .then(data => {

        data = data.trim();

        if (data !== "success") {

            showMessage(data);

            return;
        }

        clearMessage();

        let row =
            document.getElementById(
                `row-${year}-${companyCaseNo}`
            );

        if (row) {

            row.remove();

            let countElement =
                document.getElementById("recordCount");

            let current =
                parseInt(countElement.innerText);

            if (current > 0) {

                countElement.innerText =
                    current - 1;
            }
        }
    })

    .catch(error => {

        showMessage("Server connection failed");

        console.error(error);
    });
}

let wasOffline = false;

setInterval(async () => {

    try {

        await fetch("ping.php?" + Date.now());

        if (wasOffline) {

            location.reload();

            return;
        }

        wasOffline = false;

    } catch {

        wasOffline = true;

        document.getElementById("statusContent")
            .innerHTML =
            "<span style='color:red;'>🔴 Offline</span>";
    }

}, 1000);

</script>

</body>
</html>