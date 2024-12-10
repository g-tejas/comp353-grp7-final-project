<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];
$group_id = filter_input(INPUT_GET, 'group_id', FILTER_VALIDATE_INT);

// If no group_id, show list of user's groups
if (!$group_id) {
    try {
        $stmt = $conn->prepare("SELECT g.* FROM `group` g
                               JOIN group_members gm ON g.Group_ID = gm.Group_ID
                               WHERE gm.Member_ID = ?");
        $stmt->bind_param('i', $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $groups = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (Exception $e) {
        header("Location: index.php?error=" . urlencode("Unable to retrieve groups"));
        exit();
    }
} else {
    // Handle event creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create_event'])) {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            
            $sql = "INSERT INTO content (Group_ID, Member_ID, Title, Body, Is_Event) VALUES (?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiss", $group_id, $member_id, $title, $description);
            $stmt->execute();
            $event_id = $stmt->insert_id;

            foreach ($_POST['datetime'] as $key => $datetime) {
                $place = mysqli_real_escape_string($conn, $_POST['place'][$key]);
                $sql = "INSERT INTO event_options (Content_ID, Date_Time, Place) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $event_id, $datetime, $place);
                $stmt->execute();
            }
            header("Location: events.php?group_id=" . $group_id);
            exit();
        }

        if (isset($_POST['vote'])) {
            $option_id = intval($_POST['option_id']);
            $sql = "INSERT INTO event_option_votes (Member_ID, Option_ID) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE Timestamp = CURRENT_TIMESTAMP";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $member_id, $option_id);
            $stmt->execute();
            header("Location: events.php?group_id=" . $group_id);
            exit();
        }
    }

    // Fetch events for the specific group
    try {
        $sql = "SELECT c.*, m.Pseudonym 
                FROM content c 
                JOIN member m ON c.Member_ID = m.Member_ID 
                WHERE c.Group_ID = ? AND c.Is_Event = 1 
                ORDER BY c.Timestamp DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $events = $stmt->get_result();
    } catch (Exception $e) {
        echo "Error fetching events: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Group Events</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php if (!$group_id): ?>
            <h1>My Groups</h1>
            <div class="groups-list">
                <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $group): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h3><?php echo htmlspecialchars($group['Name']); ?></h3>
                                <p><?php echo htmlspecialchars($group['Description']); ?></p>
                                <a href="events.php?group_id=<?php echo $group['Group_ID']; ?>" class="btn btn-primary">View Events</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>You are not a member of any groups.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h2>Create New Event</h2>
                            <form method="post" class="event-form">
                                <div class="form-group mb-3">
                                    <input type="text" name="title" placeholder="Event Title" required class="form-control">
                                </div>
                                <div class="form-group mb-3">
                                    <textarea name="description" placeholder="Event Description" required class="form-control"></textarea>
                                </div>
                                
                                <div id="options-container">
                                    <div class="option mb-3">
                                        <input type="datetime-local" name="datetime[]" required class="form-control mb-2">
                                        <input type="text" name="place[]" placeholder="Place" required class="form-control">
                                    </div>
                                </div>
                                
                                <button type="button" onclick="addOption()" class="btn btn-secondary mb-3">Add Another Option</button>
                                <input type="submit" name="create_event" value="Create Event" class="btn btn-primary">
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <h2>Current Events</h2>
                    <div class="events-list">
                        <?php if ($events && $events->num_rows > 0): ?>
                            <?php while ($event = $events->fetch_assoc()): ?>
                                <div class="event card mb-3">
                                    <div class="card-header">
                                        <h3 class="mb-0"><?php echo htmlspecialchars($event['Title']); ?></h3>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Created by: <?php echo htmlspecialchars($event['Pseudonym']); ?></p>
                                        <p><?php echo htmlspecialchars($event['Body']); ?></p>
                                        
                                        <h4>Vote for Date/Time/Place:</h4>
                                        <?php
                                        $sql = "SELECT eo.*, 
                                                COUNT(eov.Member_ID) as votes,
                                                MAX(CASE WHEN eov.Member_ID = ? THEN 1 ELSE 0 END) as user_voted
                                                FROM event_options eo 
                                                LEFT JOIN event_option_votes eov ON eo.Option_ID = eov.Option_ID 
                                                WHERE eo.Content_ID = ?
                                                GROUP BY eo.Option_ID
                                                ORDER BY votes DESC";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("ii", $member_id, $event['Content_ID']);
                                        $stmt->execute();
                                        $options = $stmt->get_result();
                                        ?>
                                        
                                        <div class="options">
                                            <?php if ($options && $options->num_rows > 0): ?>
                                                <?php while ($option = $options->fetch_assoc()): ?>
                                                    <div class="option card mb-2">
                                                        <div class="card-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-8">
                                                                    <p class="mb-1">
                                                                        <strong>Date/Time:</strong> 
                                                                        <?php echo date('Y-m-d H:i', strtotime($option['Date_Time'])); ?>
                                                                    </p>
                                                                    <p class="mb-1">
                                                                        <strong>Place:</strong> 
                                                                        <?php echo htmlspecialchars($option['Place']); ?>
                                                                    </p>
                                                                    <p class="mb-1">
                                                                        <strong>Votes:</strong> 
                                                                        <?php echo $option['votes']; ?>
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-4 text-end">
                                                                    <form method="post">
                                                                        <input type="hidden" name="option_id" value="<?php echo $option['Option_ID']; ?>">
                                                                        <?php if ($option['user_voted']): ?>
                                                                            <button class="btn btn-success" disabled>Voted ✓</button>
                                                                        <?php else: ?>
                                                                            <input type="submit" name="vote" value="Vote" class="btn btn-primary">
                                                                        <?php endif; ?>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <p>No options available for this event.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No events found for this group.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function addOption() {
        const container = document.getElementById('options-container');
        const option = document.createElement('div');
        option.className = 'option mb-3';
        option.innerHTML = `
            <input type="datetime-local" name="datetime[]" required class="form-control mb-2">
            <input type="text" name="place[]" placeholder="Place" required class="form-control">
        `;
        container.appendChild(option);
    }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 