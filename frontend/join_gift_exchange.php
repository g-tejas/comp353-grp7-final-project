<?php
session_start();
include 'includes/dbh.inc.php';

if (!isset($_SESSION['user']) || !isset($_POST['exchange_id'])) {
    header("Location: groups.php");
    exit();
}

$member_id = $_SESSION['user'];
$exchange_id = filter_input(INPUT_POST, 'exchange_id', FILTER_VALIDATE_INT);

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if exchange is still active
    $stmt = $conn->prepare("SELECT Group_ID, Status FROM gift_exchange WHERE Exchange_ID = ? AND Status = 'Active'");
    $stmt->bind_param('i', $exchange_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exchange = $result->fetch_assoc();

    if (!$exchange) {
        throw new Exception("Exchange not found or not active");
    }

    // Check if user is already participating
    $stmt = $conn->prepare("SELECT 1 FROM gift_exchange_participants WHERE Exchange_ID = ? AND Giver_ID = ?");
    $stmt->bind_param('ii', $exchange_id, $member_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Already participating");
    }

    // Add user as participant
    $stmt = $conn->prepare("INSERT INTO gift_exchange_participants (Exchange_ID, Giver_ID) VALUES (?, ?)");
    $stmt->bind_param('ii', $exchange_id, $member_id);
    $stmt->execute();

    // If we have enough participants (at least 3), assign gift receivers
    $stmt = $conn->prepare("SELECT Giver_ID FROM gift_exchange_participants WHERE Exchange_ID = ?");
    $stmt->bind_param('i', $exchange_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $participants = $result->fetch_all(MYSQLI_ASSOC);

    if (count($participants) >= 3) {
        // Shuffle participants for random assignment
        $givers = array_column($participants, 'Giver_ID');
        $receivers = $givers;
        
        do {
            shuffle($receivers);
        } while (array_diff_assoc($givers, $receivers) === array() || 
                array_intersect_assoc($givers, $receivers) !== array());

        // Assign receivers to givers
        $stmt = $conn->prepare("UPDATE gift_exchange_participants SET Receiver_ID = ? WHERE Exchange_ID = ? AND Giver_ID = ?");
        foreach ($givers as $index => $giver_id) {
            $receiver_id = $receivers[$index];
            $stmt->bind_param('iii', $receiver_id, $exchange_id, $giver_id);
            $stmt->execute();
        }
    }

    $conn->commit();
    header("Location: group_details.php?id=" . $exchange['Group_ID'] . "&success=joined_exchange");
} catch (Exception $e) {
    $conn->rollback();
    header("Location: group_details.php?id=" . $exchange['Group_ID'] . "&error=" . urlencode($e->getMessage()));
}
?> 