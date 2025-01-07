<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

$errors             = [];
$name               = '';
$status             = 1;
$rolePermissions    = [];

$roleId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch sections and rights
$sectionsStmt       = $pdo->query("SELECT id, name FROM modules");
$sections           = $sectionsStmt->fetchAll(PDO::FETCH_ASSOC);

$rightsStmt         = $pdo->query("SELECT id, name FROM rights");
$rights             = $rightsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch role details and permissions if editing
if ($roleId) 
{
    // Fetch role details
    $roleStmt 	= $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $roleStmt->execute([$roleId]);
    $role 		= $roleStmt->fetch(PDO::FETCH_ASSOC);

    if ($role) 
    {
        $name 	= $role['name'];
        $status = $role['status'];

        // Fetch role permissions
        $permissionsStmt = $pdo->prepare("
            SELECT rmp.module_id, rmp_r.rights_id 
            FROM roles_modules_permissions rmp
            INNER JOIN roles_modules_permissions_rights rmp_r ON rmp.id = rmp_r.roles_modules_permissions_id
            WHERE rmp.role_id = ?
        ");
        $permissionsStmt->execute([$roleId]);
        $permissions = $permissionsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format permissions as an associative array
        foreach ($permissions as $permission) 
        {
            $rolePermissions[$permission['module_id']][] = $permission['rights_id'];
        }

    } else {
		        $errors[] = "Role not found.";
		    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $name               = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $status             = isset($_POST['status']) ? 1 : 0;
    $rolePermissions    = $_POST['permissions'] ?? []; // Permissions data

    // Validate name
    if (empty($name)) 
    {
        $errors[] = "Role name is required.";
    }

    // If no errors, update role
    if (empty($errors)) 
    {
        $stmt = $pdo->prepare("UPDATE roles SET name = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $status, $roleId]);

        // Delete old permissions
		$deleteRightsStmt = $pdo->prepare("
		    DELETE FROM roles_modules_permissions_rights 
		    WHERE roles_modules_permissions_id IN (
		        SELECT id FROM roles_modules_permissions WHERE role_id = ?
		    )
		");
		$deleteRightsStmt->execute([$roleId]);

		$deletePermissionsStmt = $pdo->prepare("DELETE FROM roles_modules_permissions WHERE role_id = ?");
		$deletePermissionsStmt->execute([$roleId]);

        // Insert permissions
        foreach ($rolePermissions as $moduleId => $rights) 
        {
            $stmt = $pdo->prepare("INSERT INTO roles_modules_permissions (role_id, module_id) VALUES (?, ?)");
            $stmt->execute([$roleId, $moduleId]);
            $roleModulePermissionId = $pdo->lastInsertId();

            foreach ($rights as $rightId) 
            {
                $stmt = $pdo->prepare("INSERT INTO roles_modules_permissions_rights (roles_modules_permissions_id, rights_id) VALUES (?, ?)");
                $stmt->execute([$roleModulePermissionId, $rightId]);
            }
        }

        header('Location: roles_listing.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Role</title>
</head>
<body>
    <h1>Edit Role</h1>
    <?php include '../common/navigations.php'; ?>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Role Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required><br>

        <label for="status">Active:</label>
        <input type="checkbox" name="status" value="1" <?php echo $status ? 'checked' : ''; ?>><br>

        <h3>Permissions</h3>
        <?php foreach ($sections as $module): ?>
            <div>
                <strong><?php echo htmlspecialchars($module['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php foreach ($rights as $right): ?>
                    <label>
                        <input type="checkbox" name="permissions[<?php echo $module['id']; ?>][]" value="<?php echo $right['id']; ?>"
                        <?php echo isset($rolePermissions[$module['id']]) && in_array($right['id'], $rolePermissions[$module['id']]) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($right['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <input type="hidden" name="id" value="<?php echo $roleId; ?>">
        <button type="submit">Update Role</button>
    </form>
</body>
</html>
