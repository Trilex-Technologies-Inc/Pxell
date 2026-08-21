<?php

require_once __DIR__ . '/include.php';
$db = task_management_connection();
$table = task_management_table();
if (!mysqli_query($db, "DROP TABLE IF EXISTS `$table`")) {
    throw new RuntimeException(mysqli_error($db));
}

