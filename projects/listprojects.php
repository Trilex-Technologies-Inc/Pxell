<?php // $Revision: 1.10 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: listprojects.php,v 1.10 2005/06/11 05:23:55 vjack Exp $
 *
 * Copyright (c) 2003 by the NetOffice developers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

/**
 * listprojects.php
 *
 * list projects grouped by either 'Active', 'Inavtive', or 'All' by 
 * assigned status and sortable by header fields.
 */

$checkSession = true;
require_once('../includes/library.php');

$requestedShow = (string) ($_GET['show'] ?? 'active');
$show = in_array($requestedShow, array('active', 'inactive', 'all'), true) ? $requestedShow : 'active';
$borne1 = (int) ($_GET['borne1'] ?? 0);
$isAdministrator = (string) ($_SESSION['profilSession'] ?? '') === '0' ||
    (string) ($_SESSION['idSession'] ?? '') === '1' ||
    strcasecmp((string) ($_SESSION['loginSession'] ?? ''), 'admin') === 0;

// Administrators requested an unfiltered view of application data. Do not
// hide projects because their status is blank, legacy, inactive, or custom.
if ($isAdministrator) {
    $show = 'all';
}



//--- breadcrumbs ---
$blockPage = new block();

$breadcrumbs[]=$strings['projects'];

if ($show == 'inactive') {
	$breadcrumbs[]=
		buildLink('../projects/listprojects.php?show=active', $strings['active'], LINK_INSIDE)
		.' | '. $strings['inactive']
		.' | '. buildLink('../projects/listprojects.php?show=all', $strings['all'], LINK_INSIDE);
}
else if ($show == 'active') {
	$breadcrumbs[]=
	    $strings['active']
		.' | '
		.buildLink('../projects/listprojects.php?show=inactive', $strings['inactive'], LINK_INSIDE)
		.' | '
		.buildLink('../projects/listprojects.php?show=all', $strings['all'], LINK_INSIDE);
}
else if ($show == 'all') {
    $breadcrumbs[]=
    	buildLink('../projects/listprojects.php?show=active', $strings['active'], LINK_INSIDE)
    	. ' | ' . buildLink('../projects/listprojects.php?show=inactive', $strings['inactive'], LINK_INSIDE)
    	. ' | ' . $strings['all'];
}


//--- print header ---
$pageSection='projects';
require_once('../themes/' . THEME . '/header.php');


$blockPage= new block();
$blockPage->bornesNumber = '1';

