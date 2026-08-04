<?php

$checkSession = true;
require_once('../includes/library.php');
require_once('../includes/modules.php');

if ($_SESSION['profilSession'] != 0) {
    header('Location: ../general/permissiondenied.php');
    exit;
}

$notice = '';
$error = '';
if (empty($_SESSION['moduleCsrfToken'])) {
    $_SESSION['moduleCsrfToken'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['module']) ? (string) $_POST['module'] : '';
    $operation = isset($_POST['operation']) ? (string) $_POST['operation'] : '';

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['moduleCsrfToken'], (string) $_POST['csrf_token'])) {
        $error = 'The request could not be verified.';
    } elseif (module_manifest($name) === null) {
        $error = 'Invalid module.';
    } else {
        try {
            $all = module_discover();
            $current = $all[$name];
            if ($operation === 'install') {
                module_run_hook($name, 'install');
                if (!module_set_state($name, true, true)) {
                    throw new RuntimeException('The module state file is not writable.');
                }
                $notice = 'Module installed and enabled.';
            } elseif ($operation === 'enable' && $current['installed']) {
                if (!module_set_state($name, true, true)) {
                    throw new RuntimeException('The module state file is not writable.');
                }
                $notice = 'Module enabled.';
            } elseif ($operation === 'disable' && $current['installed']) {
                if (!module_set_state($name, true, false)) {
                    throw new RuntimeException('The module state file is not writable.');
                }
                $notice = 'Module disabled.';
            } elseif ($operation === 'uninstall' && $current['installed']) {
                module_run_hook($name, 'uninstall');
                if (!module_set_state($name, false, false)) {
                    throw new RuntimeException('The module state file is not writable.');
                }
                $notice = 'Module uninstalled.';
            } else {
                $error = 'That operation is not available.';
            }
        } catch (Throwable $exception) {
            $error = 'Module operation failed: ' . $exception->getMessage();
        }
    }
}

$modules = module_discover();
$pageSection = 'admin';
$breadcrumbs[] = buildLink('../administration/admin.php', isset($strings['administration']) ? $strings['administration'] : 'Administration', LINK_INSIDE);
$breadcrumbs[] = 'Modules';
require_once('../themes/' . THEME . '/header.php');
?>
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Modules</h1><p class="text-muted mb-0">Install, enable, disable, and uninstall TaskVibe extensions.</p></div>
    <a class="btn btn-outline-secondary btn-sm flex-shrink-0" href="../administration/module_development.php">
        Module Development Guide
    </a>
</div>
<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<div class="row g-3">
<?php if (empty($modules)): ?>
    <div class="col-12"><div class="alert alert-info">No modules were discovered. Add one under <code>modules/&lt;name&gt;/module.json</code>.</div></div>
<?php endif; ?>
<?php foreach ($modules as $name => $module): ?>
    <div class="col-12 col-lg-6"><div class="card h-100 shadow-sm"><div class="card-body">
        <div class="d-flex justify-content-between gap-3"><div>
            <h2 class="h5 mb-1"><?php echo htmlspecialchars($module['name']); ?></h2>
            <div class="small text-muted mb-2">v<?php echo htmlspecialchars($module['version']); ?> · <?php echo htmlspecialchars($name); ?></div>
        </div><span class="badge <?php echo $module['enabled'] ? 'bg-success' : ($module['installed'] ? 'bg-secondary' : 'bg-light text-dark'); ?> align-self-start"><?php echo $module['enabled'] ? 'Enabled' : ($module['installed'] ? 'Disabled' : 'Not installed'); ?></span></div>
        <p><?php echo htmlspecialchars($module['description']); ?></p>
        <form method="post" class="d-flex gap-2 flex-wrap">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['moduleCsrfToken']); ?>">
            <input type="hidden" name="module" value="<?php echo htmlspecialchars($name); ?>">
            <?php if (!$module['installed']): ?><button class="btn btn-primary btn-sm" name="operation" value="install">Install</button>
            <?php elseif ($module['enabled']): ?><a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars(module_url($name, $module['default_action'])); ?>">Open</a><button class="btn btn-outline-secondary btn-sm" name="operation" value="disable">Disable</button>
            <?php else: ?><button class="btn btn-primary btn-sm" name="operation" value="enable">Enable</button><?php endif; ?>
            <?php if ($module['installed']): ?><button class="btn btn-outline-danger btn-sm" name="operation" value="uninstall" onclick="return confirm('Uninstall this module? Its uninstall hook may remove module data.');">Uninstall</button><?php endif; ?>
        </form>
    </div></div></div>
<?php endforeach; ?>
</div>
<?php require_once('../themes/' . THEME . '/footer.php'); ?>
