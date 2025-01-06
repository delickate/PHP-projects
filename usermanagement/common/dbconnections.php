<?php 
//SANI: Database connections
$host = 'localhost';
$db   = 'sani_usermanagement';
$user = 'localadmin';
$pass = 'localadmin';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

try {
        $pdo = new PDO($dsn, $user, $pass, $options);

    } catch (PDOException $e) 
            {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self';");
header('X-Frame-Options: SAMEORIGIN');

session_start();

//SANI: CSRF token generation
if (empty($_SESSION['csrf_token'])) 
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>