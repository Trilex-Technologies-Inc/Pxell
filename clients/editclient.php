<?php // $Revision: 1.9 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: editclient.php,v 1.9 2005/01/20 16:41:58 madbear Exp $
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

// these user levels can't perform this action
if ( ($_SESSION['profilSession'] == 4) || ($_SESSION['profilSession'] == 3) || 
     ($_SESSION['profilSession'] == 2) ) {
    header("Location: ../general/home.php?msg=permissiondenied");
    exit;
}

// case update client organization
if ($id != '') {
    // test exists selected client organization, redirect to list if not
    $tmpquery = "WHERE org.id = '$id'";
    $clientDetail = new request();
    $clientDetail->openOrganizations($tmpquery);
    $comptClientDetail = count($clientDetail->org_id);

    if ($comptClientDetail == '0') {
        header('Location: ../clients/listclients.php?msg=blankClient');
        exit;
    } 
} 
// case update client organization
if ($id != '') {
    if ($action == 'update') {
        if ($logoDel == 'on') {
            $tmpquery = 'UPDATE ' . $tableCollab['organizations'] . " SET extension_logo='' WHERE id='$id'";
            connectSql($tmpquery);
            @unlink("../logos_clients/" . $id . ".$extensionOld");
        } 

        $extension = strtolower(substr(strrchr($_FILES['upload']['name'], '.'), 1));

        if (@move_uploaded_file($_FILES['upload']['tmp_name'], '../logos_clients/' . $id . ".$extension")) {
            $tmpquery = 'UPDATE ' . $tableCollab['organizations'] . " SET extension_logo='$extension' WHERE id='$id'";
            connectSql($tmpquery);
        } 
        // replace quotes by html code in name and address
        $cn = convertData($cn);
        $add = convertData($add);
        $c = convertData($c);

        $tmpquery = 'UPDATE ' . $tableCollab['organizations'] . " SET name='$cn',address1='$add',phone='$wp',url='$url',email='$email',comments='$c',owner='$cown' WHERE id = '$id'";
        connectSql($tmpquery);
        header("Location: ../clients/viewclient.php?id=$id&msg=update");
        exit;
    } 
    // set value in form
    $cn = $clientDetail->org_name[0];
    $add = $clientDetail->org_address1[0];
    $wp = $clientDetail->org_phone[0];
    $url = $clientDetail->org_url[0];
    $email = $clientDetail->org_email[0];
    $c = $clientDetail->org_comments[0];
} 
// case add client organization
if ($id == '') {
    if ($action == 'add') {
        // test if name blank
        if ($cn == '') {
            $error = $strings['blank_organization_field'];
        } else {
            // replace quotes by html code in name and address
            $cn = convertData($cn);
            $add = convertData($add);
            $c = convertData($c); 
            // test if name already exists
            $tmpquery = "WHERE org.name = '$cn'";
            $existsClient = new request();
            $existsClient->openOrganizations($tmpquery);
            $comptExistsClient = count($existsClient->org_id);

            if ($comptExistsClient != '0') {
                $error = $strings['organization_already_exists'];
            } else {
                // insert into organizations and redirect to new client organization detail (last id)
                $tmpquery1 = "INSERT INTO " . $tableCollab["organizations"] . "(name,address1,phone,url,email,comments,created,owner) VALUES('$cn','$add','$wp','$url','$email','$c','$dateheure','$cown')";
                connectSql($tmpquery1);

                $tmpquery = $tableCollab['organizations'];
                last_id($tmpquery);
                $num = $lastId[0];
                unset($lastId);
                $extension = strtolower(substr(strrchr($upload_name, '.'), 1));

                if (@move_uploaded_file($upload, '../logos_clients/' . $num . ".$extension")) {
                    $tmpquery = 'UPDATE ' . $tableCollab['organizations'] . " SET extension_logo='$extension' WHERE id='$num'";
                    connectSql($tmpquery);
                } 

                header("Location: ../clients/viewclient.php?id=$num&msg=add");
                exit;
            } 
        } 
    } 
} 


//--- header ---
$breadcrumbs[]=buildLink('../clients/listclients.php?', $strings['clients'], LINK_INSIDE);
$pageSection='clients';

if ($id == '') {
    $breadcrumbs[]=$strings['add_organization'];
} 

if ($id != '') {
    $breadcrumbs[]=buildLink('../clients/viewclient.php?id=' . $clientDetail->org_id[0], $clientDetail->org_name[0], LINK_INSIDE);
    $breadcrumbs[]=$strings['edit_organization'];
} 



