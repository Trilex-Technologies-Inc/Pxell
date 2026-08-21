<?php

require_once __DIR__ . '/include.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !task_management_verify_csrf()) {
    http_response_code(403);
    echo '<div class="alert alert-danger">The request could not be verified.</div>';
    return;
}
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$db = task_management_connection();
$table = task_management_table();
$statement = mysqli_prepare($db, "DELETE FROM `$table` WHERE id = ?");
mysqli_stmt_bind_param($statement, 'i', $id);
if (!mysqli_stmt_execute($statement)) {
    throw new RuntimeException(mysqli_stmt_error($statement));
}
mysqli_stmt_close($statement);
task_management_redirect('main', array('message' => 'Task deleted.'));

