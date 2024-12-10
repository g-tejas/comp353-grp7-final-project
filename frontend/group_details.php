<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; 

// user is logged in?
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];

// check group ID
$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$group_id) {
    header("Location: groups.php");
    exit();
}

try {
    // fetch the group details
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

    // Fetch the group content
$stmt_content = $conn->prepare("SELECT c.Content_ID, c.Title, c.Body, c.Timestamp, c.Is_Event, 
c.Event_Date_and_time, c.Event_Location, m.Pseudonym AS Author,
cc.View, c.Media_Path
FROM content c
JOIN member m ON c.Member_ID = m.Member_ID
JOIN content_classification cc ON c.Content_ID = cc.Content_ID
WHERE c.Group_ID = ?
ORDER BY c.Timestamp DESC");
$stmt_content->bind_param('i', $group_id);
$stmt_content->execute();
$result_content = $stmt_content->get_result();
$content = $result_content->fetch_all(MYSQLI_ASSOC);

    // Fetch active gift exchanges for this group
    $stmt_exchanges = $conn->prepare("SELECT ge.*, m.Pseudonym as Creator_Name, 
        (SELECT COUNT(*) FROM gift_exchange_participants WHERE Exchange_ID = ge.Exchange_ID) as Participant_Count
        FROM gift_exchange ge
        JOIN member m ON ge.Created_By = m.Member_ID
        WHERE ge.Group_ID = ? AND ge.Status = 'Active'
        ORDER BY ge.Created_At DESC");
    $stmt_exchanges->bind_param('i', $group_id);
    $stmt_exchanges->execute();
    $result_exchanges = $stmt_exchanges->get_result();
    $active_exchanges = $result_exchanges->fetch_all(MYSQLI_ASSOC);
    $stmt_exchanges->close();

    // Check if user is part of any active exchanges
    $stmt_participant = $conn->prepare("SELECT gep.Exchange_ID, gep.Receiver_ID, m.Pseudonym as Receiver_Name
        FROM gift_exchange_participants gep
        LEFT JOIN member m ON gep.Receiver_ID = m.Member_ID
        WHERE gep.Giver_ID = ? AND Exchange_ID IN 
        (SELECT Exchange_ID FROM gift_exchange WHERE Group_ID = ? AND Status = 'Active')");
    $stmt_participant->bind_param('ii', $member_id, $group_id);
    $stmt_participant->execute();
    $result_participant = $stmt_participant->get_result();
    $user_exchanges = $result_participant->fetch_all(MYSQLI_ASSOC);
    $stmt_participant->close();

    // Close statements
    $stmt_group->close();
    $stmt_members->close();
    $stmt_content->close();

    // Fetch the comments for each content item
    $stmt_comments = $conn->prepare("SELECT c.Body, c.Timestamp, m.Pseudonym AS Author
                                     FROM comment c
                                     JOIN member m ON c.Member_ID = m.Member_ID
                                     WHERE c.Content_ID = ?
                                     ORDER BY c.Timestamp ASC");

    foreach ($content as &$item) {
        $stmt_comments->bind_param('i', $item['Content_ID']);
        $stmt_comments->execute();
        $result_comments = $stmt_comments->get_result();
        $item['comments'] = $result_comments->fetch_all(MYSQLI_ASSOC);
    }

    $stmt_comments->close();

    
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

                <!-- Gift Exchange Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Gift Exchanges</h5>
                        
                        <!-- User's Active Exchanges -->
                        <?php if (!empty($user_exchanges)): ?>
                            <div class="alert alert-info">
                                <h6>Your Active Exchanges</h6>
                                <?php foreach ($user_exchanges as $exchange): ?>
                                    <div class="mb-2">
                                        <?php if ($exchange['Receiver_ID']): ?>
                                            You are gifting to: <strong><?php echo htmlspecialchars($exchange['Receiver_Name']); ?></strong>
                                        <?php else: ?>
                                            Waiting for gift assignment...
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Active Exchanges List -->
                        <?php if (!empty($active_exchanges)): ?>
                            <?php foreach ($active_exchanges as $exchange): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            Gift Exchange
                                            <span class="badge badge-primary"><?php echo $exchange['Participant_Count']; ?> participants</span>
                                        </h6>
                                        <p class="card-text">
                                            <small>
                                                Budget: $<?php echo $exchange['Budget_Min']; ?> - $<?php echo $exchange['Budget_Max']; ?><br>
                                                Dates: <?php echo date('M d', strtotime($exchange['Start_Date'])); ?> - 
                                                      <?php echo date('M d, Y', strtotime($exchange['End_Date'])); ?><br>
                                                Created by: <?php echo htmlspecialchars($exchange['Creator_Name']); ?>
                                            </small>
                                        </p>
                                        <form action="join_gift_exchange.php" method="POST" class="mt-2">
                                            <input type="hidden" name="exchange_id" value="<?php echo $exchange['Exchange_ID']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Join Exchange</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="card-text">No active gift exchanges.</p>
                        <?php endif; ?>

                        <!-- Create New Exchange Button -->
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#createExchangeModal">
                            Create New Gift Exchange
                        </button>
                    </div>
                </div>

                <!-- Create Exchange Modal -->
                <div class="modal fade" id="createExchangeModal" tabindex="-1" role="dialog" aria-labelledby="createExchangeModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createExchangeModalLabel">Create Gift Exchange</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="create_gift_exchange.php" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                    
                                    <div class="form-group">
                                        <label>Budget Range</label>
                                        <div class="row">
                                            <div class="col">
                                                <input type="number" class="form-control" name="budget_min" placeholder="Min $" required>
                                            </div>
                                            <div class="col">
                                                <input type="number" class="form-control" name="budget_max" placeholder="Max $" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Exchange Dates</label>
                                        <div class="row">
                                            <div class="col">
                                                <input type="date" class="form-control" name="start_date" required>
                                            </div>
                                            <div class="col">
                                                <input type="date" class="form-control" name="end_date" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Create Exchange</button>
                                </div>
                            </form>
                        </div>
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
                                        <!-- Display media if available -->
                                        <?php if (!empty($item['Media_Path'])): ?>
                                            <?php $mediaPath = htmlspecialchars($item['Media_Path']); ?>
                                            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $mediaPath)): ?>
                                                <img src="<?php echo $mediaPath; ?>" alt="Post Image" style="max-width: 100%;">
                                            <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $mediaPath)): ?>
                                                <video controls style="max-width: 50%;">
                                                    <source src="<?php echo $mediaPath; ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($item['View'] === 'Public'): ?>
                                    <hr>
                                    <h6>Comments</h6>
                                    <?php if (!empty($item['comments'])): ?>
                                        <?php foreach ($item['comments'] as $comment): ?>
                                            <div class="card mb-2">
                                                    <div class="card-body">
                                                        <p class="card-text"><?php echo htmlspecialchars($comment['Body']); ?></p>
                                                        <div class="d-flex justify-content-between">
                                                            <p class="card-text">
                                                                <small class="text-muted">
                                                                    Posted by <?php echo htmlspecialchars($comment['Author']); ?>
                                                                    on <?php echo date('Y-m-d H:i:s', strtotime($comment['Timestamp'])); ?>
                                                                </small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="card-text">No comments yet.</p>
                                    <?php endif; ?>
                                    <form action="add_comment.php" method="POST">
                                            <input type="hidden" name="content_id" value="<?php echo $item['Content_ID']; ?>">
                                            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                            <div class="form-group">
                                                <textarea class="form-control" name="body" rows="2" placeholder="Add a comment..." required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                                        </form>
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