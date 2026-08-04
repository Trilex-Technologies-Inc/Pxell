<?php

$checkSession = true;
require_once('../includes/library.php');

if ($_SESSION['profilSession'] != 0) {
    header('Location: ../general/permissiondenied.php');
    exit;
}

$guidePath = $base_dir . 'MODULE_DEVELOPMENT.md';
$guideContent = '';
$error = '';
if (is_file($guidePath) && is_readable($guidePath)) {
    $guideContent = file_get_contents($guidePath);
    if ($guideContent === false) {
        $error = 'Could not read the module development guide.';
    }
} else {
    $error = 'Module development guide was not found.';
}

$pageSection = 'admin';
$breadcrumbs[] = buildLink('../administration/admin.php', isset($strings['administration']) ? $strings['administration'] : 'Administration', LINK_INSIDE);
$breadcrumbs[] = buildLink('../administration/modules.php', 'Modules', LINK_INSIDE);
$breadcrumbs[] = 'Module Development Guide';
require_once('../themes/' . THEME . '/header.php');
?>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center gap-3">
        <h1 class="h4 mb-0">Module Development Guide</h1>
        <a class="btn btn-outline-secondary btn-sm" href="../administration/modules.php">Back to Modules</a>
    </div>
    <div class="card-body">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <pre class="bg-light border rounded p-3 mb-0" style="white-space:pre-wrap;overflow:auto;"><?php echo htmlspecialchars($guideContent); ?></pre>
        <?php endif; ?>
    </div>
</div>
<?php require_once('../themes/' . THEME . '/footer.php'); ?>
