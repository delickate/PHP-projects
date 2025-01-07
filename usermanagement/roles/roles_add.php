<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

$errors 			= [];
$name 				= '';
$status 			= 1;
$rolePermissions 	= [];

// Fetch sections and rights
$sectionsStmt 		= $pdo->query("SELECT id, name FROM modules");
$sections 			= $sectionsStmt->fetchAll(PDO::FETCH_ASSOC);

$rightsStmt 		= $pdo->query("SELECT id, name FROM rights");
$rights 			= $rightsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $name 				= htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $status 			= isset($_POST['status']) ? 1 : 0;
    $rolePermissions 	= $_POST['permissions'] ?? []; // Permissions data

    // Validate name
    if (empty($name)) 
    {
        $errors[] = "Role name is required.";
    }

    // If no errors, insert/update role
    if (empty($errors)) 
    {
        // Insert/Update Role
        if (isset($_POST['id']) && is_numeric($_POST['id'])) 
        {
            $roleId = intval($_POST['id']);
            $stmt 	= $pdo->prepare("UPDATE roles SET name = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $status, $roleId]);

            // Delete old permissions
            $pdo->prepare("DELETE FROM roles_modules_permissions WHERE role_id = ?")->execute([$roleId]);
        } else {
		            $stmt = $pdo->prepare("INSERT INTO roles (name, status, is_default) VALUES (?, ?, 0)");
		            $stmt->execute([$name, $status]);
		            $roleId = $pdo->lastInsertId();
		        }

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
    <title><?php echo isset($_GET['id']) ? 'Edit Role' : 'Add Role'; ?></title>
</head>
<body>
    <h1><?php echo isset($_GET['id']) ? 'Edit Role' : 'Add Role'; ?></h1>
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

        <button type="submit"><?php echo isset($_GET['id']) ? 'Update Role' : 'Add Role'; ?></button>
    </form>
</body>
</html>
