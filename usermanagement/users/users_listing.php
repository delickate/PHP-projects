<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch users with pagination
$stmt = $pdo->prepare("SELECT u.*, r.`name` as role_name FROM users as u INNER JOIN users_roles as ur ON (u.id = ur.user_id) INNER JOIN roles r ON (ur.role_id = r.id)  LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Fetch total users count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM users as u INNER JOIN users_roles as ur ON (u.id = ur.user_id)  INNER JOIN roles r ON (ur.role_id = r.id) ");
$totalUsers = $totalStmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Listings</title>
</head>
<body>
    <h1>User Listings</h1>
    <?php include '../common/navigations.php'; //echo $currentUserId; ?>
    <?php if (hasAddRight($currentUserId, 3, $pdo)): ?>
    <p align="right"><a href="<?php echo BASE_URL; ?>/users/users_add.php">Add</a></p>
    <?php endif; ?>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Roles</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['phone']; ?></td>
                    <td><?php echo $user['role_name']; ?></td>
                    <td><img src="<?php echo IMAGE_URL.$user['picture']; ?>" width="100" /></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/users/users_detail.php?id=<?php echo $user['id']; ?>">View</a>
                        <?php if ($user['is_default'] == 0): ?>
                            <?php if (hasEditRight($currentUserId, 3, $pdo)): ?>
                            <a href="<?php echo BASE_URL; ?>/users/users_edit.php?id=<?php echo $user['id']; ?>">Edit</a>
                            <?php endif; ?>

                            <?php if (hasDeleteRight($currentUserId, 3, $pdo)): ?>
                            <a href="<?php echo BASE_URL; ?>/users/users_delete.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            <?php endif; ?>
                      
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>
