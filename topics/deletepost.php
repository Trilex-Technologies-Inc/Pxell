<?php // $Revision: 1.4 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: deletepost.php,v 1.4 2004/12/15 12:25:12 pixtur Exp $
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

$tmpquery = "WHERE topic.id = '$topic'";
$detailTopic = new request();
$detailTopic->openTopics($tmpquery);

if ($action == "delete" && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $detailTopic->top_posts[0] = max(0, (int) ($detailTopic->top_posts[0] ?? 0) - 1);
    $tmpquery = "DELETE FROM " . $tableCollab["posts"] . " WHERE id = '$id'";
    connectSql("$tmpquery");
    $tmpquery2 = "UPDATE " . $tableCollab["topics"] . " SET posts='" . $detailTopic->top_posts[0] . "' WHERE id = '$topic'";
    connectSql("$tmpquery2");
    header("Location: ../topics/viewtopic.php?msg=delete&id=$topic");
    exit;
} 

$tmpquery = "WHERE pos.id = '$id'";
$detailPost = new request();
$detailPost->openPosts($tmpquery);



//--- header ---
$breadcrumbs[]= buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[]= buildLink("../projects/viewproject.php?id=" . $detailTopic->top_pro_id[0], $detailTopic->top_pro_name[0], LINK_INSIDE);
$breadcrumbs[]= buildLink("../topics/listtopics.php?project=" . $detailTopic->top_pro_id[0], $strings["discussion"], LINK_INSIDE);
$breadcrumbs[]= buildLink("../topics/viewtopic.php?id=" . $detailTopic->top_id[0], $detailTopic->top_subject[0], LINK_INSIDE);
$breadcrumbs[]= $strings["delete_messages"];


require_once("../themes/" . THEME . "/header.php");

//--- content ---
$topicSubject = htmlspecialchars($detailTopic->top_subject[0], ENT_QUOTES, 'UTF-8');
$postAuthor = htmlspecialchars($detailPost->pos_mem_name[0] ?? '', ENT_QUOTES, 'UTF-8');
$postDate = createDate($detailPost->pos_created[0] ?? '', $_SESSION['timezoneSession']);
?>
<style>
    .delete-post-page { max-width: 760px; margin: 1rem auto 3rem; }
    .delete-post-card { overflow: hidden; border: 1px solid #f1c8cc; border-radius: 1rem; background: #fff; box-shadow: 0 16px 40px rgba(91, 33, 42, .11); }
    .delete-post-header { display: flex; gap: 1rem; align-items: center; padding: 1.4rem 1.5rem; color: #842029; background: linear-gradient(135deg, #fff2f3, #fde3e5); border-bottom: 1px solid #f1c8cc; }
    .delete-post-icon { display: grid; width: 52px; height: 52px; flex: 0 0 52px; place-items: center; color: #fff; border-radius: 50%; background: #dc3545; box-shadow: 0 8px 18px rgba(220, 53, 69, .25); font-size: 1.25rem; }
    .delete-post-header h1 { margin: 0 0 .2rem; font-size: 1.35rem; }
    .delete-post-header p { margin: 0; color: #9b4a52; }
    .delete-post-body { padding: 1.5rem; }
    .delete-post-context { margin-bottom: 1rem; color: #66758a; font-size: .9rem; }
    .delete-post-context strong { color: #293b56; }
    .delete-post-preview { overflow: hidden; border: 1px solid #dfe6ee; border-radius: .8rem; background: #f8fafc; }
    .delete-post-preview__meta { display: flex; flex-wrap: wrap; justify-content: space-between; gap: .35rem 1rem; padding: .8rem 1rem; color: #68778a; border-bottom: 1px solid #e5ebf2; font-size: .85rem; }
    .delete-post-preview__message { max-height: 280px; overflow: auto; padding: 1rem; color: #34445a; line-height: 1.65; overflow-wrap: anywhere; }
    .delete-post-note { display: flex; gap: .65rem; margin-top: 1rem; padding: .8rem 1rem; color: #664d03; border-radius: .65rem; background: #fff3cd; font-size: .9rem; }
    .delete-post-actions { display: flex; justify-content: flex-end; gap: .75rem; padding: 1rem 1.5rem; border-top: 1px solid #edf0f4; background: #fbfcfd; }
    @media (max-width: 560px) {
        .delete-post-page { margin-top: .5rem; }
        .delete-post-header, .delete-post-body { padding: 1.1rem; }
        .delete-post-actions { flex-direction: column-reverse; padding: 1rem; }
        .delete-post-actions .btn { width: 100%; }
    }
</style>

<main class="delete-post-page">
    <section class="delete-post-card">
        <header class="delete-post-header">
            <div class="delete-post-icon" aria-hidden="true"><i class="fa-regular fa-trash-can"></i></div>
            <div>
                <h1><?php echo $strings["delete_messages"]; ?></h1>
                <p><?php echo $strings["delete_following"]; ?></p>
            </div>
        </header>

        <div class="delete-post-body">
            <div class="delete-post-context"><i class="fa-regular fa-comments me-1"></i><?php echo $strings["discussion"]; ?>: <strong><?php echo $topicSubject; ?></strong></div>
            <article class="delete-post-preview">
                <div class="delete-post-preview__meta">
                    <span><i class="fa-regular fa-user me-1"></i><?php echo $strings["posted_by"]; ?>: <strong><?php echo $postAuthor; ?></strong></span>
                    <span><i class="fa-regular fa-clock me-1"></i><?php echo $postDate; ?></span>
                </div>
                <div class="delete-post-preview__message"><?php echo nl2br($detailPost->pos_message[0]); ?></div>
            </article>
            <div class="delete-post-note"><i class="fa-solid fa-triangle-exclamation mt-1"></i><span><?php echo $strings["delete_messages"]; ?>. This action cannot be undone.</span></div>
        </div>

        <form method="POST" action="../topics/deletepost.php?id=<?php echo (int) $id; ?>&amp;topic=<?php echo (int) $topic; ?>&amp;action=delete" class="delete-post-actions">
            <a class="btn btn-outline-secondary px-4" href="../topics/viewtopic.php?id=<?php echo (int) $topic; ?>"><i class="fa-solid fa-arrow-left me-1"></i><?php echo $strings["cancel"]; ?></a>
            <button class="btn btn-danger px-4" type="submit" name="delete"><i class="fa-regular fa-trash-can me-2"></i><?php echo $strings["delete"]; ?></button>
        </form>
    </section>
</main>
<?php

require_once("../themes/" . THEME . "/footer.php");

?>