$bodyCommand = 'onLoad="document.ecDForm.cn.focus();"';
require_once('../themes/' . THEME . '/header.php');

//---- content ---
$block1 = new block();

if ($id == '') {
    echo "<a name=\"" . $block1->form . "Anchor\"></a>\n
<form accept-charset=\"UNKNOWN\" method=\"POST\" action=\"../clients/editclient.php?action=add\" name=\"ecDForm\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"100000000\">\n";
} 

if ($id != '') {
    echo "<a name=\"" . $block1->form . "Anchor\"></a>\n
<form accept-charset=\"UNKNOWN\" method=\"POST\" action=\"../clients/editclient.php?id=$id&amp;action=update\" name=\"ecDForm\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"100000000\">\n";
} 

if ($error != '') {
    $block1->headingError($strings['errors']);
    $block1->contentError($error);
} 

if ($id == '') {
    $block1->headingForm($strings['add_organization']);
} 
else {
    $block1->headingForm($strings['edit_organization'] . ' : ' . $clientDetail->org_name[0]);
} 

$block1->openContent();
$block1->contentTitle($strings['details']);

if ($clientsFilter == 'true') {
    $selectOwner = '<select name="cown" class="form-select" >';

    $tmpquery = "WHERE (mem.profil='5' OR mem.profil='1' OR mem.profil='0') AND mem.login != 'demo' ORDER BY mem.name";
    $clientOwner = new request();
    $clientOwner->openMembers($tmpquery);
    $comptClientOwner = count($clientOwner->mem_id);

    for ($i = 0; $i < $comptClientOwner; $i++) {
        if ($clientDetail->org_owner[0] == $clientOwner->mem_id[$i] || $_SESSION['idSession'] == $clientOwner->mem_id[$i]) {
            $selectOwner .= '<option value="' . $clientOwner->mem_id[$i] . '" selected>' . $clientOwner->mem_login[$i] . ' / ' . $clientOwner->mem_name[$i] . '</option>';
        } else {
            $selectOwner .= '<option value="' . $clientOwner->mem_id[$i] . '">' . $clientOwner->mem_login[$i] . ' / ' . $clientOwner->mem_name[$i] . '</option>';
        } 
    } 

    $selectOwner .= '</select>';
    $block1->formRow($strings['owner'], $selectOwner);
} 

$block1->formRow(
    '* ' . $strings['name'],
    '<input type="text" name="cn" maxlength="100" value="' . $cn . '" class="form-control">'
);
// ---------- ADDRESS ----------
$block1->formRow(
    $strings['address'],
    '<textarea name="add" rows="3" class="form-control">' . $add . '</textarea>'
);


// ---------- PHONE ----------
$block1->formRow(
    $strings['phone'],
    '<input type="text" name="wp" maxlength="32" value="' . $wp . '" class="form-control">'
);


// ---------- URL ----------
$block1->formRow(
    $strings['url'],
    '<input type="text" name="url" maxlength="2000" value="' . $url . '" class="form-control">'
);


// ---------- EMAIL ----------
$block1->formRow(
    $strings['email'],
    '<input type="text" name="email" maxlength="2000" value="' . $email . '" class="form-control">'
);


// ---------- COMMENTS ----------
$block1->formRow(
    $strings['comments'],
    '<textarea name="c" rows="3" class="form-control">' . $c . '</textarea>'
);


// ---------- LOGO UPLOAD ----------
$block1->formRow(
    $strings['logo'],
    '<input type="file" name="upload" class="form-control">'
);


// ---------- LOGO PREVIEW + DELETE ----------
if ($id != '') {
    $logoPath = '../logos_clients/' . $id . '.' . $clientDetail->org_extension_logo[0];

    if (file_exists($logoPath)) {
        $block1->formRow(
            '',
            '<img src="' . $logoPath . '" class="img-fluid mb-2">

             <input type="hidden" name="extensionOld" value="' . $clientDetail->org_extension_logo[0] . '">

             <div class="form-check mt-2">
                 <input class="form-check-input" type="checkbox" name="logoDel" value="on">
                 <label class="form-check-label">' . $strings['delete'] . '</label>
             </div>'
        );
    }
}



$block1->formRow('', '<input  class="btn btn-primary" type="SUBMIT" value="' . $strings['save'] . '">');

$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once('../themes/' . THEME . '/footer.php');

?>
