<?php
require_once './common/dbconnections.php';

// login.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) 
    {
        die('CSRF token validation failed.');
    }

    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password = md5(htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8'));

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND password = ? AND status = 1');
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>

        <button type="submit">Login</button>
    </form>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
</body>
</html>