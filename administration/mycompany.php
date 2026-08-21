<?php // $Revision: 1.9 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: mycompany.php,v 1.9 2004/12/22 22:15:42 madbear Exp $
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

$action = (string) ($_GET['action'] ?? '');
$error = '';

if ($action == 'update' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $extensionOld = (string) ($_POST['extensionOld'] ?? '');
    $cn = (string) ($_POST['cn'] ?? '');
    $add = (string) ($_POST['add'] ?? '');
    $wp = (string) ($_POST['wp'] ?? '');
    $url = (string) ($_POST['url'] ?? '');
    $email = (string) ($_POST['email'] ?? '');
    $c = (string) ($_POST['c'] ?? '');
    $logoDel = (string) ($_POST['logoDel'] ?? '');
    $logoDirectory = $base_dir . 'logos_clients/';

    if ($logoDel == 'on') {
        $tmpquery = 'UPDATE ' . $tableCollab['organizations'] . " SET extension_logo='' WHERE id='1'";
        connectSql($tmpquery);
        $oldLogo = $logoDirectory . '1.' . basename($extensionOld);
        if (is_file($oldLogo)) {
            unlink($oldLogo);
        }
    } 

    $upload = $_FILES['upload'] ?? null;
    if (is_array($upload) && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadError = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
        $extension = strtolower((string) pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION));
        $allowedExtensions = array('png', 'jpg', 'jpeg', 'gif', 'webp');

        if ($uploadError !== UPLOAD_ERR_OK) {
            $error = 'Logo upload failed with PHP upload error code ' . $uploadError . '.';
        } elseif (!in_array($extension, $allowedExtensions, true)) {
            $error = 'Logo upload failed: only PNG, JPG, GIF, and WebP images are allowed.';
        } elseif (!is_dir($logoDirectory) || !is_writable($logoDirectory)) {
            $error = 'Logo upload failed: the logos_clients directory is not writable by the web server.';
        } elseif (!move_uploaded_file((string) $upload['tmp_name'], $logoDirectory . '1.' . $extension)) {
            $error = 'Logo upload failed while moving the uploaded file into logos_clients.';
        } else {
            $tmpquery = 'UPDATE ' . $tableCollab['organizations'] . " SET extension_logo='$extension' WHERE id='1'";
            connectSql($tmpquery);
        }
    }

    $cn = convertData($cn);
    $add = convertData($add);
    $c = convertData($c);
    $tmpquery = 'UPDATE ' . $tableCollab['organizations'] . " SET name='$cn',address1='$add',phone='$wp',url='$url',email='$email',comments='$c' WHERE id = '1'";
    connectSql($tmpquery);
    if ($error === '') {
        header('Location: ../administration/mycompany.php');
        exit;
    }
} 

$tmpquery = "WHERE org.id='1'";

$clientDetail = new request();
$clientDetail->openOrganizations($tmpquery);

$cn = $clientDetail->org_name[0];
$add = $clientDetail->org_address1[0];
$wp = $clientDetail->org_phone[0];
$url = $clientDetail->org_url[0];
$email = $clientDetail->org_email[0];
$c = $clientDetail->org_comments[0];



//--- header ---------
$breadcrumbs[]=buildLink('../administration/admin.php?', $strings['administration'], LINK_INSIDE);
$breadcrumbs[]=$strings['company_details'];


$bodyCommand = 'onLoad="document.adminDForm.cn.focus();"';

$pageSection = 'admin';
require_once('../themes/' . THEME . '/header.php');

//---- content -------
$blockPage= new block();

$block1 = new block();
echo '<a name="' . $block1->form . 'Anchor"></a>';
echo '<form accept-charset="UNKNOWN" method="POST" action="../administration/mycompany.php?action=update" name="adminDForm" enctype="multipart/form-data">';
echo '<input type="hidden" name="MAX_FILE_SIZE" value="100000000">';

if ($error != '') {
    $block1->headingError($strings['errors']);
    $block1->contentError($error);
} 

$block1->headingForm($strings['company_details']);
$block1->openContent();
$block1->contentTitle($strings['company_info']);

// ---------- NAME ----------
$block1->formRow(
    $strings['name'],
    '<input type="text" name="cn" value="' . htmlspecialchars($cn) . '" class="form-control" maxlength="100">'
);

// ---------- ADDRESS ----------
$block1->formRow(
    $strings['address'],
    '<textarea name="add" class="form-control" rows="3">' . htmlspecialchars($add) . '</textarea>'
);

// ---------- PHONE ----------
$block1->formRow(
    $strings['phone'],
    '<input type="text" name="wp" value="' . htmlspecialchars($wp) . '" class="form-control" maxlength="32">'
);

// ---------- URL ----------
$block1->formRow(
    $strings['url'],
    '<input type="url" name="url" value="' . htmlspecialchars($url) . '" class="form-control" maxlength="2000">'
);

// ---------- EMAIL ----------
$block1->formRow(
    $strings['email'],
    '<input type="email" name="email" value="' . htmlspecialchars($email) . '" class="form-control" maxlength="2000">'
);

// ---------- COMMENTS ----------
$block1->formRow(
    $strings['comments'],
    '<textarea name="c" class="form-control" rows="3">' . htmlspecialchars($c) . '</textarea>'
);

// ---------- LOGO UPLOAD ----------
$block1->formRow(
    $strings['logo'] . $blockPage->printHelp('mycompany_logo'),
    '<input type="file" name="upload" class="form-control">'
);

// ---------- EXISTING LOGO DISPLAY AND DELETE OPTION ----------
$companyLogoPath = $base_dir . 'logos_clients/1.' . $clientDetail->org_extension_logo[0];
if (is_file($companyLogoPath)) {
    $block1->formRow(
        '',
        '<div class="mb-2">
            <img src="../logos_clients/1.' . $clientDetail->org_extension_logo[0] . '" alt="' . htmlspecialchars($clientDetail->org_name[0]) . '" class="img-fluid mb-1">
            <input type="hidden" name="extensionOld" value="' . htmlspecialchars($clientDetail->org_extension_logo[0]) . '">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="logoDel" value="on" id="logoDel">
                <label class="form-check-label" for="logoDel">' . $strings['delete'] . '</label>
            </div>
        </div>'
    );
}

// ---------- SUBMIT BUTTON ----------
$block1->formRow(
    '',
    '<input type="submit" value="' . $strings['save'] . '" class="btn btn-primary">'
);

$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once('../themes/' . THEME . '/footer.php');

?>
