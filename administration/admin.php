<?php // $Revision: 1.9 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: admin.php,v 1.9 2005/01/27 10:32:28 dylan_cuthbert Exp $
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

$breadcrumbs[]=buildLink('../administration/admin.php', $strings['administration'], LINK_INSIDE);
$breadcrumbs[]=$strings['admin_intro'];

$pageSection = 'admin';
require_once('../themes/' . THEME . '/header.php');

$adminCards = array(
    array(
        'href' => '../users/listusers.php',
        'label' => $strings['user_management'],
        'icon' => 'fa-users',
        'tone' => 'blue'
    ),
    array(
        'href' => '../services/listservices.php',
        'label' => $strings['service_management'],
        'icon' => 'fa-screwdriver-wrench',
        'tone' => 'green'
    ),
    array(
        'href' => '../administration/systeminfo.php',
        'label' => text('system_information'),
        'icon' => 'fa-microchip',
        'tone' => 'slate'
    ),
    array(
        'href' => '../administration/mycompany.php',
        'label' => text('company_details'),
        'icon' => 'fa-building',
        'tone' => 'blue'
    ),
    array(
        'href' => '../administration/listlogs.php',
        'label' => text('logs'),
        'icon' => 'fa-clipboard-list',
        'tone' => 'slate'
    ),
    array(
        'href' => '../administration/updatesettings.php',
        'label' => text('edit_settings'),
        'icon' => 'fa-sliders',
        'tone' => 'green'
    ),
    array(
        'href' => '../administration/listholidays.php',
        'label' => text('edit_holidays'),
        'icon' => 'fa-calendar-days',
        'tone' => 'blue'
    ),
    array(
        'href' => '../administration/edit_language.php',
        'label' => text('edit_language'),
        'icon' => 'fa-language',
        'tone' => 'slate'
    )
);

if ($supportType == 'admin') {
    $adminCards[] = array(
        'href' => '../administration/support.php',
        'label' => $strings['support_management'],
        'icon' => 'fa-life-ring',
        'tone' => 'green'
    );
}

if ($databaseType == 'mysql') {
    $adminCards[] = array(
        'href' => '../administration/phpmyadmin.php',
        'label' => $strings['database'],
        'icon' => 'fa-database',
        'tone' => 'blue'
    );
}
?>

<style>
    .admin-dashboard {
        display: grid;
        gap: 22px;
    }

    .admin-hero {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 24px;
        align-items: center;
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 12px 36px rgba(34, 49, 72, 0.09);
        padding: 24px;
    }

    .admin-hero__eyebrow {
        color: #2f6f6a;
        font-size: 0.78rem;
        font-weight: 750;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .admin-hero h1 {
        color: #162033;
        font-size: 1.9rem;
        font-weight: 750;
        letter-spacing: 0;
        margin: 0 0 8px;
    }

    .admin-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .admin-hero__meta span {
        background: #f6f9fb;
        border: 1px solid #d9e3ec;
        border-radius: 999px;
        color: #526174;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 6px 10px;
    }

    .admin-hero__badge {
        width: 72px;
        height: 72px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #e8f4f4;
        color: #2f6f6a;
        font-size: 1.8rem;
    }

    .admin-quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 14px;
    }

    .admin-tile {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 92px;
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(34, 49, 72, 0.07);
        color: #162033;
        padding: 16px;
        text-decoration: none;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
    }

    .admin-tile:hover {
        border-color: #b7c6d5;
        box-shadow: 0 16px 34px rgba(34, 49, 72, 0.12);
        color: #162033;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .admin-tile__icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 8px;
        font-size: 1.05rem;
    }

    .admin-tile__icon--blue {
        background: #e8f0f7;
        color: #164773;
    }

    .admin-tile__icon--green {
        background: #e8f4f4;
        color: #2f6f6a;
    }

    .admin-tile__icon--slate {
        background: #eef2f6;
        color: #526174;
    }

    .admin-tile__label {
        font-size: 0.98rem;
        font-weight: 750;
        line-height: 1.25;
    }

    .admin-panel {
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 12px 36px rgba(34, 49, 72, 0.09);
        padding: 18px;
    }

    .admin-panel__title {
        color: #162033;
        font-size: 1rem;
        font-weight: 750;
        margin: 0 0 12px;
    }

    .admin-alert {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        border-radius: 8px;
        padding: 14px;
    }

    .admin-alert + .admin-alert {
        margin-top: 10px;
    }

    .admin-alert--warning {
        background: #fff7df;
        color: #745319;
    }

    .admin-alert--info {
        background: #e8f4f4;
        color: #204f4b;
    }

    .admin-alert i {
        margin-top: 2px;
    }

    @media (max-width: 720px) {
        .admin-hero {
            grid-template-columns: 1fr;
            padding: 20px;
        }

        .admin-hero__badge {
            width: 56px;
            height: 56px;
            font-size: 1.35rem;
        }
    }
</style>

<div class="admin-dashboard">
    <section class="admin-hero">
        <div>
            <div class="admin-hero__eyebrow"><?php echo $strings['administration']; ?></div>
            <h1><?php echo $strings['admin_intro']; ?></h1>
            <div class="admin-hero__meta" aria-label="Administration status">
                <span><?php echo htmlspecialchars($databaseType); ?></span>
                <span>v<?php echo htmlspecialchars($version); ?></span>
                <span><?php echo htmlspecialchars($installationType); ?></span>
            </div>
        </div>
        <div class="admin-hero__badge" aria-hidden="true">
            <i class="fa fa-user-shield"></i>
        </div>
    </section>

    <section class="admin-quick-grid">
        <?php
        foreach ($adminCards as $card) {
            echo '<a class="admin-tile" href="' . htmlspecialchars($card['href']) . '">';
            echo '<span class="admin-tile__icon admin-tile__icon--' . htmlspecialchars($card['tone']) . '"><i class="fa ' . htmlspecialchars($card['icon']) . '"></i></span>';
            echo '<span class="admin-tile__label">' . htmlspecialchars($card['label']) . '</span>';
            echo '</a>';
        }
        ?>
    </section>

    <?php if (($updateChecker == 'true' && $installationType == 'online') || is_dir('../installation')) { ?>
        <section class="admin-panel">
            <h2 class="admin-panel__title"><?php echo $strings['attention']; ?></h2>

            <?php if ($updateChecker == 'true' && $installationType == 'online') { ?>
                <div class="admin-alert admin-alert--info">
                    <i class="fa fa-circle-info"></i>
                    <div><?php echo updatechecker($version); ?></div>
                </div>
            <?php } ?>

            <?php if (is_dir('../installation')) { ?>
                <div class="admin-alert admin-alert--warning">
                    <i class="fa fa-triangle-exclamation"></i>
                    <div><strong><?php echo $strings['attention']; ?></strong> : <?php echo $strings['install_erase']; ?></div>
                </div>
            <?php } ?>
        </section>
    <?php } ?>
</div>
<?php


require_once('../themes/' . THEME . '/footer.php');

?>
