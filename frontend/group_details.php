<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];

// Validate group ID
$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$group_id) {
    header("Location: groups.php");
    exit();
}

try {
    // Fetch the group details from the database
    $stmt_group = $conn->prepare("SELECT g.Name, g.Description, m.Pseudonym AS Owner_Name
                           FROM `group` g
                           JOIN group_members gm ON g.Group_ID = gm.Group_ID
                           JOIN member m ON gm.Member_ID = m.Member_ID
                           WHERE g.Group_ID = ? AND gm.Is_Owner = 1
                           LIMIT 1");
    $stmt_group->bind_param('i', $group_id);
    $stmt_group->execute();
    $result_group = $stmt_group->get_result();
    $group = $result_group->fetch_assoc();

    // Check if group exists
    if (!$group) {
        throw new Exception("Group not found");
    }

    // Fetch the group members
    $stmt_members = $conn->prepare("SELECT m.Pseudonym, gm.Is_Owner
                           FROM group_members gm
                           JOIN member m ON gm.Member_ID = m.Member_ID
                           WHERE gm.Group_ID = ?
                           ORDER BY gm.Is_Owner DESC, m.Pseudonym");
    $stmt_members->bind_param('i', $group_id);
    $stmt_members->execute();
    $result_members = $stmt_members->get_result();
    $members = $result_members->fetch_all(MYSQLI_ASSOC);

    // Fetch the group content (posts, events, etc.)
    $stmt_content = $conn->prepare("SELECT c.Content_ID, c.Title, c.Body, c.Timestamp, c.Is_Event, 
                                    c.Event_Date_and_time, c.Event_Location, m.Pseudonym AS Author
                           FROM content c
                           JOIN member m ON c.Member_ID = m.Member_ID
                           WHERE c.Group_ID = ?
                           ORDER BY c.Timestamp DESC");
    $stmt_content->bind_param('i', $group_id);
    $stmt_content->execute();
    $result_content = $stmt_content->get_result();
    $content = $result_content->fetch_all(MYSQLI_ASSOC);

    // Close statements
    $stmt_group->close();
    $stmt_members->close();
    $stmt_content->close();
} catch (Exception $e) {
    // Log the error
    error_log("Group details error: " . $e->getMessage());
    
    // Redirect with error
    header("Location: groups.php?error=" . urlencode("Unable to retrieve group details"));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($group['Name']); ?> - Group Details</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-3"><?php echo htmlspecialchars($group['Name']); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($group['Description']); ?></p>
                <p>Owner: <?php echo htmlspecialchars($group['Owner_Name']); ?></p>
            </div>
            <div class="col-md-4 text-right">
                <a href="create_content.php?group_id=<?php echo $group_id; ?>" class="btn btn-primary mb-3">Create Content</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Members 
                            <span class="badge badge-secondary"><?php echo count($members); ?></span>
                        </h5>
                        <ul class="list-group">
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $member): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($member['Pseudonym']); ?>
                                        <?php if ($member['Is_Owner']): ?>
                                            <span class="badge badge-primary">Owner</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item">No members found.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Group Feed</h5>
                        <?php if (!empty($content)): ?>
                            <?php foreach ($content as $item): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($item['Title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($item['Body']); ?></p>
                                        <div class="d-flex justify-content-between">
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    Posted by <?php echo htmlspecialchars($item['Author']); ?> 
                                                    on <?php echo date('Y-m-d H:i:s', strtotime($item['Timestamp'])); ?>
                                                </small>
                                            </p>
                                            <?php if ($item['Is_Event']): ?>
                                                <span class="badge badge-info">Event</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($item['Is_Event']): ?>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    Event: <?php echo date('Y-m-d H:i', strtotime($item['Event_Date_and_time'])); ?> 
                                                    - <?php echo htmlspecialchars($item['Event_Location']); ?>
                                                </small>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No content found for this group.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>