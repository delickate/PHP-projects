<?php
include '../common/dbconnections.php';
require_once '../common/middleware.php';
require_once '../common/helpers.php';

createFolderIfNotExists('../uploads/images/profile/');

$id = $_GET['id'];

//SANI: declaring variables
$errors         = [];
$name           = $email = $phone = $status = $picture = '';
$selectedRoles  = [];

// Fetch user
$stmt = $pdo->prepare("SELECT u.*, r.id as user_role  FROM users as u INNER JOIN users_roles as ur ON (u.id = ur.user_id) INNER JOIN roles r ON (ur.role_id = r.id) WHERE u.id = ? AND u.is_default = 0");
$stmt->execute([$id]);
$user = $stmt->fetch();

//SANI:  Fetch roles from the `roles` table
$role_query = $pdo->query("SELECT id, name FROM roles WHERE is_default = 0");
$roles = $role_query->fetchAll(PDO::FETCH_ASSOC);

if (!$user) {
    die('Unauthorized access or user cannot be edited.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $name 		= htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email 		= htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    //$password 	= password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone 		= htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');;
    $status 	= isset($_POST['status']) ? 1 : 0;
    $picture 	= $user['picture'];

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
    // if (strlen($password) < 6) 
    // {
    //     $errors[] = "Password must be at least 6 characters long.";
    // }

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
//print_r($errors);
    //SANI:  If no errors, insert the user and assign roles
    if (empty($errors)) 
    {

    	// Update user
	    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, picture = ?, status = ? WHERE id = ?");
	    $stmt->execute([$name, $email, $phone, $picture, $status, $id]);

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
<?php if (!empty($errors)): ?>
            <div style="color: red;">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="name">Name:</label><input type="text" name="name" value="<?php echo $user['name']; ?>" required><br>
        <label for="email">Email:</label><input type="email" name="email" value="<?php echo $user['email']; ?>" required><br>
        <label for="phone">Phone:</label><input type="text" name="phone" value="<?php echo $user['phone']; ?>"><br>
        <label for="picture">Profile Picture:</label><input type="file" name="picture"><br><img src="<?php echo IMAGE_URL.$user['picture']; ?>" width="100" />
        <label for="status">Active:</label><input type="checkbox" name="status" value="1" <?php if ($user['status']) echo 'checked'; ?>><br>
        <label for="roles">Roles:</label>
        <select name="roles[]" multiple required>
            <?php foreach ($roles as $role): ?>
                <option value="<?php echo $role['id']; ?>" <?php echo ($role['id'] == $user['user_role']) ? 'selected="selected"' : ''; ?>>
                    <?php echo htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit">Update User</button>
    </form>
</body>
</html>
