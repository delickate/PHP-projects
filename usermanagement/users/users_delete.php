<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
    die('Invalid user ID.');
}

$userId = intval($_GET['id']);

// Prevent deleting default users
$stmt = $pdo->prepare("SELECT is_default FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || $user['is_default'] == 1) 
{
    die('Cannot delete this user.');
}

// Delete user roles
$pdo->prepare("DELETE FROM users_roles WHERE user_id = ?")->execute([$userId]);

// Delete user
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

header('Location: users_listing.php');
exit;
?>
