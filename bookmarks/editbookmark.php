<?php // $Revision: 1.6 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: editbookmark.php,v 1.6 2004/12/15 12:25:18 pixtur Exp $
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

if ($id != '' && $action != 'add') {
    $tmpquery = "WHERE boo.id = '$id'";
    $bookmarkDetail = new request();
    $bookmarkDetail->openBookmarks($tmpquery);

    if ($bookmarkDetail->boo_owner[0] != $_SESSION['idSession']) {
        header('Location: ../bookmarks/listbookmarks.php?view=my&msg=bookmarkOwner');
        exit;
    } 
} 

// case update bookmark entry
if ($id != '') {
    // case update bookmark entry
    if ($action == 'update') {
        if ($piecesNew != '') {
            $users = '|' . implode('|', $piecesNew) . '|';
        } 

        if ($category_new != '') {
            $tmpquery = "WHERE boocat.name = '$category_new'";
            $listCategories = new request();
            $listCategories->openBookmarksCategories($tmpquery);
            $comptListCategories = count($listCategories->boocat_id);

            if ($comptListCategories == '0') {
                $tmpquery1 = 'INSERT INTO ' . $tableCollab['bookmarks_categories'] . "(name) VALUES('$category_new')";
                connectSql($tmpquery1);
                $tmpquery = $tableCollab['bookmarks_categories'];
                last_id($tmpquery);
                $num = $lastId[0];
                unset($lastId);
                $category = $num;
            } else {
                $category = $listCategories->boocat_id[0];
            } 
        } 

        if ($shared == '' || $users != '') {
            $shared = '0';
        } 

        if ($home == '') {
            $home = '0';
        } 

        if ($comments == '') {
            $comments = '0';
        } 

        $name = convertData($name);
        $description = convertData($description);
        $tmpquery5 = 'UPDATE ' . $tableCollab['bookmarks'] . " SET url='$url',name='$name',description='$description',modified='$dateheure',category='$category',shared='$shared',home='$home',comments='$comments',users='$users' WHERE id = '$id'";
        connectSql($tmpquery5);
        header('Location: ../bookmarks/listbookmarks.php?view=my&msg=update');
        exit;
    } 

    // set value in form
    $name = $bookmarkDetail->boo_name[0];
    $url = $bookmarkDetail->boo_url[0];
    $description = $bookmarkDetail->boo_description[0];
    $category = $bookmarkDetail->boo_category[0];
    $shared = $bookmarkDetail->boo_shared[0];

    if ($shared == '1') {
        $checkedShared = 'checked';
    } 

    $home = $bookmarkDetail->boo_home[0];

    if ($home == '1') {
        $checkedHome = 'checked';
    } 

    $comments = $bookmarkDetail->boo_comments[0];

    if ($comments == '1') {
        $checkedComments = 'checked';
    } 
} 

// case add note entry
if ($id == '') {
    $checkedShared = 'checked';
    $checkedComments = 'checked'; 
    // case add note entry
    if ($action == 'add') {
        if ($piecesNew != '') {
            $users = '|' . implode('|', $piecesNew) . '|';
        } 

        if ($category_new != '') {
            $tmpquery = "WHERE boocat.name = '$category_new'";
            $listCategories = new request();
            $listCategories->openBookmarksCategories($tmpquery);
            $comptListCategories = count($listCategories->boocat_id);

            if ($comptListCategories == '0') {
                $tmpquery1 = 'INSERT INTO ' . $tableCollab['bookmarks_categories'] . "(name) VALUES('$category_new')";
                connectSql($tmpquery1);
                $tmpquery = $tableCollab['bookmarks_categories'];
                last_id($tmpquery);
                $num = $lastId[0];
                unset($lastId);
                $category = $num;
            } else {
                $category = $listCategories->boocat_id[0];
            } 
        } 

        if ($shared == '' || $users != '') {
            $shared = '0';
        } 

        if ($home == '') {
            $home = '0';
        } 

        if ($comments == '') {
            $comments = '0';
        } 

        $name = convertData($name);
        $description = convertData($description);
        $tmpquery1 = 'INSERT INTO ' . $tableCollab['bookmarks'] . "(owner,category,name,url,description,shared,home,comments,users,created) VALUES('" . $_SESSION['idSession'] . "','$category','$name','$url','$description','$shared','$home','$comments','$users','$dateheure')";
        connectSql($tmpquery1);
        header('Location: ../bookmarks/listbookmarks.php?view=my&msg=add');
        exit;
    } 
} 


//--- header -----
$breadcrumbs[]=buildLink('../bookmarks/listbookmarks.php?view=my', $strings['bookmarks'], LINK_INSIDE);

if ($id == '') {
    $breadcrumbs[]=$strings['add_bookmark'];
} 

if ($id != '') {
    $breadcrumbs[]=buildLink('../bookmarks/viewbookmark.php?id=' . $bookmarkDetail->boo_id[0], $bookmarkDetail->boo_name[0], LINK_INSIDE);
    $breadcrumbs[]=$strings['edit_bookmark'];
} 

$bodyCommand = 'onLoad="document.booForm.name.focus();"';
$pageSection = 'bookmarks';
require_once('../themes/' . THEME . '/header.php');


//----- content ------
$block1 = new block();

if ($id == '') {
    $block1->form = 'boo';
    $block1->openForm('../bookmarks/editbookmark.php?action=add#' . $block1->form . 'Anchor');
} 

