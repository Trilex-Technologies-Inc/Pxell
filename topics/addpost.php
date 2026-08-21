<?php // $Revision: 1.5 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: addpost.php,v 1.5 2004/12/15 12:25:12 pixtur Exp $
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

$tmpquery = "WHERE pro.id = '" . $detailTopic->top_project[0] . "'";
$projectDetail = new request();
$projectDetail->openProjects($tmpquery);

if ($action == "add" && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $tpm = convertData($tpm);
    autoLinks($tpm);
    $detailTopic->top_posts[0] = (int) ($detailTopic->top_posts[0] ?? 0) + 1;
    $tmpquery1 = "INSERT INTO " . $tableCollab["posts"] . "(topic,member,created,message) VALUES('$id','" . $_SESSION['idSession'] . "','$dateheure','$newText')";
    connectSql("$tmpquery1");
    $tmpquery2 = "UPDATE " . $tableCollab["topics"] . " SET last_post='$dateheure',posts='" . $detailTopic->top_posts[0] . "' WHERE id = '$id'";
    connectSql("$tmpquery2");

    if ($notifications == "true") {
        require_once("../topics/noti_newpost.php");
    } 
    header("Location: ../topics/viewtopic.php?id=$id&msg=add");
    exit;
} 

$idStatus = $detailTopic->top_status[0];
$idPublish = $detailTopic->top_published[0];

$tmpquery = "WHERE pos.topic = '" . $detailTopic->top_id[0] . "' ORDER BY pos.created DESC";
$listPosts = new request();
$listPosts->openPosts($tmpquery);
$comptListPosts = count($listPosts->pos_id);

if ($projectDetail->pro_org_id[0] == "1") {
    $projectDetail->pro_org_name[0] = $strings["none"];
} 

//--- header ----
$breadcrumbs[]= buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[]= buildLink("../projects/viewproject.php?id=" . $projectDetail->pro_id[0], $projectDetail->pro_name[0], LINK_INSIDE);
$breadcrumbs[]= buildLink("../topics/listtopics.php?project=" . $projectDetail->pro_id[0], $strings["discussions"], LINK_INSIDE);
$breadcrumbs[]=$detailTopic->top_subject[0];


$bodyCommand = "onLoad=\"document.ptTForm.tpm.focus();\"";
require_once("../themes/" . THEME . "/header.php");

