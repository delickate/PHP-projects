<?php 

define('BASE_URL', 'http://localhost:98/PHP/PHP-projects/usermanagement');
define('IMAGE_URL',BASE_URL.'/uploads/images/profile/');


//SANI: check if user session exists
if (!isset($_SESSION['user_id'])) 
{
    header('Location: login.php');
    exit;
}
?>