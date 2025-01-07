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

// Fetch role details
$roleStmt = $pdo->prepare("SELECT id, name, status FROM roles WHERE id = ?");
$roleStmt->execute([$roleId]);
$role = $roleStmt->fetch(PDO::FETCH_ASSOC);

if (!$role) 
{
    header('Location: roles_listing.php?error=RoleNotFound');
    exit;
}

// Fetch permissions for the role
$permissionsStmt = $pdo->prepare("
    SELECT 
        m.name AS module_name,
        r.name AS right_name
    FROM 
        roles_modules_permissions p
    INNER JOIN 
        modules m ON p.module_id = m.id
    INNER JOIN 
        roles_modules_permissions_rights pmr ON p.id = pmr.roles_modules_permissions_id
    INNER JOIN 
        rights r ON pmr.rights_id = r.id
    WHERE 
        p.role_id = ?
");
$permissionsStmt->execute([$roleId]);
$permissions = $permissionsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Role Details</title>
</head>
<body>
    <h1>Role Details</h1>
    <?php include '../common/navigations.php'; ?>

    <h2>Role Information</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><strong>Status:</strong> <?php echo $role['status'] ? 'Active' : 'Inactive'; ?></p>

    <h2>Permissions</h2>
    <?php if (!empty($permissions)): ?>
        <table border="1" width="100%">
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Rights</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Group permissions by module
                $groupedPermissions = [];
                foreach ($permissions as $permission) 
                {
                    $groupedPermissions[$permission['module_name']][] = $permission['right_name'];
                }
                ?>
                <?php foreach ($groupedPermissions as $moduleName => $rights): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo implode(', ', array_map('htmlspecialchars', $rights)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No permissions assigned to this role.</p>
    <?php endif; ?>

   <!--  <p>
        <a href="roles_edit.php?id=<?php //echo $role['id']; ?>">Edit Role</a> |
        <a href="roles_delete.php?id=<?php //echo $role['id']; ?>" onclick="return confirm('Are you sure you want to delete this role?')">Delete Role</a> -->
    </p>
</body>
</html>