{

	$block1 = new block();

	$block1->form = 'saP';
	$block1->openForm('../projects/listprojects.php?borne1=' . $borne1 . '&show=' . $show . '#' . $block1->form . 'Anchor');

	$block1->heading($strings['projects']);

	$block1->openPaletteIcon();

	if ($_SESSION['profilSession'] == 0 || $_SESSION['profilSession'] == 1 || $_SESSION['profilSession'] == 5) {
	    $block1->paletteIcon(0, 'add', $strings['add']);
	    $block1->paletteIcon(1, 'remove', $strings['delete']);
	}

	$block1->paletteIcon(2, 'info', $strings['view']);

	if ($_SESSION['profilSession'] == 0 || $_SESSION['profilSession'] == 1 || $_SESSION['profilSession'] == 5) {
	    $block1->paletteIcon(3, 'edit', $strings['edit']);
	    $block1->paletteIcon(4, 'copy', $strings['copy']);
	}

	if ($enable_cvs == 'true') {
	    $block1->paletteIcon(7, 'cvs', $strings['browse_cvs']);
	}

	// will be replacing with internal module for bug tracking
	if ($enableMantis == 'true') {
	    $block1->paletteIcon(8, 'bug', $strings['bug']);
	}
	$block1->closePaletteIcon();

	$block1->borne = $blockPage->returnBorne('1');
	$block1->rowsLimit = 20;
	
	$block1->sorting(
	   'projects', 
	   $sortingUser->sor_projects[0], 
	   'pro.name ASC', 
	   $sortingFields = array(
	       #'pro.id', 
	       'pro.priority', 
	       'pro.name', 
	       'org.name', 
	       'pro.status', 
	       'mem.login', 
	       'pro.published'
	       )
	   );

	if ($show == 'inactive') {
	    if ($projectsFilter == 'true' && !$isAdministrator) {
	        $tmpquery = 'LEFT OUTER JOIN ' . $tableCollab['teams'] . ' teams ON teams.project = pro.id ';
	        $tmpquery .= ' WHERE pro.status IN(1,4,6) AND teams.member = ' . $_SESSION['idSession'] . ' ORDER BY ' . $block1->sortingValue;
	    } else {
	        $tmpquery = 'WHERE pro.status IN(1,4,6) ORDER BY ' . $block1->sortingValue;
	    }
	} else if ($show == 'active') {
	    if ($projectsFilter == 'true' && !$isAdministrator) {
	        $tmpquery = 'LEFT OUTER JOIN ' . $tableCollab['teams'] . ' teams ON teams.project = pro.id ';
	        $tmpquery .= 'WHERE pro.status IN(0,2,3,5) AND teams.member = ' . $_SESSION['idSession'] . ' ORDER BY ' . $block1->sortingValue;
	    } else {
	        $tmpquery = 'WHERE pro.status IN(0,2,3,5) ORDER BY ' . $block1->sortingValue;
	    }
	} else if ($show == 'all') {
	    if ($projectsFilter == 'true' && !$isAdministrator) {
	        $tmpquery = 'LEFT OUTER JOIN ' . $tableCollab['teams'] . ' teams ON teams.project = pro.id ';
	        $tmpquery .= 'WHERE teams.member = ' . $_SESSION['idSession'] . ' ORDER BY ' . $block1->sortingValue;
	    } else {
	        $tmpquery = 'ORDER BY ' . $block1->sortingValue;
	    }
	}

	$listProjects = new request();
	if ($isAdministrator) {
	    // The old request mapper depends on the physical column order of
	    // projects (SELECT pro.*).  Upgraded/older databases can have a
	    // different order, which made existing rows appear to be missing.
	    $projectTable = str_replace('`', '``', $tableCollab['projects']);
	    $organizationTable = str_replace('`', '``', $tableCollab['organizations']);
	    $memberTable = str_replace('`', '``', $tableCollab['members']);
	    $projectConnection = openDatabase();

	    $projectCountResult = mysqli_query($projectConnection, "SELECT COUNT(*) AS row_count FROM `$projectTable`");
	    $projectCountRow = $projectCountResult instanceof mysqli_result
	        ? mysqli_fetch_assoc($projectCountResult)
	        : array();
	    $block1->recordsTotal = (int) ($projectCountRow['row_count'] ?? 0);
	    if ($projectCountResult instanceof mysqli_result) {
	        mysqli_free_result($projectCountResult);
	    }
	    // The administrator's "All" view is intentionally unfiltered and
	    // should not hide records on another page.
	    $block1->rowsLimit = max(1, $block1->recordsTotal);

	    $projectSql = "SELECT
	        pro.id AS pro_id, pro.organization AS pro_organization,
	        pro.owner AS pro_owner, pro.priority AS pro_priority,
	        pro.status AS pro_status, pro.name AS pro_name,
	        pro.description AS pro_description, pro.url_dev AS pro_url_dev,
	        pro.url_prod AS pro_url_prod, pro.created AS pro_created,
	        pro.modified AS pro_modified, pro.published AS pro_published,
	        pro.upload_max AS pro_upload_max, pro.phase_set AS pro_phase_set,
	        pro.type AS pro_type, org.id AS org_id, org.name AS org_name,
	        mem.id AS mem_id, mem.login AS mem_login, mem.name AS mem_name,
	        mem.email_work AS mem_email_work
	      FROM `$projectTable` pro
	      LEFT JOIN `$organizationTable` org ON org.id = pro.organization
	      LEFT JOIN `$memberTable` mem ON mem.id = pro.owner
	      ORDER BY pro.name ASC";
	    $projectResult = mysqli_query($projectConnection, $projectSql);
	    while ($projectResult instanceof mysqli_result && ($projectRow = mysqli_fetch_assoc($projectResult))) {
	        foreach (array(
	            'pro_id', 'pro_organization', 'pro_owner', 'pro_priority',
	            'pro_status', 'pro_name', 'pro_description', 'pro_url_dev',
	            'pro_url_prod', 'pro_created', 'pro_modified', 'pro_published',
	            'pro_upload_max', 'pro_phase_set', 'pro_type'
	        ) as $field) {
	            $listProjects->{$field}[] = $projectRow[$field];
	        }
	        $listProjects->pro_org_id[] = $projectRow['org_id'];
	        $listProjects->pro_org_name[] = $projectRow['org_name'];
	        $listProjects->pro_mem_id[] = $projectRow['mem_id'];
	        $listProjects->pro_mem_login[] = $projectRow['mem_login'];
	        $listProjects->pro_mem_name[] = $projectRow['mem_name'];
	        $listProjects->pro_mem_email_work[] = $projectRow['mem_email_work'];
	    }
	    if ($projectResult instanceof mysqli_result) {
	        mysqli_free_result($projectResult);
	    }
	    mysqli_close($projectConnection);
	} else {
	    $block1->recordsTotal = compt($initrequest['projects'] . ' ' . $tmpquery);
	    $listProjects->openProjects($tmpquery, $block1->borne, $block1->rowsLimit);
	}
	$comptListProjects = count($listProjects->pro_id);

	if ($comptListProjects != 0) {
	    $block1->openResults();
	    
	    $block1->labels(
	       $labels = array(
	               #$strings['id'], 
	               #$strings['priority'], 
	               "P",
	               $strings['project'], 
	               $strings['organization'], 
	               $strings['status'], 
	               $strings['owner'], 
	               $strings['project_site']
	           ), 
	           'true'
	       );

	    for ($i = 0; $i < $comptListProjects; $i++) {
	        if ($listProjects->pro_org_id[$i] == 1) {
	            $listProjects->pro_org_name[$i] = $strings['none'];
	        }

	        $idStatus = $listProjects->pro_status[$i];
	        $idPriority = $listProjects->pro_priority[$i];

	        $block1->openRow($listProjects->pro_id[$i]);
	        $block1->checkboxRow($listProjects->pro_id[$i]);

	        //--- id ---
	        //$block1->cellRow(buildLink('../projects/viewproject.php?id=' . $listProjects->pro_id[$i], $listProjects->pro_id[$i], LINK_INSIDE));
	        
	        //--- prio ----
	        $block1->cellRow(
	        	'<img src="../themes/'. THEME . '/gfx_priority/' . $idPriority . '.gif" title="' . $priority[$idPriority] . '">',
	        	#"&nbsp;".$priority[$idPriority]
	        	"1", true);
	        	
	        //--- name  ---
	        $block1->cellRow(buildLink(
	            '../projects/viewproject.php?id=' . $listProjects->pro_id[$i], $listProjects->pro_name[$i], LINK_INSIDE),
	            "30"
	        );
	        
	        //--- client ----
	        $block1->cellRow($listProjects->pro_org_name[$i]);

	        //--- status ---
	        $block1->cellRow('<img src="../themes/' . THEME . '/gfx_status/' . $idStatus . '.gif" alt="' . $status[$idStatus] . '">&nbsp;' . $status[$idStatus], '', true);

	        //--- owner ----
	        $block1->cellRow(buildLink($listProjects->pro_mem_email_work[$i], $listProjects->pro_mem_login[$i], LINK_MAIL),false,true);

	        //--- project-site ------
	        if ($sitePublish == 'true') {
	            if ($listProjects->pro_published[$i] == "1") {
	                $block1->cellRow('&lt;' . buildLink('../projects/addprojectsite.php?id=' . $listProjects->pro_id[$i], $strings['create'] . '...', LINK_INSIDE) . '&gt;', "8");
	            } else {
	                $block1->cellRow('&lt;' . buildLink('../projects/viewprojectsite.php?id=' . $listProjects->pro_id[$i], $strings['details'], LINK_INSIDE) . '&gt;', "8");
	            }
	        }

	        $block1->closeRow();
	    }

	    $block1->closeResults();
	    $block1->bornesFooter(1, $blockPage->bornesNumber, '', 'show=' . $show);
		} else {
		    $block1->noresults();
		}

	$block1->closeFormResults();
	$block1->headingForm_close();	//added


	$block1->openPaletteScript();

	if ($_SESSION['profilSession'] == 0 || $_SESSION['profilSession'] == 1 || $_SESSION['profilSession'] == 5) {
	    $block1->paletteScript(0, 'add', '../projects/editproject.php', 'true,false,false', $strings['add']);
	    $block1->paletteScript(1, 'remove', '../projects/deleteproject.php', 'false,true,false', $strings['delete']);
	}

	$block1->paletteScript(2, 'info', '../projects/viewproject.php', 'false,true,false', $strings['view']);

	if ($_SESSION['profilSession'] == 0 || $_SESSION['profilSession'] == 1 || $_SESSION['profilSession'] == 5) {
	    $block1->paletteScript(3, 'edit', '../projects/editproject.php', 'false,true,false', $strings['edit']);
	    $block1->paletteScript(4, 'copy', '../projects/editproject.php?cpy=true', 'false,true,false', $strings['copy']);
	}

	if ($enable_cvs == 'true') {
	    $block1->paletteScript(7, 'cvs', '../browsecvs/browsecvs.php', 'false,true,false', $strings['browse_cvs']);
	}

	// this will be replced with the internal bug module in the future
	if ($enableMantis == 'true') {
	    $block1->paletteScript(8, 'bug', $pathMantis . "login.php?url=http://{$HTTP_HOST}{$REQUEST_URI}&f_username=" . $_SESSION['loginSession'] . "&f_password=" . $_SESSION['passwordSession'], 'false,true,false', $strings['bug']);
	}

	$block1->closePaletteScript($comptListProjects, $listProjects->pro_id);
}


require_once('../themes/' . THEME . '/footer.php');

?>
