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

try {
    // Fetch the group ID
    if (!isset($_GET['id'])) {
        throw new Exception("Group ID is not provided.");
    }
    $group_id = intval($_GET['id']);

    // Fetch the group details
    $stmt = $conn->prepare("SELECT Name, Description FROM `group` WHERE Group_ID = ?");
    $stmt->bind_param('i', $group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Group not found.");
    }

    $group = $result->fetch_assoc();

    // Check if the user is the group owner
    $stmt = $conn->prepare("SELECT Is_Owner FROM group_members WHERE Member_ID = ? AND Group_ID = ?");
    $stmt->bind_param('ii', $member_id, $group_id);
    $stmt->execute();
    $stmt->bind_result($is_owner);
    $stmt->fetch();

    if (!$is_owner) {
        // Redirect non-owners to the group details page
        header("Location: group_details.php?id=$group_id");
        exit();
    }
    $stmt->close();

    // Check if the user has the required privilege level to remove members
    $stmt = $conn->prepare("SELECT Privilege_Level FROM member WHERE Member_ID = ?");
    $stmt->bind_param('i', $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $privilege_level = $row['Privilege_Level'];
    } else {
        // Handle the case when the user is not found in the database
        echo "Error: User not found.";
        exit();
    }

    $stmt->close();

    // Handle form submission for editing the group

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_members'])) {
        $emails = isset($_POST['emails']) ? array_map('trim', explode(',', $_POST['emails'])) : [];
        $pseudonyms = isset($_POST['pseudonyms']) ? array_map('trim', explode(',', $_POST['pseudonyms'])) : [];
        $dates_of_birth = isset($_POST['dates_of_birth']) ? array_map('trim', explode(',', $_POST['dates_of_birth'])) : [];
    
        if (empty($emails) || empty($pseudonyms) || empty($dates_of_birth)) {
            $error_message = "Please provide email addresses, pseudonyms, and dates of birth for all members you want to add.";
        } else {
            $member_count = count($emails);
            if (count($pseudonyms) !== $member_count || count($dates_of_birth) !== $member_count) {
                $error_message = "The number of email addresses, pseudonyms, and dates of birth must match.";
            } else {
                try {
                    $conn->autocommit(FALSE);
    
                    for ($i = 0; $i < $member_count; $i++) {
                        $email = $emails[$i];
                        $pseudonym = $pseudonyms[$i];
                        $date_of_birth = $dates_of_birth[$i];
    
                        // Check if the member already exists
                        $stmt = $conn->prepare("SELECT Member_ID FROM member WHERE Email = ?");
                        $stmt->bind_param('s', $email);
                        $stmt->execute();
                        $result = $stmt->get_result();
    
                        if ($result->num_rows > 0) {
                            $member_id = $result->fetch_assoc()['Member_ID'];
                        } else {
                            // Insert a new member
                            $stmt = $conn->prepare("INSERT INTO member (Email, Pseudonym, Date_of_Birth) VALUES (?, ?, ?)");
                            $stmt->bind_param('sss', $email, $pseudonym, $date_of_birth);
                            $stmt->execute();
                            $member_id = $conn->insert_id;
                        }
    
                        // Add the member to the group
                        $stmt = $conn->prepare("INSERT INTO group_members (Member_ID, Group_ID, Is_Owner) VALUES (?, ?, 0)");
                        $stmt->bind_param('ii', $member_id, $group_id);
                        $stmt->execute();
                    }
    
                    $conn->commit();
                    $success_message = "Members added successfully.";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_message = "Error adding members: " . $e->getMessage();
                } finally {
                    $conn->autocommit(TRUE);
                }
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_members'])) {
        $member_ids = isset($_POST['member_ids']) ? array_map('intval', explode(',', $_POST['member_ids'])) : [];
    
        if (empty($member_ids)) {
            $error_message = "Please provide the Member IDs of the members you want to remove.";
        } else {
            try {
                $conn->autocommit(FALSE);
    
                foreach ($member_ids as $member_id) {
                    // Remove the member from the group
                    $stmt = $conn->prepare("DELETE FROM group_members WHERE Member_ID = ? AND Group_ID = ?");
                    $stmt->bind_param('ii', $member_id, $group_id);
                    $stmt->execute();
                }
    
                $conn->commit();
                $success_message = "Members removed successfully.";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Error removing members: " . $e->getMessage();
            } finally {
                $conn->autocommit(TRUE);
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_members']) && !isset($_POST['remove_members'])) {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

        if (empty($name) || empty($description)) {
            throw new Exception("Name and Description cannot be empty.");
        }

        // Update the group details in the database
        $stmt = $conn->prepare("UPDATE `group` SET Name = ?, Description = ? WHERE Group_ID = ?");
        $stmt->bind_param('ssi', $name, $description, $group_id);
        $stmt->execute();

        // Redirect to the group details page
        header("Location: group_details.php?id=$group_id");
        exit();
    }
} catch (Exception $e) {
    error_log("Error in edit_group.php: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
} finally {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Group</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container my-5">
        <h1>Edit Group</h1>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (isset($group)): ?>
        <form method="POST" action="edit_group.php?id=<?php echo $group_id; ?>">
            <div class="form-group">
                <label for="name">Group Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($group['Name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Group Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($group['Description']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="group_details.php?id=<?php echo $group_id; ?>" class="btn btn-secondary">Cancel</a>
        </form>

        <?php if ($privilege_level >= 2): ?>
            <h2>Remove Members</h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $group_id); ?>">
                <div class="form-group">
                    <label for="member_ids">Member IDs (comma-separated)</label>
                    <input type="text" class="form-control" id="member_ids" name="member_ids" required>
                </div>
                <button type="submit" class="btn btn-danger" name="remove_members">Remove Members</button>
                <button type="submit" class="btn btn-danger" name="remove_members">Ban Member</button>
            </form>
        <?php endif; ?>

        <div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Invite Members</h5>
    </div>
    <div class="card-body">
        <form action="invite_to_group.php" method="POST" class="invite-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
            </div>
            <div class="form-group">
                <label for="pseudonym">Pseudonym</label>
                <input type="text" class="form-control" id="pseudonym" name="pseudonym" placeholder="Enter pseudonym" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
            </div>
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <button type="submit" class="btn btn-primary">Invite</button>
        </form>
        <div id="invite-response" class="mt-3"></div>
    </div>
</div>
<?php else: ?>
    <p class="alert alert-danger">Group details not found.</p>
<?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
