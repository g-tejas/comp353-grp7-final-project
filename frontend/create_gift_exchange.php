<?php
session_start();
include 'includes/dbh.inc.php';

if (!isset($_SESSION['user']) || !isset($_POST['group_id'])) {
    header("Location: groups.php");
    exit();
}

$member_id = $_SESSION['user'];
$group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
$budget_min = filter_input(INPUT_POST, 'budget_min', FILTER_VALIDATE_FLOAT);
$budget_max = filter_input(INPUT_POST, 'budget_max', FILTER_VALIDATE_FLOAT);
$start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
$end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);

try {
    // Start transaction
    $conn->begin_transaction();

    // Create the gift exchange
    $stmt = $conn->prepare("INSERT INTO gift_exchange (Group_ID, Start_Date, End_Date, Budget_Min, Budget_Max, Created_By) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issddi', $group_id, $start_date, $end_date, $budget_min, $budget_max, $member_id);
    $stmt->execute();
    $exchange_id = $conn->insert_id;

    // Add creator as first participant
    $stmt = $conn->prepare("INSERT INTO gift_exchange_participants (Exchange_ID, Giver_ID) VALUES (?, ?)");
    $stmt->bind_param('ii', $exchange_id, $member_id);
    $stmt->execute();

    $conn->commit();
    header("Location: group_details.php?id=" . $group_id . "&success=exchange_created");
} catch (Exception $e) {
    $conn->rollback();
    header("Location: group_details.php?id=" . $group_id . "&error=creation_failed");
}
?> 