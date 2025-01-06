<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

// Dynamic folder creation
createFolderIfNotExists('../uploads/images/profile/');

//SANI: declaring variables
$errors         = [];
$name           = $email = $phone = $status = $picture = '';
$selectedRoles  = [];

//SANI:  Fetch roles from the `roles` table
$stmt = $pdo->query("SELECT id, name FROM roles WHERE is_default = 0");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

//SANI: if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $name     = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email    = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password']; // Hashing is done after validation
    $phone    = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $status   = isset($_POST['status']) ? 1 : 0;
    $selectedRoles = $_POST['roles'] ?? []; // Get selected roles
    $picture = '';

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

    //SANI:  Validate password: required and at least 6 characters
    if (strlen($password) < 6) 
    {
        $errors[] = "Password must be at least 6 characters long.";
    }

    //SANI:  Validate phone: must start with '0092' and contain only digits
    if (!preg_match('/^0092[0-9]+$/', $phone)) 
    {
        $errors[] = "Phone number must start with '0092' and contain only digits.";
    }

    // Validate roles
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
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

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

    //SANI:  If no errors, insert the user and assign roles
    if (empty($errors)) 
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        //SANI:  Insert user into `users` table
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, picture, status, is_default) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$name, $email, $hashedPassword, $phone, $picture, $status]);
        $userId = $pdo->lastInsertId();

        //SANI:  Insert roles into `users_roles` table
        $stmt = $pdo->prepare("INSERT INTO users_roles (user_id, role_id) VALUES (?, ?)");
        
        foreach ($selectedRoles as $roleId) 
        {
            $stmt->execute([$userId, $roleId]);
        }

        header('Location: users_listing.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
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
        <input type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <label for="phone">Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" required><br>

        <label for="picture">Profile Picture:</label>
        <input type="file" name="picture"><br>

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

        <button type="submit">Add User</button>
    </form>
</body>
</html>
