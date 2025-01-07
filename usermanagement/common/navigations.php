<ul>
        <?php  foreach ($modules as $module): ?>
            <li><a href="<?php echo BASE_URL; ?><?php echo $module['url']; ?>"><?php echo $module['name']; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <a href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
    <br /><br />