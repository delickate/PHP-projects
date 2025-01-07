<?php 

//SANI: Create folders if not exists
function createFolderIfNotExists($path) 
{
    if (!is_dir($path)) 
    {
        mkdir($path, 0777, true);
    }
}

//SANI: web portal base url
// function base_url()
// {
// 	return BASE_URL;
// }

function base_url($path = '') 
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $protocol . '://' . $host . $scriptDir . '/' . ltrim($path, '/');
}



$user_id =  $_SESSION['user_id'];
$currentUserId = $_SESSION['user_id'] ?? 0;

// Fetch user's roles
$stmt = $pdo->prepare('SELECT role_id FROM users_roles WHERE user_id = ?');
$stmt->execute([$user_id]);
$user_roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch accessible modules
$placeholders = implode(',', array_fill(0, count($user_roles), '?'));
$stmt = $pdo->prepare("SELECT m.name, m.url FROM modules m
    JOIN roles_modules_permissions rmp ON m.id = rmp.module_id
    WHERE rmp.role_id IN ($placeholders) AND m.status = 1");
$stmt->execute($user_roles);
$modules = $stmt->fetchAll();




// Check if a user has the right to add
function hasAddRight($userId, $moduleId, $pdo) 
{
    return hasRight($userId, $moduleId, 'Add', $pdo);
}

// Check if a user has the right to edit
function hasEditRight($userId, $moduleId, $pdo) 
{
    return hasRight($userId, $moduleId, 'Edit', $pdo);
}

// Check if a user has the right to delete
function hasDeleteRight($userId, $moduleId, $pdo) 
{
    return hasRight($userId, $moduleId, 'Delete', $pdo);
}

// Generic function to check a specific right
function hasRight($userId, $moduleId, $rightName, $pdo) 
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM roles_modules_permissions_rights pmr
        INNER JOIN roles_modules_permissions p ON pmr.roles_modules_permissions_id = p.id
        INNER JOIN rights r ON pmr.rights_id = r.id
        INNER JOIN users_roles ur ON p.role_id = ur.role_id
        WHERE ur.user_id = ? AND p.module_id = ? AND r.name = ?
    ");
    $stmt->execute([$userId, $moduleId, $rightName]); //echo $stmt->fetchColumn();
    return $stmt->fetchColumn() > 0;
}
?>
