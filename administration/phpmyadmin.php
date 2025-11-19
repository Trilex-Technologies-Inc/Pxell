<?php // $Revision: 1.6 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: phpmyadmin.php,v 1.6 2004/12/15 19:43:11 madbear Exp $
 *
 * Copyright (c) 2003 by the NetOffice developers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

$checkSession = true;
require_once('../includes/library.php');

if ($_SESSION['profilSession'] != 0) {
    header('Location: ../general/permissiondenied.php');
    exit;
}


$breadcrumbs[] = buildLink('../administration/admin.php?', $strings['administration'], LINK_INSIDE);
$breadcrumbs[] = $strings['database'] . ' ' . MYDATABASE;

$pageSection = 'admin';
require_once('../themes/' . THEME . '/header.php');


$block1 = new block();
$block1->headingForm($strings['database'] . ' ' . MYDATABASE);
$block1->openContent();
$block1->contentTitle('Backup database');


echo '<script src="../includes/phpmyadmin/functions.js" type="text/javascript"></script>';

// ---------- DATABASE DUMP FORM ----------
echo '<form method="post" action="../includes/phpmyadmin/tbl_dump.php" name="db_dump" class="mb-4">';

// Table select
echo '<div class="mb-3">
        <label class="form-label fw-bold">Select tables:</label>
        <select name="table_select[]" size="5" multiple class="form-select" style="max-width: 300px;">';
sort($tableCollab);
foreach ($tableCollab as $val) {
    echo '<option value="' . htmlspecialchars($val) . '" selected>' . htmlspecialchars($val) . '</option>';
}
echo '  </select>
      </div>';

// Radio buttons for export type
echo '<div class="mb-3">
        <label class="form-label fw-bold">Export options:</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="what" value="structure" id="structure">
            <label class="form-check-label" for="structure">Structure only</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="what" value="data" id="data" checked>
            <label class="form-check-label" for="data">Structure and data</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="what" value="dataonly" id="dataonly">
            <label class="form-check-label" for="dataonly">Data only</label>
        </div>
      </div>';

// Checkboxes
$checkboxes = [
    'drop' => ['label' => 'Add drop table', 'checked' => 'checked'],
    'showcolumns' => ['label' => 'Complete inserts'],
    'extended_ins' => ['label' => 'Extended inserts'],
    'use_backquotes' => ['label' => 'Use backquotes with tables and fields names'],
];
foreach ($checkboxes as $name => $opt) {
    $checked = isset($opt['checked']) ? 'checked' : '';
    echo '<div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="' . $name . '" value="1" ' . $checked . '>
            <label class="form-check-label">' . $opt['label'] . '</label>
          </div>';
}

// Save as file options
echo '<div class="mb-3">
        <label class="form-label fw-bold">Save as file:</label>
        <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" name="asfile" value="sendit" checked onclick="return checkTransmitDump(this.form, \'transmit\')">
            <label class="form-check-label">Save as file</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="zip" value="zip" onclick="return checkTransmitDump(this.form, \'zip\')">
            <label class="form-check-label">zipped</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="gzip" value="gzip" onclick="return checkTransmitDump(this.form, \'gzip\')">
            <label class="form-check-label">gzipped</label>
        </div>
      </div>';

// Submit
echo '<div class="mb-3">
        <input type="submit" value="Go" class="btn btn-primary">
      </div>';

// Hidden fields
echo '<input type="hidden" name="server" value="1">
      <input type="hidden" name="lang" value="en">
      <input type="hidden" name="db" value="' . MYDATABASE . '">';

echo '</form>';

// ---------- DATABASE RESTORE FORM ----------
$block1->contentTitle('Restore database from sql file');

echo '<form method="post" action="../includes/phpmyadmin/read_dump.php" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="is_js_confirmed" value="0">
        <input type="hidden" name="lang" value="en">
        <input type="hidden" name="server" value="1">
        <input type="hidden" name="db" value="' . MYDATABASE . '">
        <input type="hidden" name="pos" value="0">
        <input type="hidden" name="goto" value="db_details.php">
        <input type="hidden" name="zero_rows" value="Your SQL-query has been executed successfully">
        <input type="hidden" name="prev_sql_query" value="">
        
        <div class="mb-3">
            <label class="form-label fw-bold">Location of SQL file:</label>
            <input type="file" name="sql_file" class="form-control">
        </div>

        <input type="submit" name="SQL" value="Go" class="btn btn-primary">
      </form>';

$block1->closeContent();
$block1->headingForm_close();

require_once('../themes/' . THEME . '/footer.php');

?>
