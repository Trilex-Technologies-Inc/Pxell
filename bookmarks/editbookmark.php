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

$id = (string) ($_GET['id'] ?? '');
$action = (string) ($_GET['action'] ?? '');
$name = (string) ($_POST['name'] ?? '');
$url = (string) ($_POST['url'] ?? '');
$description = (string) ($_POST['description'] ?? '');
$category = (string) ($_POST['category'] ?? '0');
$category_new = (string) ($_POST['category_new'] ?? '');
$shared = (string) ($_POST['shared'] ?? '');
$home = (string) ($_POST['home'] ?? '');
$comments = (string) ($_POST['comments'] ?? '');
$piecesNew = $_POST['piecesNew'] ?? array();
$users = '';
$error = '';
$bookmarkDetail = new request();
if ($id === '') {
    $bookmarkDetail->boo_category = array($category);
    $bookmarkDetail->boo_users = array('');
}

if ($id != '' && $action != 'add') {
    $tmpquery = "WHERE boo.id = '$id'";
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
                        <?php echo $strings['add_bookmark']; ?>
                    <?php else: ?>
                        <?php echo $strings['edit_bookmark'] . ' : ' . htmlspecialchars($bookmarkDetail->boo_name[0]); ?>
                    <?php endif; ?>
                </h5>
            </div>

            <?php if ($id == ''): ?>
            <form method="POST" action="../bookmarks/editbookmark.php?action=add" name="booForm" id="booForm">
                <?php else: ?>
                <form method="POST" action="../bookmarks/editbookmark.php?id=<?php echo $id; ?>&amp;action=update" name="booForm" id="booForm">
                    <?php endif; ?>

                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted"><?php echo $strings['details']; ?></h6>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings['bookmark_category']; ?> :</label>
                            <div class="col-sm-9">
                                <select name="category" class="form-select">
                                    <option value="0">-</option>
                                    <?php
                                    $tmpquery = 'ORDER BY boocat.name';
                                    $listCategories = new request();
                                    $listCategories->openBookmarksCategories($tmpquery);
                                    $comptListCategories = count($listCategories->boocat_id);

                                    for ($i = 0; $i < $comptListCategories; $i++) {
                                        $selected = ($listCategories->boocat_id[$i] == $bookmarkDetail->boo_category[0]) ? 'selected' : '';
                                        echo '<option value="' . $listCategories->boocat_id[$i] . '" ' . $selected . '>' .
                                            htmlspecialchars($listCategories->boocat_name[$i]) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings["bookmark_category_new"]; ?> :</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="category_new" value="<?php echo htmlspecialchars($category_new); ?>">
                                <div class="form-text"><?php echo $strings["create_new_category"]; ?></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings["name"]; ?> :</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>" autofocus required>
                                <div class="invalid-feedback"><?php echo $strings["name_required"]; ?></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings["url"]; ?> :</label>
                            <div class="col-sm-9">
                                <input type="url" class="form-control" name="url" value="<?php echo htmlspecialchars($url); ?>" required>
                                <div class="invalid-feedback"><?php echo $strings["url_required"]; ?></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings["description"]; ?> :</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-9 offset-sm-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="shared" name="shared" value="1" <?php echo isset($checkedShared) ? $checkedShared : ''; ?>>
                                    <label for="shared" class="form-check-label"><?php echo $strings["shared"]; ?></label>
                                    <div class="form-text"><?php echo $strings["shared_bookmark_help"]; ?></div>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="home" name="home" value="1" <?php echo isset($checkedHome) ? $checkedHome : ''; ?>>
                                    <label for="home" class="form-check-label"><?php echo $strings["home"]; ?></label>
                                    <div class="form-text"><?php echo $strings["home_bookmark_help"]; ?></div>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="comments" name="comments" value="1" <?php echo isset($checkedComments) ? $checkedComments : ''; ?>>
                                    <label for="comments" class="form-check-label"><?php echo $strings["comments"]; ?></label>
                                    <div class="form-text"><?php echo $strings["comments_bookmark_help"]; ?></div>
                                </div>
                            </div>
                        </div>

                        <?php
                        if ($demoMode == true) {
                            $tmpquery = "WHERE mem.id != '" . $_SESSION['idSession'] . "' AND mem.profil != '3' ORDER BY mem.login";
                        } else {
                            $tmpquery = "WHERE mem.id != '" . $_SESSION['idSession'] . "' AND mem.profil != '3' AND mem.id != '2' ORDER BY mem.login";
                        }

                        $listUsers = new request();
                        $listUsers->openMembers($tmpquery);
                        $comptListUsers = count($listUsers->mem_id);

                        $oldCaptured = isset($bookmarkDetail->boo_users[0]) ? $bookmarkDetail->boo_users[0] : '';
                        if (!empty($bookmarkDetail->boo_users[0])) {
                            $listCaptured = explode('|', $bookmarkDetail->boo_users[0]);
                            $comptListCaptured = count($listCaptured);
                        }

                        if ($comptListUsers != '0'):
                            ?>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label"><?php echo $strings['private']; ?> :</label>
                                <div class="col-sm-9">
                                    <select name="piecesNew[]" multiple size="8" class="form-select">
                                        <?php
                                        for ($i = 0; $i < $comptListUsers; $i++) {
                                            $selected = '';
                                            if (isset($comptListCaptured)) {
                                                for ($j = 0; $j < $comptListCaptured; $j++) {
                                                    if ($listUsers->mem_id[$i] == $listCaptured[$j]) {
                                                        $selected = 'selected';
                                                        break;
                                                    }
                                                }
                                            }
                                            echo '<option value="' . $listUsers->mem_id[$i] . '" ' . $selected . '>' .
                                                htmlspecialchars($listUsers->mem_login[$i]) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <input type="hidden" name="oldCaptured" value="<?php echo htmlspecialchars($oldCaptured); ?>">
                                    <div class="form-text"><?php echo $strings["private_bookmark_help"]; ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

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
