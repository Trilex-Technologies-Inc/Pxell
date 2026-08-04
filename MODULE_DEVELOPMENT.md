# Creating a TaskVibe Module

Modules are discovered from `modules/<name>/module.json`, managed by administrators at `administration/modules.php`, and opened with this route:

```text
modules/index.php?page=module:action
```

## Minimal module

```text
modules/example/
├── module.json
├── main.php
├── install.php       # optional
└── uninstall.php     # optional
```

`module.json`:

```json
{
  "name": "Example",
  "version": "1.0.0",
  "description": "An example TaskVibe module",
  "default_action": "main",
  "navigation": {
    "label": "Example",
    "icon": "fa-puzzle-piece"
  }
}
```

`main.php` is included after TaskVibe authentication and the normal page header. It can use existing globals such as `$MY_DBH`, `$tableCollab`, `$strings`, `$base_uri`, and the current session. Escape all displayed request/database values with `htmlspecialchars()`.

Install and uninstall hooks run only for administrators. Throw an exception if a hook fails; the manager will show the error and will not update the module state.

Module directory names and action names must start with a lowercase letter and contain only lowercase letters, numbers, underscores, or hyphens. Requests cannot escape the selected module directory.

## Lifecycle and management

1. Copy the module directory into `modules/`.
2. Sign in as an administrator.
3. Open **Administration > Module management**.
4. Select **Install**. The manager runs `install.php`, then enables the module.
5. Enabled modules with a `navigation` manifest entry appear automatically in the sidebar.
6. **Disable** hides the navigation entry and blocks all module routes without deleting data.
7. **Uninstall** runs `uninstall.php`. An uninstaller may permanently delete module-owned data.

## Adding actions

Every action is a PHP file directly inside the module directory. For example:

```text
modules/example/edit.php
modules/index.php?page=example:edit&id=12
```

The router only accepts safe lowercase action names and confirms the resolved file remains inside the selected module. Actions receive the authenticated TaskVibe environment and render between the standard header and footer.

For reusable queries and helpers, create `include.php` and load it from actions with:

```php
require_once __DIR__ . '/include.php';
```

Use prepared `mysqli` statements for request values, a dedicated CSRF token for every POST operation, POST rather than GET for deletion, and `htmlspecialchars()` for displayed data.

## Working example

`modules/task_management/` is a complete example with:

- manifest discovery and automatic sidebar navigation;
- database installation and removal hooks;
- list, create, edit, complete, and delete actions;
- prepared SQL statements, validation, and CSRF protection.

Install it from **Administration > Module management**. It creates a separate `<table-prefix>module_tasks` table and does not modify TaskVibe's legacy task tables.

## Validation checklist

- Run `find modules/<name> -name '*.php' -print0 | xargs -0 -n1 php -l`.
- Confirm the module appears in Module management.
- Test install, disable, enable, and uninstall.
- Confirm disabled and uninstalled routes return an access error.
- Test invalid values, quotes in text, and unauthorized POST requests.
- Confirm uninstall removes only tables owned by that module.
