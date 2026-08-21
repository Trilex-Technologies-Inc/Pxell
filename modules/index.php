<?php

$checkSession = true;
require_once('../includes/library.php');
require_once('../includes/modules.php');

$route = isset($_GET['page']) ? (string) $_GET['page'] : '';
$parts = explode(':', $route, 2);
$moduleName = isset($parts[0]) ? $parts[0] : '';
$action = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : 'main';
$manifest = module_manifest($moduleName);

if ($manifest === null || !module_valid_name($action)) {
    http_response_code(404);
    $moduleError = 'The requested module page was not found.';
} else {
    $modules = module_discover();
    $current = isset($modules[$moduleName]) ? $modules[$moduleName] : array();
    if (empty($current['installed']) || empty($current['enabled'])) {
        http_response_code(403);
        $moduleError = 'This module is not installed and enabled.';
    } else {
        $actionFile = $base_dir . 'modules/' . $moduleName . '/' . $action . '.php';
        $moduleRoot = realpath($base_dir . 'modules/' . $moduleName);
        $resolvedAction = realpath($actionFile);
        if ($resolvedAction === false || $moduleRoot === false || strpos($resolvedAction, $moduleRoot . DIRECTORY_SEPARATOR) !== 0) {
            http_response_code(404);
            $moduleError = 'The requested module action was not found.';
        }
    }
}

$pageSection = 'module_' . $moduleName;
$pageTitle = $manifest !== null ? $manifest['name'] : 'Module';
$breadcrumbs[] = buildLink('../general/home.php', isset($strings['home']) ? $strings['home'] : 'Home', LINK_INSIDE);
$breadcrumbs[] = htmlspecialchars($pageTitle);
require_once('../themes/' . THEME . '/header.php');

if (isset($moduleError)) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($moduleError) . '</div>';
} else {
    // Module actions execute inside the normal TaskVibe application context.
    require $resolvedAction;
}

require_once('../themes/' . THEME . '/footer.php');

