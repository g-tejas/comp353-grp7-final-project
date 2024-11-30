<?php
session_start();
include 'includes/header.php';

// Fetch the group details from the database
$group_id = $_GET['id'];
$stmt = $conn->prepare("SELECT g.Name, g.Description, m.Pseudonym AS Owner_Name
                       FROM `group` g
                       JOIN group_members gm ON g.Group_ID = gm.Group_ID
                       JOIN member m ON gm.Member_ID = m.Member_ID
                       WHERE g.Group_ID = :group_id AND gm.Is_Owner = 1
                       LIMIT 1");
$stmt->bindParam(':group_id', $group_id);
$stmt->execute();
$group = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch the group members
$stmt = $conn->prepare("SELECT m.Pseudonym, gm.Is_Owner
                       FROM group_members gm
                       JOIN member m ON gm.Member_ID = m.Member_ID
                       WHERE gm.Group_ID = :group_id");
$stmt->bindParam(':group_id', $group_id);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the group content (posts, events, etc.)
$stmt = $conn->prepare("SELECT c.Title, c.Body, c.Timestamp, c.Is_Event, c.Event_Date_and_time, c.Event_Location, m.Pseudonym AS Author
                       FROM content c
                       JOIN member m ON c.Member_ID = m.Member_ID
                       WHERE c.Group_ID = :group_id
                       ORDER BY c.Timestamp DESC");
$stmt->bindParam(':group_id', $group_id);
$stmt->execute();
$content = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
    <h1><?php echo htmlspecialchars($group['Name']); ?></h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Group Information</h5>
            <p class="card-text"><?php echo htmlspecialchars($group['Description']); ?></p>
            <p class="card-text">Owner: <?php echo htmlspecialchars($group['Owner_Name']); ?></p>
            <?php if (isset($_SESSION['user_id']) && $group['Owner_Name'] === $_SESSION['username']): ?>
                <a href="edit_group.php?id=<?php echo $group_id; ?>" class="btn btn-secondary">Manage Group</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Members</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($members as $member): ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($member['Pseudonym']); ?>
                        <?php if ($member['Is_Owner']): ?>
                            <span class="badge badge-primary">Owner</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Group Feed</h5>
            <?php foreach ($content as $item): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['Title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($item['Body']); ?></p>
                        <p class="card-text">
                            <small class="text-muted">Posted by <?php echo htmlspecialchars($item['Author']); ?> on <?php echo date('Y-m-d H:i:s', strtotime($item['Timestamp'])); ?></small>
                        </p>
                        <?php if ($item['Is_Event']): ?>
                            <p class="card-text">
                                <small class="text-muted">Event: <?php echo date('Y-m-d H:i', strtotime($item['Event_Date_and_time'])); ?> - <?php echo htmlspecialchars($item['Event_Location']); ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>