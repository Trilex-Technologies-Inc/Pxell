<?php

/**
 * Lightweight, filesystem-backed module registry.
 *
 * Each module lives in modules/<name>/ and must provide module.json.
 */

function module_valid_name($name)
{
    return is_string($name) && preg_match('/^[a-z][a-z0-9_-]*$/', $name) === 1;
}

function module_manifest($name)
{
    global $base_dir;

    if (!module_valid_name($name)) {
        return null;
    }

    $file = $base_dir . 'modules/' . $name . '/module.json';
    if (!is_file($file) || !is_readable($file)) {
        return null;
    }

    $manifest = json_decode((string) file_get_contents($file), true);
    if (!is_array($manifest) || empty($manifest['name'])) {
        return null;
    }

    $manifest['directory'] = $name;
    $manifest['version'] = isset($manifest['version']) ? (string) $manifest['version'] : '1.0.0';
    $manifest['description'] = isset($manifest['description']) ? (string) $manifest['description'] : '';
    $manifest['default_action'] = isset($manifest['default_action']) && module_valid_name($manifest['default_action'])
        ? $manifest['default_action'] : 'main';

    return $manifest;
}

function module_state_all()
{
    global $base_dir;
    $file = $base_dir . 'modules/.state.json';
    if (!is_file($file) || !is_readable($file)) {
        return array();
    }

    $state = json_decode((string) file_get_contents($file), true);
    return is_array($state) ? $state : array();
}

function module_state_save($state)
{
    global $base_dir;
    $file = $base_dir . 'modules/.state.json';
    $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return $json !== false && file_put_contents($file, $json . "\n", LOCK_EX) !== false;
}

function module_discover()
{
    global $base_dir;
    $modules = array();
    $state = module_state_all();

    foreach (glob($base_dir . 'modules/*/module.json') ?: array() as $file) {
        $name = basename(dirname($file));
        $manifest = module_manifest($name);
        if ($manifest === null) {
            continue;
        }

        $saved = isset($state[$name]) && is_array($state[$name]) ? $state[$name] : array();
        $manifest['installed'] = !empty($saved['installed']);
        $manifest['enabled'] = $manifest['installed'] && !empty($saved['enabled']);
        $modules[$name] = $manifest;
    }

    ksort($modules);
    return $modules;
}

function module_set_state($name, $installed, $enabled)
{
    if (module_manifest($name) === null) {
        return false;
    }

    $state = module_state_all();
    $state[$name] = array(
        'installed' => (bool) $installed,
        'enabled' => (bool) $installed && (bool) $enabled
    );
    return module_state_save($state);
}

function module_run_hook($name, $hook)
{
    global $base_dir, $MY_DBH, $tableCollab;

    if (module_manifest($name) === null || !in_array($hook, array('install', 'uninstall'), true)) {
        throw new RuntimeException('Invalid module hook.');
    }

    $file = $base_dir . 'modules/' . $name . '/' . $hook . '.php';
    if (is_file($file)) {
        require $file;
    }
}

function module_url($name, $action = 'main', $parameters = array())
{
    global $base_uri;
    $parameters = array_merge(array('page' => $name . ':' . $action), $parameters);
    return $base_uri . 'modules/index.php?' . http_build_query($parameters);
}

