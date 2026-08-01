<?php // $Revision: 1.3 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: threadpost.php,v 1.3 2004/12/22 22:16:31 madbear Exp $
 * 
 * Copyright (c) 2003 by the NetOffice developers
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

$checkSession = true;
require_once("../includes/library.php");

$tmpquery = "WHERE topic.id = '$id'";
$detailTopic = new request();
$detailTopic->openTopics($tmpquery);

if ($detailTopic->top_published[0] == "1" || $detailTopic->top_project[0] != $_SESSION['projectSession']) {
    header('Location: index.php');
    exit;
} 

if ($action == "add" && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $detailTopic->top_posts[0] = $detailTopic->top_posts[0] + 1;
    $messageField = convertData($messageField);
    autoLinks($messageField);
    $tmpquery1 = "INSERT INTO " . $tableCollab["posts"] . "(topic,member,created,message) VALUES('$id','" . $_SESSION['idSession'] . "','$dateheure','$newText')";
    connectSql("$tmpquery1");
    $tmpquery2 = "UPDATE " . $tableCollab["topics"] . " SET last_post='$dateheure',posts='" . $detailTopic->top_posts[0] . "' WHERE id = '$id'";
    connectSql("$tmpquery2");

    if ($notifications == "true") {
        $tmpquery = "WHERE pro.id = '" . $_SESSION['projectSession'] . "'";
        $projectDetail = new request();
        $projectDetail->openProjects($tmpquery);

        require_once("../topics/noti_newpost.php");
    } 
    // header("Location: showallthreads.php?id=$id");
    // exit;
} 

$bouton[5] = "over";
$titlePage = $strings["post_reply"];
require_once ("include_header.php");

$idStatus = $detailTopic->top_status[0];

echo '<form accept-charset="UNKNOWN" method="POST" action="../projects_site/threadpost.php?action=add" name="post" enctype="application/x-www-form-urlencoded">
<input name="id" type="hidden" value="' . $id . '">
<div class="card mb-3">
<div class="card-header">
<h4>' . htmlspecialchars($detailTopic->top_subject[0]) . '</h4>
</div>
<div class="card-body">
<h5>' . htmlspecialchars($strings["information"]) . '</h5>
<dl class="row mb-3">
<dt class="col-sm-3">' . htmlspecialchars($strings["project"]) . ':</dt>
<dd class="col-sm-3">' . htmlspecialchars($projectDetail->pro_name[0]) . '</dd>
<dt class="col-sm-3">' . htmlspecialchars($strings["posts"]) . ':</dt>
<dd class="col-sm-3">' . htmlspecialchars($detailTopic->top_posts[0]) . '</dd>
<dt class="col-sm-3">' . htmlspecialchars($strings["last_post"]) . ':</dt>
<dd class="col-sm-3">' . htmlspecialchars(createDate($detailTopic->top_last_post[0], $_SESSION['timezoneSession'])) . '</dd>
<dt class="col-sm-3">' . htmlspecialchars($strings["retired"]) . ':</dt>
<dd class="col-sm-3">' . htmlspecialchars($statusTopicBis[$idStatus]) . '</dd>
<dt class="col-sm-3">' . htmlspecialchars($strings["owner"]) . ':</dt>
<dd class="col-sm-9"><a href="mailto:' . htmlspecialchars($detailTopic->top_mem_email_work[0]) . '">' . htmlspecialchars($detailTopic->top_mem_login[0]) . '</a></dd>
</dl>
<hr>
<h5>' . htmlspecialchars($strings["enter_message"]) . '</h5>
<div class="mb-3">
<label for="messageField" class="form-label">* ' . htmlspecialchars($strings["message"]) . ':</label>
<textarea class="form-control" id="messageField" name="messageField" rows="6" cols="60"></textarea>
</div>
<div class="mb-3">
<button type="submit" name="submit" class="btn btn-primary">' . htmlspecialchars($strings["save"]) . '</button>
</div>
</div>
</div>
</form>';

$tmpquery = "WHERE pos.topic = '" . $detailTopic->top_id[0] . "' ORDER BY pos.created DESC";
$listPosts = new request();
$listPosts->openPosts($tmpquery);
$comptListPosts = count($listPosts->pos_id);

if ($comptListPosts != "0") {
    for ($i = 0;$i < $comptListPosts;$i++) {
        echo '<div class="card mb-3">
<div class="card-header d-flex justify-content-between align-items-center">
<div><strong>' . htmlspecialchars($strings["posted_by"]) . ':</strong> ' . htmlspecialchars($listPosts->pos_mem_name[$i]) . '</div>
<div><a href="../projects_site/threadpost.php?id=' . $id . '&amp;action=delete&amp;post=' . $listPosts->pos_id[$i] . '" class="btn btn-sm btn-outline-danger">' . htmlspecialchars($strings["delete_message"]) . '</a></div>
</div>
<div class="card-body">
<dl class="row mb-2">
<dt class="col-sm-2">' . htmlspecialchars($strings["email"]) . ':</dt>
<dd class="col-sm-10"><a href="mailto:' . htmlspecialchars($listPosts->pos_mem_email_work[$i]) . '">' . htmlspecialchars($listPosts->pos_mem_email_work[$i]) . '</a></dd>
<dt class="col-sm-2">' . htmlspecialchars($strings["when"]) . ':</dt>
<dd class="col-sm-10">' . htmlspecialchars(createDate($listPosts->pos_created[$i], $_SESSION['timezoneSession'])) . '</dd>
<dt class="col-sm-2">' . htmlspecialchars($strings["message"]) . ':</dt>
<dd class="col-sm-10">' . nl2br(htmlspecialchars($listPosts->pos_message[$i])) . '</dd>
</dl>
</div>
</div>';
    } 
} else {
    echo '<div class="alert alert-info">' . htmlspecialchars($strings["no_items"]) . '</div>';
}

require_once ("include_footer.php");

?>
