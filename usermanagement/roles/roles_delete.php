<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

// Check if role ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
    header('Location: roles_listing.php');
    exit;
}

$roleId = intval($_GET['id']);

try {
    // Start a transaction
    $pdo->beginTransaction();

    // Delete rights associated with this role
    $deleteRightsStmt = $pdo->prepare("
        DELETE FROM roles_modules_permissions_rights 
        WHERE roles_modules_permissions_id IN (
            SELECT id FROM roles_modules_permissions WHERE role_id = ?
        )
    ");
    $deleteRightsStmt->execute([$roleId]);

    // Delete permissions associated with this role
    $deletePermissionsStmt = $pdo->prepare("DELETE FROM roles_modules_permissions WHERE role_id = ?");
    $deletePermissionsStmt->execute([$roleId]);

    // Delete the role itself
    $deleteRoleStmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
    $deleteRoleStmt->execute([$roleId]);

    // Commit the transaction
    $pdo->commit();

    // Redirect to roles listing
    header('Location: roles_listing.php?success=1');
    exit;
} catch (PDOException $e) {
    // Rollback the transaction in case of an error
    $pdo->rollBack();

    // Log or display the error
    error_log("Error deleting role: " . $e->getMessage());
    header('Location: roles_listing.php?error=1');
    exit;
}
?>
