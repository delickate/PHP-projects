<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

//SANI:  Dynamic folder creation
createFolderIfNotExists('../uploads/images/profile/');

//SANI:  Initialize variables
$errors         = [];
$name           = $email = $phone = $status = $picture = '';
$selectedRoles  = [];

//SANI:  Fetch roles from the `roles` table
$stmt   = $pdo->query("SELECT id, name FROM roles WHERE is_default = 0");
$roles  = $stmt->fetchAll(PDO::FETCH_ASSOC);

//SANI:  Fetch user details
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
    die('Invalid user ID.');
}

$userId     = intval($_GET['id']);
$userStmt   = $pdo->prepare("SELECT u.*  FROM users as u INNER JOIN users_roles as ur ON (u.id = ur.user_id) INNER JOIN roles r ON (ur.role_id = r.id) WHERE u.id = ?");
$userStmt->execute([$userId]);
$user       = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) 
{
    die('User not found.');
}

//SANI:  Prepopulate form fields
$name       = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
$email      = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
$phone      = htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8');
$picture    = htmlspecialchars($user['picture'], ENT_QUOTES, 'UTF-8');
$status     = $user['status'];

//SANI:  Fetch user's roles
$rolesStmt      = $pdo->prepare("SELECT role_id FROM users_roles WHERE user_id = ?");
$rolesStmt->execute([$userId]);
$selectedRoles  = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);

//SANI:  Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $name           = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email          = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password       = $_POST['password']; // Hashing is done after validation
    $phone          = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $status         = isset($_POST['status']) ? 1 : 0;
    $selectedRoles  = $_POST['roles'] ?? [];
    $picture        = $user['picture'];

    //SANI:  Validate name: alphanumeric, space, hyphen, dot
    if (!preg_match('/^[a-zA-Z0-9 .\-]+$/', $name)) 
    {
        $errors[] = "Name can only contain alphanumeric characters, spaces, hyphens, and dots.";
    }

    //SANI:  Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
    {
        $errors[] = "Invalid email format.";
    }

    //SANI:  Validate password: optional but must be at least 6 characters if provided
    if (!empty($password) && strlen($password) < 6) 
    {
        $errors[] = "Password must be at least 6 characters long.";
    }

    //SANI:  Validate phone: must start with '0092' and contain only digits
    if (!preg_match('/^0092[0-9]+$/', $phone)) 
    {
        $errors[] = "Phone number must start with '0092' and contain only digits.";
    }

    //SANI:  Validate roles
    if (empty($selectedRoles)) 
    {
        $errors[] = "At least one role must be selected.";
    }

    //SANI:  Handle file upload
    if (!empty($_FILES['picture']['name'])) 
    {
        $targetDir  = "../uploads/images/profile/";
        $fileName   = time() . '_' . basename($_FILES['picture']['name']);
        $targetFile = $targetDir . $fileName;

        //SANI:  Check file type
        $fileType       = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes   = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileType, $allowedTypes)) 
        {
            $errors[] = "Profile picture must be an image (jpg, jpeg, png, gif).";
        } elseif (move_uploaded_file($_FILES['picture']['tmp_name'], $targetFile)) 
        {
            $picture = $fileName;
        } else {
                    $errors[] = "Failed to upload profile picture.";
                }
    }

    //SANI:  Update user if no errors
    if (empty($errors)) 
    {
        $updateStmt = $pdo->prepare(
            "UPDATE users SET name = ?, email = ?, phone = ?, picture = ?, status = ? WHERE id = ?"
        );
        $updateStmt->execute([$name, $email, $phone, $picture, $status, $userId]);

        //SANI:  Update password if provided
        if (!empty($password)) 
        {
            //$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $hashedPassword = md5($password);
            $passwordStmt   = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $passwordStmt->execute([$hashedPassword, $userId]);
        }

        //SANI:  Update roles
        $pdo->prepare("DELETE FROM users_roles WHERE user_id = ?")->execute([$userId]);
        $rolesStmt = $pdo->prepare("INSERT INTO users_roles (user_id, role_id) VALUES (?, ?)");

        foreach ($selectedRoles as $roleId) 
        {
            $rolesStmt->execute([$userId, $roleId]);
        }

        header('Location: users_listing.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
    <?php include '../common/navigations.php'; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php if (!empty($errors)): ?>
            <div style="color: red;">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <label for="name">Name:</label>
        <input type="text" name="name" value="<?php echo $name; ?>" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo $email; ?>" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" placeholder="Leave blank to keep unchanged"><br>

        <label for="phone">Phone:</label>
        <input type="text" name="phone" value="<?php echo $phone; ?>" required><br>

        <label for="picture">Profile Picture:</label>
        <input type="file" name="picture"><br>
        <?php if ($picture): ?>
             <img src="<?php echo IMAGE_URL.$picture; ?>" width="100" /><br>
        <?php endif; ?>

        <label for="status">Active:</label>
        <input type="checkbox" name="status" value="1" <?php echo $status ? 'checked' : ''; ?>><br>

        <label for="roles">Roles:</label>
        <select name="roles[]" multiple required>
            <?php foreach ($roles as $role): ?>
                <option value="<?php echo $role['id']; ?>" <?php echo in_array($role['id'], $selectedRoles) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Update User</button>
    </form>

    <input type="button" name="btn_back" value="Back" onclick="history.back()" />
</body>
</html>
