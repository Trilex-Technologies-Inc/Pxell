<?php

require_once __DIR__ . '/include.php';
$db = task_management_connection();
$table = task_management_table();
$result = mysqli_query($db, "SELECT id, title, description, priority, due_date, is_complete, created_at FROM `$table` ORDER BY is_complete ASC, due_date IS NULL ASC, due_date ASC, id DESC");
if (!$result) {
    throw new RuntimeException(mysqli_error($db));
}
$tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
?>
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Task Management</h1><p class="text-muted mb-0">Plan and track work from one module.</p></div>
    <a class="btn btn-primary" href="<?php echo htmlspecialchars(module_url('task_management', 'new')); ?>"><i class="fa fa-plus me-1"></i> New task</a>
</div>
<?php if (isset($_GET['message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars((string) $_GET['message']); ?></div><?php endif; ?>
<div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Task</th><th>Priority</th><th>Due date</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php if (empty($tasks)): ?><tr><td colspan="5" class="text-center text-muted py-5">No tasks yet. Create the first one.</td></tr><?php endif; ?>
    <?php foreach ($tasks as $task): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($task['title']); ?></strong><?php if ($task['description'] !== ''): ?><div class="small text-muted"><?php echo nl2br(htmlspecialchars($task['description'])); ?></div><?php endif; ?></td>
            <td><span class="badge <?php echo $task['priority'] === 'High' ? 'bg-danger' : ($task['priority'] === 'Low' ? 'bg-secondary' : 'bg-primary'); ?>"><?php echo htmlspecialchars($task['priority']); ?></span></td>
            <td><?php echo $task['due_date'] ? htmlspecialchars($task['due_date']) : '—'; ?></td>
            <td><?php echo $task['is_complete'] ? '<span class="text-success">Complete</span>' : '<span class="text-warning">Open</span>'; ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars(module_url('task_management', 'edit', array('id' => $task['id']))); ?>">Edit</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div></div>

