<?php

require_once __DIR__ . '/include.php';
$db = task_management_connection();
$table = task_management_table();
$sql = "CREATE TABLE IF NOT EXISTS `$table` (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('Low', 'Normal', 'High') NOT NULL DEFAULT 'Normal',
    due_date DATE DEFAULT NULL,
    is_complete TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY task_status_due (is_complete, due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($db, $sql)) {
    throw new RuntimeException(mysqli_error($db));
}

