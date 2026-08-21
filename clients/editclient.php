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

// This page predates PHP's removal of register_globals. Define every optional
// request value explicitly so an ordinary GET or partially completed form
// does not generate undefined-variable/array-key warnings.
$requestedId = (string) ($_GET['id'] ?? '');
$id = ($requestedId === '' || ctype_digit($requestedId)) ? $requestedId : '';
$requestedAction = (string) ($_GET['action'] ?? '');
$action = in_array($requestedAction, array('add', 'update'), true) ? $requestedAction : '';
$cn = (string) ($_POST['cn'] ?? '');
$add = (string) ($_POST['add'] ?? '');
$wp = (string) ($_POST['wp'] ?? '');
$url = (string) ($_POST['url'] ?? '');
$email = (string) ($_POST['email'] ?? '');
$c = (string) ($_POST['c'] ?? '');
$cown = (string) ($_POST['cown'] ?? ($_SESSION['idSession'] ?? ''));
$logoDel = (string) ($_POST['logoDel'] ?? '');
$extensionOld = (string) ($_POST['extensionOld'] ?? '');
$error = '';
$clientDetail = null;
$upload = $_FILES['upload'] ?? array();

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
    if ($action == 'update' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if ($logoDel == 'on') {
            $tmpquery = 'UPDATE ' . $tableCollab['organizations'] . " SET extension_logo='' WHERE id='$id'";
            connectSql($tmpquery);
            @unlink("../logos_clients/" . $id . ".$extensionOld");
        }

        $uploadName = (string) ($upload['name'] ?? '');
        $uploadTmpName = (string) ($upload['tmp_name'] ?? '');
        $extension = strtolower((string) pathinfo($uploadName, PATHINFO_EXTENSION));

        if ($extension !== '' && is_uploaded_file($uploadTmpName) &&
            move_uploaded_file($uploadTmpName, '../logos_clients/' . $id . ".$extension")) {
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
    if ($action == 'add' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
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
                $uploadName = (string) ($upload['name'] ?? '');
                $uploadTmpName = (string) ($upload['tmp_name'] ?? '');
                $extension = strtolower((string) pathinfo($uploadName, PATHINFO_EXTENSION));

                if ($extension !== '' && is_uploaded_file($uploadTmpName) &&
                    move_uploaded_file($uploadTmpName, '../logos_clients/' . $num . ".$extension")) {
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
?>
    <div class="container mt-4">
        <?php if ($error != ''): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php if ($id == ''): ?>
                        <?php echo $strings['add_organization']; ?>
                    <?php else: ?>
                        <?php echo $strings['edit_organization'] . ' : ' . htmlspecialchars($clientDetail->org_name[0]); ?>
                    <?php endif; ?>
                </h5>
            </div>

            <?php if ($id == ''): ?>
            <form method="POST" action="../clients/editclient.php?action=add" name="ecDForm" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
                <?php else: ?>
                <form method="POST" action="../clients/editclient.php?id=<?php echo htmlspecialchars($id); ?>&amp;action=update" name="ecDForm" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
                    <?php endif; ?>

                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted"><?php echo $strings['details']; ?></h6>

                        <?php if (($clientsFilter ?? 'false') == 'true'): ?>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label"><?php echo $strings['owner']; ?> :</label>
                                <div class="col-sm-9">
                                    <select name="cown" class="form-select">
                                        <?php
                                        $tmpquery = "WHERE (mem.profil='5' OR mem.profil='1' OR mem.profil='0') AND mem.login != 'demo' ORDER BY mem.name";
                                        $clientOwner = new request();
                                        $clientOwner->openMembers($tmpquery);
                                        $comptClientOwner = count($clientOwner->mem_id);

                                        for ($i = 0; $i < $comptClientOwner; $i++) {
                                            $currentOwner = $clientDetail !== null
                                                ? $clientDetail->org_owner[0]
                                                : $cown;
                                            $selected = ((string) $currentOwner === (string) $clientOwner->mem_id[$i]) ? 'selected' : '';
                                            echo '<option value="' . $clientOwner->mem_id[$i] . '" ' . $selected . '>' .
                                                htmlspecialchars($clientOwner->mem_login[$i] . ' / ' . $clientOwner->mem_name[$i]) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">
                                <span class="text-danger">*</span> <?php echo $strings['name']; ?> :
                            </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="cn" value="<?php echo htmlspecialchars($cn); ?>" maxlength="100" required autofocus>
                                <div class="invalid-feedback">
                                    <?php echo $strings['blank_organization_field']; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings['address']; ?> :</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="add" rows="3"><?php echo htmlspecialchars($add); ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings['phone']; ?> :</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="wp" value="<?php echo htmlspecialchars($wp); ?>" maxlength="32">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings['url']; ?> :</label>
                            <div class="col-sm-9">
                                <input type="url" class="form-control" name="url" value="<?php echo htmlspecialchars($url); ?>" maxlength="2000">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings['email']; ?> :</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" maxlength="2000">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings['comments']; ?> :</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="c" rows="3"><?php echo htmlspecialchars($c); ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings['logo']; ?> :</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control" name="upload" accept="image/*">
                                <div class="form-text"><?php echo $strings['logo_upload_help'] ?? 'Upload an image file for the organization logo.'; ?></div>
                            </div>
                        </div>

                        <?php if ($id != '' && !empty($clientDetail->org_extension_logo[0])):
                            $logoPath = '../logos_clients/' . $id . '.' . $clientDetail->org_extension_logo[0];
                            if (file_exists($logoPath)):
                                ?>
                                <div class="row mb-3">
                                    <div class="col-sm-9 offset-sm-3">
                                        <div class="mb-2">
                                            <img src="<?php echo $logoPath; ?>" class="img-fluid" style="max-height: 150px;">
                                        </div>
                                        <input type="hidden" name="extensionOld" value="<?php echo $clientDetail->org_extension_logo[0]; ?>">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="logoDel" value="on" id="logoDelete">
                                            <label class="form-check-label" for="logoDelete">
                                                <?php echo $strings['delete']; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; endif; ?>

                        <div class="row mb-3">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary"><?php echo $strings['save']; ?></button>
                            </div>
                        </div>
                    </div>
                </form>
        </div>
    </div>


<?php
require_once('../themes/' . THEME . '/footer.php');

?>
