<?php

require_once __DIR__ . '/include.php';
$error = '';
$title = isset($_POST['title']) ? trim((string) $_POST['title']) : '';
$description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';
$priority = isset($_POST['priority']) ? (string) $_POST['priority'] : 'Normal';
$dueDate = isset($_POST['due_date']) ? trim((string) $_POST['due_date']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!task_management_verify_csrf()) {
        $error = 'The request could not be verified.';
    } elseif ($title === '') {
        $error = 'Title is required.';
    } elseif (!in_array($priority, array('Low', 'Normal', 'High'), true)) {
        $error = 'Invalid priority.';
    } elseif ($dueDate !== '' && DateTime::createFromFormat('Y-m-d', $dueDate) === false) {
        $error = 'Invalid due date.';
    } else {
        $db = task_management_connection();
        $table = task_management_table();
        $createdBy = isset($_SESSION['idSession']) ? (int) $_SESSION['idSession'] : null;
        $statement = mysqli_prepare($db, "INSERT INTO `$table` (title, description, priority, due_date, created_by) VALUES (?, ?, ?, NULLIF(?, ''), ?)");
        mysqli_stmt_bind_param($statement, 'ssssi', $title, $description, $priority, $dueDate, $createdBy);
        if (!mysqli_stmt_execute($statement)) {
            $error = mysqli_stmt_error($statement);
        } else {
            mysqli_stmt_close($statement);
            task_management_redirect('main', array('message' => 'Task created.'));
        }
    }
}
?>
<h1 class="h3 mb-4">New task</h1>
<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="post" class="card shadow-sm"><div class="card-body">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(task_management_csrf_token()); ?>">
    <div class="mb-3"><label class="form-label" for="title">Title</label><input class="form-control" id="title" name="title" maxlength="255" required value="<?php echo htmlspecialchars($title); ?>"></div>
    <div class="mb-3"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea></div>
    <div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="priority">Priority</label><select class="form-select" id="priority" name="priority"><?php foreach (array('Low', 'Normal', 'High') as $option): ?><option<?php echo $priority === $option ? ' selected' : ''; ?>><?php echo $option; ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6 mb-3"><label class="form-label" for="due_date">Due date</label><input class="form-control" type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($dueDate); ?>"></div></div>
    <button class="btn btn-primary" type="submit">Create task</button> <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars(module_url('task_management')); ?>">Cancel</a>
</div></form>

