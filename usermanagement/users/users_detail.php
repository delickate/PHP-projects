<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

$id = $_GET['id'];

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die('User not found.');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Details</title>
</head>
<body>
	 <?php include '../common/navigations.php'; ?>
	 
    <h1>User Details</h1>
    <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
    <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
    <p><strong>Phone:</strong> <?php echo $user['phone']; ?></p>
    <p><strong>Status:</strong> <?php echo $user['status'] ? 'Active' : 'Inactive'; ?></p>
    <p><strong>Profile Picture:</strong><br>
        <img src="../uploads/images/profile/<?php echo $user['picture']; ?>" alt="Profile Picture" width="100">
    </p>
</body>
</html>