//--- content ---
$topicSubject = htmlspecialchars($detailTopic->top_subject[0], ENT_QUOTES, 'UTF-8');
$projectName = htmlspecialchars($projectDetail->pro_name[0], ENT_QUOTES, 'UTF-8');
$organizationName = htmlspecialchars($projectDetail->pro_org_name[0], ENT_QUOTES, 'UTF-8');
$topicStatus = htmlspecialchars($statusTopicBis[$idStatus] ?? '', ENT_QUOTES, 'UTF-8');
$publishedStatus = htmlspecialchars($statusPublish[$idPublish] ?? '', ENT_QUOTES, 'UTF-8');
$postCount = (int) ($detailTopic->top_posts[0] ?? 0);
?>
<style>
    .reply-page { max-width: 1050px; margin: 0 auto 2rem; }
    .reply-hero {
        position: relative; overflow: hidden; padding: 1.75rem;
        color: #fff; border-radius: 1rem;
        background: linear-gradient(135deg, #273b68 0%, #315b9c 55%, #397ac1 100%);
        box-shadow: 0 14px 34px rgba(31, 55, 91, .18);
    }
    .reply-hero::after {
        content: ""; position: absolute; width: 220px; height: 220px;
        right: -65px; top: -105px; border-radius: 50%; background: rgba(255, 255, 255, .08);
    }
    .reply-hero__kicker { margin-bottom: .45rem; font-size: .78rem; font-weight: 700; letter-spacing: .09em; opacity: .8; text-transform: uppercase; }
    .reply-hero h1 { position: relative; z-index: 1; margin: 0; font-size: clamp(1.4rem, 3vw, 2rem); overflow-wrap: anywhere; }
    .reply-hero__meta { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: .65rem 1.25rem; margin-top: 1rem; color: rgba(255, 255, 255, .82); font-size: .9rem; }
    .reply-hero__meta a { color: #fff; font-weight: 600; }
    .reply-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin: 1rem 0; }
    .reply-stat { padding: .9rem 1rem; border: 1px solid #dfe7f1; border-radius: .8rem; background: #fff; }
    .reply-stat__label { color: #718096; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .reply-stat__value { margin-top: .25rem; color: #203454; font-weight: 700; overflow-wrap: anywhere; }
    .reply-composer { margin-bottom: 1.5rem; border: 1px solid #d9e3ef; border-radius: 1rem; background: #fff; box-shadow: 0 10px 28px rgba(34, 49, 72, .08); }
    .reply-composer__header { display: flex; align-items: center; gap: .7rem; padding: 1rem 1.25rem; border-bottom: 1px solid #e6edf5; }
    .reply-composer__icon { display: grid; width: 38px; height: 38px; place-items: center; color: #fff; border-radius: 50%; background: #3478d4; }
    .reply-composer__body { padding: 1.25rem; }
    .reply-composer textarea { min-height: 170px; resize: vertical; }
    .discussion-feed__title { display: flex; align-items: center; justify-content: space-between; margin: 1.75rem 0 .9rem; }
    .discussion-feed__title h2 { margin: 0; font-size: 1.1rem; }
    .discussion-post { display: grid; grid-template-columns: 48px 1fr; gap: .9rem; padding: 1.15rem; border: 1px solid #e0e7f0; border-radius: .9rem; background: #fff; }
    .discussion-post + .discussion-post { margin-top: .75rem; }
    .discussion-post__avatar { display: grid; width: 44px; height: 44px; place-items: center; color: #315b9c; border-radius: 50%; background: #e8f1fc; font-size: 1rem; font-weight: 800; }
    .discussion-post__header { display: flex; flex-wrap: wrap; justify-content: space-between; gap: .35rem 1rem; padding-bottom: .65rem; border-bottom: 1px solid #edf1f6; }
    .discussion-post__author a { font-weight: 700; text-decoration: none; }
    .discussion-post__date { color: #77869a; font-size: .85rem; }
    .discussion-post__body { padding-top: .8rem; color: #34445a; line-height: 1.65; overflow-wrap: anywhere; }
    @media (max-width: 680px) {
        .reply-hero { padding: 1.3rem; }
        .reply-stats { grid-template-columns: 1fr; }
        .discussion-post { grid-template-columns: 1fr; }
        .discussion-post__avatar { display: none; }
    }
</style>

<main class="reply-page">
    <?php if ($error != ""): ?>
        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
    <?php endif; ?>

    <section class="reply-hero">
        <div class="reply-hero__kicker"><i class="fa-regular fa-comments me-2"></i><?php echo $strings["discussion"]; ?></div>
        <h1><?php echo $topicSubject; ?></h1>
        <div class="reply-hero__meta">
            <span><i class="fa-solid fa-diagram-project me-1"></i><?php echo buildLink("../projects/viewproject.php?id=" . $projectDetail->pro_id[0], $projectName . " (#" . $projectDetail->pro_id[0] . ")", LINK_INSIDE); ?></span>
            <span><i class="fa-regular fa-building me-1"></i><?php echo $organizationName; ?></span>
        </div>
    </section>

    <section class="reply-stats" aria-label="<?php echo htmlspecialchars($strings["info"], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="reply-stat"><div class="reply-stat__label"><?php echo $strings["posts"]; ?></div><div class="reply-stat__value"><?php echo $postCount; ?></div></div>
        <div class="reply-stat"><div class="reply-stat__label"><?php echo $strings["retired"]; ?></div><div class="reply-stat__value"><?php echo $topicStatus; ?></div></div>
        <div class="reply-stat"><div class="reply-stat__label"><?php echo $strings["last_post"]; ?></div><div class="reply-stat__value"><?php echo createDate($detailTopic->top_last_post[0], $_SESSION['timezoneSession']); ?></div></div>
    </section>

    <form method="POST" action="../topics/addpost.php?action=add&amp;id=<?php echo (int) $detailTopic->top_id[0]; ?>&amp;project=<?php echo (int) $detailTopic->top_project[0]; ?>" name="ptTForm" class="reply-composer">
        <div class="reply-composer__header">
            <span class="reply-composer__icon"><i class="fa-solid fa-reply"></i></span>
            <div><div class="fw-bold"><?php echo $strings["post_to_discussion"]; ?></div><div class="small text-muted"><?php echo $topicSubject; ?></div></div>
        </div>
        <div class="reply-composer__body">
            <label class="form-label fw-semibold" for="replyMessage"><?php echo $strings["message"]; ?></label>
            <textarea class="form-control" id="replyMessage" name="tpm" rows="7" required><?php echo htmlspecialchars($tpm ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="d-flex flex-wrap justify-content-between gap-2 border-top mt-3 pt-3">
                <a class="btn btn-outline-secondary" href="../topics/viewtopic.php?id=<?php echo (int) $detailTopic->top_id[0]; ?>"><i class="fa-solid fa-arrow-left me-1"></i><?php echo $strings["cancel"]; ?></a>
                <button class="btn btn-primary px-4" type="submit"><i class="fa-regular fa-paper-plane me-2"></i><?php echo $strings["save"]; ?></button>
            </div>
        </div>
    </form>

    <section class="discussion-feed">
        <div class="discussion-feed__title"><h2><?php echo $strings["posts"]; ?></h2><span class="badge rounded-pill text-bg-primary"><?php echo $comptListPosts; ?></span></div>
        <?php if ($comptListPosts == 0): ?>
            <div class="alert alert-light border text-muted"><?php echo $strings["no_items"]; ?></div>
        <?php endif; ?>
        <?php for ($i = 0; $i < $comptListPosts; $i++):
            $authorName = htmlspecialchars($listPosts->pos_mem_name[$i], ENT_QUOTES, 'UTF-8');
            $authorInitial = function_exists('mb_substr') ? mb_substr($authorName, 0, 1) : substr($authorName, 0, 1);
            $isNewPost = $listPosts->pos_created[$i] > $_SESSION['lastvisiteSession'];
        ?>
            <article class="discussion-post">
                <div class="discussion-post__avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper($authorInitial), ENT_QUOTES, 'UTF-8'); ?></div>
                <div>
                    <header class="discussion-post__header">
                        <div class="discussion-post__author"><?php echo buildLink($listPosts->pos_mem_email_work[$i], $authorName, LINK_MAIL); ?></div>
                        <time class="discussion-post__date"><?php if ($isNewPost): ?><span class="badge text-bg-success me-1"><?php echo $strings["new"]; ?></span><?php endif; ?><?php echo createDate($listPosts->pos_created[$i], $_SESSION['timezoneSession']); ?></time>
                    </header>
                    <div class="discussion-post__body"><?php echo nl2br($listPosts->pos_message[$i]); ?></div>
                </div>
            </article>
        <?php endfor; ?>
    </section>
</main>
<?php

require_once("../themes/" . THEME . "/footer.php");

?>
