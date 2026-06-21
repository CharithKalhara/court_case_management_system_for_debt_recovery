<?php

include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {

    echo "Session expired";

    exit();
}

$year = isset($_POST['year'])
    ? (int)$_POST['year']
    : 0;

$company_case_no = isset($_POST['company_case_no'])
    ? (int)$_POST['company_case_no']
    : 0;

if ($year <= 0 || $company_case_no <= 0) {

    echo "Invalid record";

    exit();
}

try {

    $stmt = $db->prepare("
        DELETE FROM nadu_duplicate_public
        WHERE year = :year
        AND company_case_no = :company_case_no
    ");

    $stmt->bindValue(':year', $year);

    $stmt->bindValue(':company_case_no', $company_case_no);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        echo "success";

    } else {

        echo "Record not found";
    }

} catch (PDOException $e) {

    echo "Database delete failed";
}
?>