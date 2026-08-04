<?php

require_once __DIR__ . '/include.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$task = task_management_find($id);
if ($task === null) {
    http_response_code(404);
    echo '<div class="alert alert-danger">Task not found.</div>';
    return;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $priority = (string) ($_POST['priority'] ?? 'Normal');
    $dueDate = trim((string) ($_POST['due_date'] ?? ''));
    $complete = isset($_POST['is_complete']) ? 1 : 0;
    if (!task_management_verify_csrf()) {
        $error = 'The request could not be verified.';
    } elseif ($title === '' || !in_array($priority, array('Low', 'Normal', 'High'), true)) {
        $error = 'Enter a title and valid priority.';
    } elseif ($dueDate !== '' && DateTime::createFromFormat('Y-m-d', $dueDate) === false) {
        $error = 'Invalid due date.';
    } else {
        $db = task_management_connection();
        $table = task_management_table();
        $statement = mysqli_prepare($db, "UPDATE `$table` SET title = ?, description = ?, priority = ?, due_date = NULLIF(?, ''), is_complete = ? WHERE id = ?");
        mysqli_stmt_bind_param($statement, 'ssssii', $title, $description, $priority, $dueDate, $complete, $id);
        if (mysqli_stmt_execute($statement)) {
            mysqli_stmt_close($statement);
            task_management_redirect('main', array('message' => 'Task updated.'));
        }
        $error = mysqli_stmt_error($statement);
    }
    $task = array_merge($task, array('title' => $title, 'description' => $description, 'priority' => $priority, 'due_date' => $dueDate, 'is_complete' => $complete));
}
?>
<h1 class="h3 mb-4">Edit task</h1>
<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="post" class="card shadow-sm"><div class="card-body">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(task_management_csrf_token()); ?>">
    <div class="mb-3"><label class="form-label" for="title">Title</label><input class="form-control" id="title" name="title" maxlength="255" required value="<?php echo htmlspecialchars($task['title']); ?>"></div>
    <div class="mb-3"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($task['description']); ?></textarea></div>
    <div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="priority">Priority</label><select class="form-select" id="priority" name="priority"><?php foreach (array('Low', 'Normal', 'High') as $option): ?><option<?php echo $task['priority'] === $option ? ' selected' : ''; ?>><?php echo $option; ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6 mb-3"><label class="form-label" for="due_date">Due date</label><input class="form-control" type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars((string) $task['due_date']); ?>"></div></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="is_complete" name="is_complete" value="1"<?php echo $task['is_complete'] ? ' checked' : ''; ?>><label class="form-check-label" for="is_complete">Complete</label></div>
    <button class="btn btn-primary" type="submit">Save changes</button> <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars(module_url('task_management')); ?>">Cancel</a>
</div></form>
<form method="post" action="<?php echo htmlspecialchars(module_url('task_management', 'delete', array('id' => $id))); ?>" class="mt-3" onsubmit="return confirm('Delete this task?');"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(task_management_csrf_token()); ?>"><button class="btn btn-outline-danger" type="submit">Delete task</button></form>

