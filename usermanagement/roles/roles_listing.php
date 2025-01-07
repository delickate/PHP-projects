<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch roles with pagination
$stmt = $pdo->prepare("SELECT * FROM roles  LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$roles = $stmt->fetchAll();

// Fetch total roles count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM roles");
$totalroles = $totalStmt->fetchColumn();
$totalPages = ceil($totalroles / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Listings</title>
</head>
<body>
    <h1>User Listings</h1>
    <?php include '../common/navigations.php'; ?>

    <?php if (hasAddRight($currentUserId, 4, $pdo)): ?>
    <p align="right"><a href="<?php echo BASE_URL; ?>/roles/roles_add.php">Add</a></p>
    <?php endif; ?>

    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Name</th>
                
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?php echo $role['name']; ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/roles/roles_detail.php?id=<?php echo $role['id']; ?>">View</a>
                        <?php if ($role['is_default'] == 0): ?>
                            <?php if (hasEditRight($currentUserId, 4, $pdo)): ?>
                            <a href="<?php echo BASE_URL; ?>/roles/roles_edit.php?id=<?php echo $role['id']; ?>">Edit</a>
                            <?php endif; ?>

                            <?php if (hasDeleteRight($currentUserId, 4, $pdo)): ?>
                            <a href="<?php echo BASE_URL; ?>/roles/roles_delete.php?id=<?php echo $role['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
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