if ($id != '') {
    $block1->form = 'boo';
    $block1->openForm('../bookmarks/editbookmark.php?id=' . $id . '&amp;action=update#' . $block1->form . 'Anchor');
} 

if ($error != '') {
    $block1->headingError($strings['errors']);
    $block1->contentError($error);
} 

if ($id == '') {
    $block1->headingForm($strings['add_bookmark']);
} 
else {
    $block1->headingForm($strings['edit_bookmark'] . ' : ' . $bookmarkDetail->boo_name[0]);
} 

$block1->openContent();
$block1->contentTitle($strings['details']);

echo '<div class="mb-3">';
echo '<label class="form-label">' . $strings['bookmark_category'] . ' :</label>';
echo '<select name="category" class="form-select">';
echo '<option value="0">-</option>';

$tmpquery = 'ORDER BY boocat.name';
$listCategories = new request();
$listCategories->openBookmarksCategories($tmpquery);
$comptListCategories = count($listCategories->boocat_id);

for ($i = 0; $i < $comptListCategories; $i++) {
    $selected = ($listCategories->boocat_id[$i] == $bookmarkDetail->boo_category[0]) ? 'selected' : '';
    echo '<option value="' . $listCategories->boocat_id[$i] . '" ' . $selected . '>' . $listCategories->boocat_name[$i] . '</option>';
}
echo '</select>';
echo '</div>';

/* --- New Category --- */
echo '<div class="mb-3">';
echo '<label class="form-label">' . $strings["bookmark_category_new"] . ' :</label>';
echo '<input type="text" class="form-control" name="category_new" value="' . htmlspecialchars($category_new) . '" style="max-width:400px;">';
echo '</div>';

/* --- Name --- */
echo '<div class="mb-3">';
echo '<label class="form-label">' . $strings["name"] . ' :</label>';
echo '<input type="text" class="form-control" name="name" value="' . htmlspecialchars($name) . '" style="max-width:400px;">';
echo '</div>';

/* --- URL --- */
echo '<div class="mb-3">';
echo '<label class="form-label">' . $strings["url"] . ' :</label>';
echo '<input type="text" class="form-control" name="url" value="' . htmlspecialchars($url) . '" style="max-width:400px;">';
echo '</div>';

/* --- Description --- */
echo '<div class="mb-3">';
echo '<label class="form-label">' . $strings["description"] . ' :</label>';
echo '<textarea class="form-control" name="description" rows="6" style="max-width:400px;">' . htmlspecialchars($description) . '</textarea>';
echo '</div>';

/* --- Shared --- */
echo '<div class="form-check mb-2">';
echo '<input class="form-check-input" type="checkbox" id="shared" name="shared" value="1" ' . $checkedShared . '>';
echo '<label for="shared" class="form-check-label">' . $strings["shared"] . '</label>';
echo '</div>';

/* --- Home --- */
echo '<div class="form-check mb-2">';
echo '<input class="form-check-input" type="checkbox" id="home" name="home" value="1" ' . $checkedHome . '>';
echo '<label for="home" class="form-check-label">' . $strings["home"] . '</label>';
echo '</div>';

/* --- Comments --- */
echo '<div class="form-check mb-3">';
echo '<input class="form-check-input" type="checkbox" id="comments" name="comments" value="1" ' . $checkedComments . '>';
echo '<label for="comments" class="form-check-label">' . $strings["comments"] . '</label>';
echo '</div>';

/* --- Private / Members --- */
if ($demoMode == true) {
    $tmpquery = "WHERE mem.id != '" . $_SESSION['idSession'] . "' AND mem.profil != '3' ORDER BY mem.login";
} else {
    $tmpquery = "WHERE mem.id != '" . $_SESSION['idSession'] . "' AND mem.profil != '3' AND mem.id != '2' ORDER BY mem.login";
}

$listUsers = new request();
$listUsers->openMembers($tmpquery);
$comptListUsers = count($listUsers->mem_id);

$oldCaptured = $bookmarkDetail->boo_users[0];
if ($bookmarkDetail->boo_users[0] != "") {
    $listCaptured = explode('|', $bookmarkDetail->boo_users[0]);
    $comptListCaptured = count($listCaptured);
}

if ($comptListUsers != '0') {
    echo '<div class="mb-3">';
    echo '<label class="form-label">' . $strings['private'] . ' :</label>';
    echo '<select name="piecesNew[]" multiple size="10" class="form-select" style="max-width:400px;">';

    for ($i = 0; $i < $comptListUsers; $i++) {
        $selected = '';
        for ($j = 0; $j < $comptListCaptured; $j++) {
            if ($listUsers->mem_id[$i] == $listCaptured[$j]) {
                $selected = 'selected';
                break;
            }
        }
        echo '<option value="' . $listUsers->mem_id[$i] . '" ' . $selected . '>' . $listUsers->mem_login[$i] . '</option>';
    }

    echo '</select>';
    echo '<input type="hidden" name="oldCaptured" value="' . htmlspecialchars($oldCaptured) . '">';
    echo '</div>';
}

/* --- Submit --- */
echo '<div class="mt-3">';
echo '<input type="submit" class="btn btn-primary" value="' . $strings['save'] . '">';
echo '</div>';

$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once('../themes/' . THEME . '/footer.php');

?>
