<?php

function task_management_table()
{
    global $tablePrefix;
    $prefix = isset($tablePrefix) ? (string) $tablePrefix : '';
    if (preg_match('/^[A-Za-z0-9_]*$/', $prefix) !== 1) {
        throw new RuntimeException('Invalid database table prefix.');
    }
    return $prefix . 'module_tasks';
}

function task_management_connection()
{
    global $MY_DBH;
    if (!($MY_DBH instanceof mysqli)) {
        $MY_DBH = openDatabase();
    }
    return $MY_DBH;
}

function task_management_csrf_token()
{
    if (empty($_SESSION['taskManagementCsrfToken'])) {
        $_SESSION['taskManagementCsrfToken'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['taskManagementCsrfToken'];
}

function task_management_verify_csrf()
{
    return isset($_POST['csrf_token'])
        && hash_equals(task_management_csrf_token(), (string) $_POST['csrf_token']);
}

function task_management_find($id)
{
    $db = task_management_connection();
    $table = task_management_table();
    $statement = mysqli_prepare($db, "SELECT id, title, description, priority, due_date, is_complete, created_at FROM `$table` WHERE id = ?");
    mysqli_stmt_bind_param($statement, 'i', $id);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $task = mysqli_fetch_assoc($result);
    mysqli_stmt_close($statement);
    return $task ?: null;
}

function task_management_redirect($action, $parameters = array())
{
    header('Location: ' . module_url('task_management', $action, $parameters));
    exit;
}

