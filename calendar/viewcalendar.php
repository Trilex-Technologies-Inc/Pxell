<?php // $Revision: 1.11 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: viewcalendar.php,v 1.11 2005/05/18 03:47:13 vjack Exp $
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

function _dayOfWeek($timestamp)
{
    return intval(date("w", $timestamp) + 1);
}

$year = date("Y");
$month = date("n");
$day = date("j");
if (strlen($month) == 1) {
    $month = "0$month";
}
if (strlen($day) == 1) {
    $day = "0$day";
}
$dateToday = "$year-$month-$day";

$tmpquery = " WHERE id='" . $_SESSION['idSession'] . "'";
$sortingDetail = new request();
$sortingDetail->openSorting($tmpquery);
if ($sortingDetail->sor_calendar_view[0] == NULL) {
    $lastDateCalend = $dateToday;
    $lastViewCalend = "0";
} else {
    $lastDateCalend = substr($sortingDetail->sor_calendar_view[0], 0, 10);
    $lastViewCalend = trim(substr($sortingDetail->sor_calendar_view[0], 10));
}

if ($type == "") {
    $type = "monthPreview";
    if ($sortingDetail->sor_calendar_view[0] == NULL) {
        $viewCalend = "0";
    } else {
        $viewCalend = $lastViewCalend;
    }
} else if ($type == "monthPreview" && $viewCalend == "") {
    $viewCalend = $S_VIEW;
}

if ($dateCalend == "") {
    if ($sortingDetail->sor_calendar_view[0] == NULL) {
        $dateCalend = $dateToday;
    } else {
        $dateCalend = $lastDateCalend;
    }
}
$year = substr("$dateCalend", 0, 4);
$month = substr("$dateCalend", 5, 2);
$day = substr("$dateCalend", 8, 2);

if ($type == "monthPreview" && ($lastDateCalend != $dateCalend || $lastViewCalend != $viewCalend)) {
    $newCalendarView = $dateCalend . $viewCalend;
    $tmpquery = "UPDATE " . $tableCollab["sorting"] . " set calendar_view='$newCalendarView' WHERE id='" . $_SESSION['idSession'] . "'";
    connectSql("$tmpquery") ;
}

if ($viewCalend != 0) {
    $tmpquery = " WHERE tea.project='$viewCalend'";
    $listTeam = new request();
    $listTeam->openTeams($tmpquery);
}

$yearDay = date("Y");
$monthDay = date("n");
$dayDay = date("d");

$dayName = date("w", mktime(0, 0, 0, $month, $day, $year));
$monthName = date("n", mktime(0, 0, 0, $month, $day, $year));
$dayName = $dayNameArray[$dayName];
$monthName = $monthNameArray[$monthName];

$daysmonth = date("t", mktime(0, 0, 0, $month, $day, $year));
$firstday = date("w", mktime(0, 0, 0, $month, 1, $year));
$padmonth = date("m", mktime(0, 0, 0, $month, $day, $year));
$padday = date("d", mktime(0, 0, 0, $month, $day, $year));

if ($firstday == 0) {
    $firstday = 7;
}

if ($type == "calendEdit") {
    if ($action == "update" && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if ($recurring == "") {
            $recurring = "0";
        } else {
            $dateStart_A = substr("$dateStart", 0, 4);
            $dateStart_M = substr("$dateStart", 5, 2);
            $dateStart_J = substr("$dateStart", 8, 2);
            $dayRecurr = _dayOfWeek(mktime(12, 12, 12, $dateStart_M, $dateStart_J, $dateStart_A));
        }
        $subject = convertData($subject);
        $description = convertData($description);
        $tmpquery = "UPDATE " . $tableCollab["calendar"] . " SET subject='$subject',description='$description',shortname='$shortname',date_start='$dateStart',date_end='$dateEnd',time_start='$time_start',time_end='$time_end',reminder='$reminder',recurring='$recurring',recur_day='$dayRecurr' WHERE id = '$dateEnreg'";
        connectSql("$tmpquery");
        header("Location: ../calendar/viewcalendar.php?viewCalend=$viewCalend&dateEnreg=$dateEnreg&dateCalend=$dateCalend&type=calendDetail&msg=update");
        exit;
    }
    if ($action == "add" && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if ($shortname == "") {
            $error = $strings["blank_fields"];
        } else {
            if ($recurring == "") {
                $recurring = "0";
            } else {
                $dateStart_A = substr("$dateStart", 0, 4);
                $dateStart_M = substr("$dateStart", 5, 2);
                $dateStart_J = substr("$dateStart", 8, 2);
                $dayRecurr = _dayOfWeek(mktime(12, 12, 12, $dateStart_M, $dateStart_J, $dateStart_A));
            }
            $subject = convertData($subject);
            $description = convertData($description);
            $shortname = convertData($shortname);
            if ($viewCalend == 0) {
                $tmpquery = "INSERT INTO " . $tableCollab["calendar"] . "(owner,subject,description,shortname,date_start,date_end,time_start,time_end,reminder,recurring,recur_day) VALUES('" . $_SESSION['idSession'] . "','$subject','$description','$shortname','$dateStart','$dateEnd','$time_start','$time_end','$reminder','$recurring','$dayRecurr')";
            } else {
                $tmpquery = "INSERT INTO " . $tableCollab["calendar"] . "(project,subject,description,shortname,date_start,date_end,time_start,time_end,reminder,recurring,recur_day) VALUES('$viewCalend','$subject','$description','$shortname','$dateStart','$dateEnd','$time_start','$time_end','$reminder','$recurring','$dayRecurr')";
            }
            connectSql("$tmpquery");
            $tmpquery = $tableCollab["calendar"];
            last_id($tmpquery);
            $num = $lastId[0];
            unset($lastId);
            header("Location: ../calendar/viewcalendar.php?viewCalend=$viewCalend&dateEnreg=$num&dateCalend=$dateCalend&type=calendDetail&msg=add&");
            exit;
        }
    }
}

if ($type == "calendEdit") {
    if ($dateEnreg == "" && $id != "") {
        $dateEnreg = $id;
    }
    if ($id != "") {
        if ($viewCalend == 0) {
            $tmpquery = "WHERE cal.owner = '" . $_SESSION['idSession'] . "' AND cal.id = '$dateEnreg'";
        } else {
            $tmpquery = "WHERE cal.project = '$viewCalend' AND cal.id = '$dateEnreg'";
        }
        $detailCalendar = new request();
        $detailCalendar->openCalendar($tmpquery);
        $comptDetailCalendar = count($detailCalendar->cal_id);
        if ($comptDetailCalendar == "0") {
            header('Location: ../calendar/viewcalendar.php');
            exit;
        }
    }
}

if ($type == "calendDetail") {
    if ($dateEnreg == "" && $id != "") {
        $dateEnreg = $id;
    }
    if ($viewCalend == 0) {
        $tmpquery = "WHERE cal.owner = '" . $_SESSION['idSession'] . "' AND cal.id = '$dateEnreg'";
    } else {
        $tmpquery = "WHERE cal.project = '$viewCalend' AND cal.id = '$dateEnreg'";
    }
    $detailCalendar = new request();
    $detailCalendar->openCalendar($tmpquery);
    $comptDetailCalendar = count($detailCalendar->cal_id);
    if ($comptDetailCalendar == "0") {
        header("Location: ../calendar/viewcalendar.php");
        exit;
    }
}

if ($type == "calendEdit") {
    $bodyCommand = "onLoad=\"document.calendForm.subject.focus();\"";
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?type=monthPreview", $strings["calendar"], LINK_INSIDE);
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?viewCalend=$viewCalend&type=monthPreview&amp;dateCalend=$dateCalend", "$monthName $year", LINK_INSIDE);
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?viewCalend=$viewCalend&type=dayList&amp;dateCalend=$dateCalend", "$dayName $day $monthName $year", LINK_INSIDE);
}


if ($type == "calendEdit") {
    if ($id != "") {
        $subject = $detailCalendar->cal_subject[0];
        $description = $detailCalendar->cal_description[0];
        $shortname = $detailCalendar->cal_shortname[0];
        $date_start = $detailCalendar->cal_date_start[0];
        $date_end = $detailCalendar->cal_date_end[0];
        $time_start = $detailCalendar->cal_time_start[0];
        $time_end = $detailCalendar->cal_time_end[0];
        $reminder = $detailCalendar->cal_reminder[0];
        $recurring = $detailCalendar->cal_recurring[0];
        if ($recurring == "1") {
            $checked2_a = "checked"; //true
        }
        if ($reminder == "0") {
            $checked1_b = "checked"; //false
        } else {
            $checked2_b = "checked"; //true
        }
    } else {
        $checked2_b = "checked"; //true
    }

    //--- header -----
    if ($id != "") {
        $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?viewCalend=$viewCalend&type=calendDetail&amp;dateCalend=$dateCalend&amp;dateEnreg=$dateEnreg", $detailCalendar->cal_shortname[0], LINK_INSIDE);
        $breadcrumbs[]=$strings["edit"];
    } else {
        $breadcrumbs[]=$strings["add"];
    }
    $pageSection='calendar';
    require_once("../themes/" . THEME . "/header.php");



    $block1 = new block();

    $block1->form = "calend";
    if ($id != "") {
        $block1->openForm("../calendar/viewcalendar.php?viewCalend=$viewCalend&dateEnreg=$dateEnreg&amp;dateCalend=$dateCalend&amp;type=$type&amp;action=update#" . $block1->form . "Anchor");
    } else {
        $block1->openForm("../calendar/viewcalendar.php?viewCalend=$viewCalend&dateEnreg=$dateEnreg&amp;dateCalend=$dateCalend&amp;type=$type&amp;action=add#" . $block1->form . "Anchor");
    }

    if ($error != "") {
        $block1->headingError($strings["errors"]);
        $block1->contentError($error);
    }

    if ($id != "") {
        $block1->headingForm($strings["edit"] . ": " . $detailCalendar->cal_shortname[0]);
    }
    else {
        $block1->headingForm($strings["add"] . ":");
    }

    $block1->openContent();
    $block1->contentTitle($strings["details"]);

    if ($viewCalend != 0) {
        echo "<tr class=\"odd\"><td valign=\"top\" class=\"leftvalue\">" . $strings['project'] . " :</td><td>" . $listTeam->tea_pro_name[0] . "</td></tr>";
    }

    // ---------- SUBJECT ----------
    echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["subject"] . ' :</label>
        <input type="text" name="subject" maxlength="128" value="' . htmlspecialchars($subject) . '" class="form-control" >
      </div>';

// ---------- DESCRIPTION ----------
    echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["description"] . ' :</label>
        <textarea name="description" rows="2" class="form-control" style="max-width: 400px; height: 50px;">' . htmlspecialchars($description) . '</textarea>
      </div>';

// ---------- SHORTNAME ----------
    echo '<div class="mb-3">
        <label class="form-label fw-bold">
            * ' . $strings["shortname"] . $template->printHelp("calendar_shortname") . ' :
        </label>
        <input type="text" name="shortname" maxlength="128" value="' . htmlspecialchars($shortname) . '" class="form-control" >
      </div>';

// ---------- DATE START ----------
    if ($date_start == "") $date_start = $dateCalend;
    echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["date_start"] . ' :</label>
        <div class="input-group" >
            <input type="date" name="dateStart" id="sel11" value="' . htmlspecialchars($date_start) . '" class="form-control">
         
        </div>
        
      </div>';

// ---------- DATE END ----------
    if ($date_end == "") $date_end = $dateCalend;
    echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["date_end"] . ' :</label>
        <div class="input-group" >
            <input type="date" name="dateEnd" id="sel31" value="' . htmlspecialchars($date_end) . '" class="form-control">
           
        </div>
       
      </div>';

// ---------- TIME START ----------
    echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["time_start"] . ' :</label>
        <input type="time" name="time_start" maxlength="128" value="' . htmlspecialchars($time_start) . '" class="form-control" >
      </div>';

// ---------- TIME END ----------
    echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["time_end"] . ' :</label>
        <input type="time" name="time_end" maxlength="128" value="' . htmlspecialchars($time_end) . '" class="form-control" >
      </div>';

// ---------- REMINDER RADIO ----------
    echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["calendar_reminder"] . ' :</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="reminder" value="0" ' . $checked1_b . '>
            <label class="form-check-label">' . $strings["no"] . '</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="reminder" value="1" ' . $checked2_b . '>
            <label class="form-check-label">' . $strings["yes"] . '</label>
        </div>
      </div>';

// ---------- RECURRING CHECKBOX ----------
    echo '<div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="recurring" value="1" ' . $checked2_a . '>
            <label class="form-check-label">' . $strings["calendar_recurring"] . '</label>
        </div>
      </div>';


    echo '<div class="mb-3">
        <input type="submit" class="btn btn-primary" value="'.$strings["save"].'" >
      </div>';

    $block1->closeContent();
    $block1->headingForm_close();
    $block1->closeForm();
}




// calendDetail
if ($type == "calendDetail") {
    $reminder = $detailCalendar->cal_reminder[0];
    $recurring = $detailCalendar->cal_recurring[0];
    if ($reminder == "0") {
        $reminder = $strings["no"];
    } else {
        $reminder = $strings["yes"];
    }
    if ($recurring == "0") {
        $recurring = $strings["no"];
    } else {
        $recurring = $strings["yes"];
    }


    //--- header ----
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?type=monthPreview", $strings["calendar"], LINK_INSIDE);
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?viewCalend=$viewCalend&type=monthPreview&amp;dateCalend=$dateCalend", "$monthName $year", LINK_INSIDE);
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?viewCalend=$viewCalend&type=dayList&amp;dateCalend=$dateCalend", "$dayName $day $monthName $year", LINK_INSIDE);
    $breadcrumbs[]=$detailCalendar->cal_shortname[0];

    $pageSection='calendar';
    require_once("../themes/" . THEME . "/header.php");

    //--- content -----
    $eventSubject = htmlspecialchars($detailCalendar->cal_subject[0], ENT_QUOTES, 'UTF-8');
    $eventShortName = htmlspecialchars($detailCalendar->cal_shortname[0], ENT_QUOTES, 'UTF-8');
    $eventDescription = nl2br(htmlspecialchars($detailCalendar->cal_description[0], ENT_QUOTES, 'UTF-8'));
    $eventStartDate = htmlspecialchars($detailCalendar->cal_date_start[0], ENT_QUOTES, 'UTF-8');
    $eventEndDate = htmlspecialchars($detailCalendar->cal_date_end[0], ENT_QUOTES, 'UTF-8');
    $eventStartTime = htmlspecialchars($detailCalendar->cal_time_start[0], ENT_QUOTES, 'UTF-8');
    $eventEndTime = htmlspecialchars($detailCalendar->cal_time_end[0], ENT_QUOTES, 'UTF-8');
    $eventTimestamp = strtotime($detailCalendar->cal_date_start[0]);
    $eventDay = $eventTimestamp ? date('d', $eventTimestamp) : '&ndash;';
    $eventMonth = $eventTimestamp ? date('M', $eventTimestamp) : '';
    $calendarLabel = $viewCalend != 0
        ? htmlspecialchars($listTeam->tea_pro_name[0], ENT_QUOTES, 'UTF-8')
        : htmlspecialchars($strings['cal_personal'] . $strings['calendar'], ENT_QUOTES, 'UTF-8');
?>
    <style>
        .event-detail-page { max-width: 1100px; margin: 0 auto 2rem; }
        .event-detail-hero {
            display: flex; justify-content: space-between; gap: 1.5rem;
            padding: 1.75rem; color: #fff; border-radius: 1rem 1rem 0 0;
            background: linear-gradient(135deg, #2457a7 0%, #3478d4 58%, #4c91e8 100%);
        }
        .event-detail-heading { display: flex; gap: 1rem; align-items: center; min-width: 0; }
        .event-date-tile {
            width: 72px; min-width: 72px; overflow: hidden; text-align: center;
            border-radius: .8rem; color: #17365f; background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .16);
        }
        .event-date-tile__month { padding: .25rem; color: #fff; background: #dc3545; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .event-date-tile__day { padding: .35rem; font-size: 1.8rem; font-weight: 750; line-height: 1.2; }
        .event-detail-kicker { margin-bottom: .3rem; opacity: .78; font-size: .78rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .event-detail-title { margin: 0; font-size: clamp(1.35rem, 3vw, 2rem); overflow-wrap: anywhere; }
        .event-detail-subject { margin: .4rem 0 0; opacity: .86; }
        .event-detail-actions { display: flex; flex-wrap: wrap; align-content: flex-start; justify-content: flex-end; gap: .5rem; }
        .event-detail-actions .btn { white-space: nowrap; }
        .event-detail-body { padding: 1.75rem; border: 1px solid #dfe7f1; border-top: 0; border-radius: 0 0 1rem 1rem; background: #fff; box-shadow: 0 14px 36px rgba(34, 49, 72, .09); }
        .event-schedule { display: grid; grid-template-columns: 1fr auto 1fr; gap: 1rem; align-items: center; padding: 1.25rem; border-radius: .85rem; background: #f5f8fc; }
        .event-schedule__item { min-width: 0; }
        .event-schedule__label { margin-bottom: .3rem; color: #6c7887; font-size: .75rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
        .event-schedule__date { color: #172b4d; font-size: 1.05rem; font-weight: 700; }
        .event-schedule__time { margin-top: .2rem; color: #526174; }
        .event-schedule__arrow { color: #8ca0b8; font-size: 1.2rem; }
        .event-info-card { height: 100%; padding: 1.2rem; border: 1px solid #e2e8f0; border-radius: .85rem; }
        .event-info-label { margin-bottom: .35rem; color: #6c7887; font-size: .78rem; font-weight: 700; text-transform: uppercase; }
        .event-description { color: #34445a; line-height: 1.7; overflow-wrap: anywhere; }
        @media (max-width: 767px) {
            .event-detail-hero { flex-direction: column; padding: 1.25rem; }
            .event-detail-actions { justify-content: flex-start; }
            .event-detail-body { padding: 1.1rem; }
            .event-schedule { grid-template-columns: 1fr; }
            .event-schedule__arrow { transform: rotate(90deg); justify-self: center; }
        }
    </style>

    <div class="event-detail-page" id="calendAnchor">
        <?php if ($error != ""): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <section class="event-detail-hero">
            <div class="event-detail-heading">
                <div class="event-date-tile" aria-hidden="true">
                    <div class="event-date-tile__month"><?php echo $eventMonth; ?></div>
                    <div class="event-date-tile__day"><?php echo $eventDay; ?></div>
                </div>
                <div>
                    <div class="event-detail-kicker"><i class="fa-regular fa-calendar me-1"></i><?php echo $calendarLabel; ?></div>
                    <h1 class="event-detail-title"><?php echo $eventShortName; ?></h1>
                    <?php if ($eventSubject !== ''): ?><p class="event-detail-subject"><?php echo $eventSubject; ?></p><?php endif; ?>
                </div>
            </div>
            <div class="event-detail-actions">
                <a class="btn btn-light" href="../calendar/viewcalendar.php?viewCalend=<?php echo (int) $viewCalend; ?>&amp;id=<?php echo (int) $dateEnreg; ?>&amp;type=calendEdit&amp;dateCalend=<?php echo urlencode($dateCalend); ?>"><i class="fa-solid fa-pen me-1"></i><?php echo $strings["edit"]; ?></a>
                <a class="btn btn-outline-light" href="../calendar/exportcalendar.php?id=<?php echo (int) $dateEnreg; ?>"><i class="fa-solid fa-download me-1"></i><?php echo $strings["export"]; ?></a>
                <a class="btn btn-outline-light" href="../calendar/deletecalendar.php?id=<?php echo (int) $dateEnreg; ?>"><i class="fa-regular fa-trash-can me-1"></i><?php echo $strings["delete"]; ?></a>
            </div>
        </section>

        <section class="event-detail-body">
            <div class="event-schedule mb-4">
                <div class="event-schedule__item">
                    <div class="event-schedule__label"><?php echo $strings["date_start"]; ?></div>
                    <div class="event-schedule__date"><?php echo $eventStartDate; ?></div>
                    <div class="event-schedule__time"><i class="fa-regular fa-clock me-1"></i><?php echo $eventStartTime ?: '&ndash;'; ?></div>
                </div>
                <i class="fa-solid fa-arrow-right event-schedule__arrow" aria-hidden="true"></i>
                <div class="event-schedule__item">
                    <div class="event-schedule__label"><?php echo $strings["date_end"]; ?></div>
                    <div class="event-schedule__date"><?php echo $eventEndDate; ?></div>
                    <div class="event-schedule__time"><i class="fa-regular fa-clock me-1"></i><?php echo $eventEndTime ?: '&ndash;'; ?></div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="event-info-card">
                        <div class="event-info-label"><?php echo $strings["description"]; ?></div>
                        <div class="event-description"><?php echo $eventDescription !== '' ? $eventDescription : '&ndash;'; ?></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="event-info-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted"><i class="fa-regular fa-bell me-2"></i><?php echo $strings["calendar_reminder"]; ?></span>
                            <span class="badge <?php echo $detailCalendar->cal_reminder[0] == '0' ? 'text-bg-secondary' : 'text-bg-primary'; ?>"><?php echo $reminder; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fa-solid fa-rotate me-2"></i><?php echo $strings["calendar_recurring"]; ?></span>
                            <span class="badge <?php echo $detailCalendar->cal_recurring[0] == '0' ? 'text-bg-secondary' : 'text-bg-primary'; ?>"><?php echo $recurring; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php
}
else if ($type == "dayList") {

    //--- header----
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?type=monthPreview", $strings["calendar"], LINK_INSIDE);
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?viewCalend=$viewCalend&type=monthPreview&amp;dateCalend=$dateCalend", "$monthName $year", LINK_INSIDE);
    $breadcrumbs[]="$dayName $day $monthName $year";
    $pageSection='calendar';
    require_once("../themes/" . THEME . "/header.php");


    //--- content--------
    $block1 = new block();

    $block1->form = "calendList";
    $block1->openForm("../calendar/viewcalendar.php?viewCalend=$viewCalend&type=$type&amp;dateCalend=$dateCalend#" . $block1->form . "Anchor");

    if ($viewCalend == 0) {
        $heading_posfix = "(" . $strings['cal_personal'] . $strings['calendar'] . ")";
    } else {
        $heading_posfix = "(" . $strings['project'] . $strings['calendar'] . "-" . $listTeam->tea_pro_name[0] . ")";
    }
    $block1->heading("$dayName $day $monthName $year" . $heading_posfix);

    $block1->openPaletteIcon();

    $block1->paletteIcon(0, "add", $strings["add"]);
    $block1->paletteIcon(1, "remove", $strings["delete"]);
    $block1->paletteIcon(2, "info", $strings["view"]);
    $block1->paletteIcon(3, "edit", $strings["edit"]);

    $block1->closePaletteIcon();

    $block1->sorting("calendar", $sortingUser->sor_calendar[0], "cal.date_end DESC", $sortingFields = array(0 => "cal.shortname", 1 => "cal.subject", 2 => "cal.date_start", 3 => "cal.date_end"));

    $dayRecurr = _dayOfWeek(mktime(12, 12, 12, $month, $day, $year));

    if ($viewCalend == 0) {
        $tmpquery = "WHERE cal.owner = '" . $_SESSION['idSession'] . "' AND ((cal.date_start <= '$dateCalend' AND cal.date_end >= '$dateCalend' AND cal.recurring = '0') OR ((cal.date_start <= '$dateCalend' AND cal.date_end <= '$dateCalend') AND cal.recurring = '1' AND cal.recur_day = '$dayRecurr')) ORDER BY cal.shortname";
    } else {
        $tmpquery = "WHERE cal.project = '$viewCalend' AND ((cal.date_start <= '$dateCalend' AND cal.date_end >= '$dateCalend' AND cal.recurring = '0') OR ((cal.date_start <= '$dateCalend' AND cal.date_end <= '$dateCalend') AND cal.recurring = '1' AND cal.recur_day = '$dayRecurr')) ORDER BY cal.shortname";
    }

    // $tmpquery = "WHERE cal.owner = '" . $_SESSION['idSession'] . "' AND cal.date_start <= '$dateCalend' AND cal.date_end >= '$dateCalend' ORDER BY $block1->sortingValue";
    $listCalendar = new request();
    $listCalendar->openCalendar($tmpquery);
    $comptListCalendar = count($listCalendar->cal_id);

    if ($comptListCalendar != "0") {
        $block1->openResults();

        $block1->labels($labels = array(0 => $strings["shortname"], 1 => $strings["subject"], 2 => $strings["date_start"], 3 => $strings["date_end"]), "false");

        for ($i = 0;$i < $comptListCalendar;$i++) {
            $block1->openRow($listCalendar->cal_id[$i]);
            $block1->checkboxRow($listCalendar->cal_id[$i]);
            $block1->cellRow(buildLink("../calendar/viewcalendar.php?$dateEnreg=" . $listCalendar->cal_id[$i] . "&amp;viewCalend=$viewCalend&amp;type=calendDetail&amp;dateCalend=$dateCalend", $listCalendar->cal_shortname[$i], LINK_INSIDE));
            $block1->cellRow($listCalendar->cal_subject[$i]);
            $block1->cellRow($listCalendar->cal_date_start[$i]);
            $block1->cellRow($listCalendar->cal_date_end[$i]);
            $block1->closeRow();
        }
        $block1->closeResults();
    } else {
        $block1->noresults();
    }
    $block1->closeFormResults();

    $block1->openPaletteScript();
    $block1->paletteScript(0, "add", "../calendar/viewcalendar.php?viewCalend=$viewCalend&dateCalend=$dateCalend&type=calendEdit", "true,false,false", $strings["add"]);
    $block1->paletteScript(1, "remove", "../calendar/deletecalendar.php?", "false,true,true", $strings["delete"]);
    $block1->paletteScript(2, "info", "../calendar/viewcalendar.php?viewCalend=$viewCalend&dateCalend=$dateCalend&type=calendDetail", "false,true,false", $strings["view"]);
    $block1->paletteScript(3, "edit", "../calendar/viewcalendar.php?viewCalend=$viewCalend&dateCalend=$dateCalend&type=calendEdit", "false,true,false", $strings["edit"]);
    $block1->closePaletteScript($comptListCalendar, $listCalendar->cal_id);
}

else if ($type == "monthPreview") {

    //--- header ----
    $breadcrumbs[]=buildLink("../calendar/viewcalendar.php?", $strings["calendar"], LINK_INSIDE);
    $breadcrumbs[]="$monthName $year";
    $pageSection='calendar';
    require_once("../themes/" . THEME . "/header.php");

    $tmpquery = "WHERE tea.member = '" . $_SESSION['idSession'] . "' ORDER BY tea.project";
    $teamList = new request();
    $teamList->openTeams($tmpquery);
    $comptTeamList = count($teamList->tea_id);

    $calendarMonthValue = intval($month);
    $calendarYearValue = intval($year);
    $datePast = date("Y-m-d", mktime(0, 0, 0, $calendarMonthValue - 1, 1, $calendarYearValue));
    $dateNext = date("Y-m-d", mktime(0, 0, 0, $calendarMonthValue + 1, 1, $calendarYearValue));
    $calendarScope = $strings['cal_personal'] . " " . $strings['calendar'];
    if ($viewCalend != 0) {
        $calendarScope = $strings['project'] . " " . $strings['calendar'] . " - " . $listTeam->tea_pro_name[0];
    }
    ?>
    <style>
        .calendar-page {
            display: grid;
            gap: 22px;
        }

        .calendar-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 22px;
            align-items: center;
            background: #ffffff;
            border: 1px solid #d9e3ec;
            border-radius: 8px;
            box-shadow: 0 12px 36px rgba(34, 49, 72, 0.09);
            padding: 24px;
        }

        .calendar-hero__eyebrow {
            color: #2f6f6a;
            font-size: 0.78rem;
            font-weight: 750;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .calendar-hero h1 {
            color: #162033;
            font-size: 1.9rem;
            font-weight: 750;
            letter-spacing: 0;
            margin: 0 0 8px;
        }

        .calendar-hero p {
            color: #657487;
            margin: 0;
        }

        .calendar-hero__badge {
            width: 72px;
            height: 72px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #e8f4f4;
            color: #2f6f6a;
            font-size: 1.8rem;
        }

        .calendar-toolbar {
            display: grid;
            grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
            gap: 12px;
            align-items: end;
            background: #ffffff;
            border: 1px solid #d9e3ec;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(34, 49, 72, 0.06);
            padding: 12px;
        }

        .calendar-toolbar__field {
            min-width: 0;
        }

        .calendar-toolbar__field .form-label {
            color: #162033;
            font-size: 0.82rem;
            font-weight: 750;
            margin-bottom: 6px;
        }

        .calendar-toolbar__field .form-select {
            min-height: 38px;
            width: 100%;
        }

        .calendar-toolbar__nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .calendar-toolbar__nav .btn {
            min-height: 38px;
            min-width: 64px;
        }

        .calendar-month-card {
            background: #ffffff;
            border: 1px solid #d9e3ec;
            border-radius: 8px;
            box-shadow: 0 12px 36px rgba(34, 49, 72, 0.09);
            overflow: hidden;
        }

        .calendar-month-card__header {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #d9e3ec;
            padding: 16px 18px;
        }

        .calendar-month-card__header h2 {
            color: #162033;
            font-size: 1.05rem;
            font-weight: 750;
            letter-spacing: 0;
            margin: 0;
        }

        .calendar-month-table-wrap {
            overflow: auto;
            padding: 12px;
        }

        .calendar-month-grid {
            border: 0;
            border-collapse: separate;
            border-spacing: 8px;
            min-width: 860px;
            width: 100%;
        }

        .calendar-month-grid .calendDays {
            background: #f6f9fb;
            border: 1px solid #d9e3ec;
            border-radius: 8px;
            color: #526174;
            font-size: 0.78rem;
            font-weight: 750;
            letter-spacing: 0.04em;
            padding: 10px;
            text-transform: uppercase;
        }

        .calendar-day {
            background: #ffffff;
            border: 1px solid #d9e3ec;
            border-radius: 8px;
            height: 132px;
            padding: 8px;
            vertical-align: top;
        }

        .calendar-day--empty {
            background: #f6f9fb;
        }

        .calendar-day--today {
            background: #e8f4f4;
            border-color: #9fcfca;
        }

        .calendar-day__number {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 6px;
        }

        .calendar-day__number a {
            display: grid;
            place-items: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f6f9fb;
            color: #164773;
            font-weight: 750;
            text-decoration: none;
        }

        .calendar-day--today .calendar-day__number a {
            background: #2f6f6a;
            color: #ffffff;
        }

        .calendar-day a {
            color: #164773;
            font-weight: 650;
            text-decoration: none;
        }

        .calendar-day a:hover {
            text-decoration: underline;
        }

        .calendar-gantt {
            background: #ffffff;
            border: 1px solid #d9e3ec;
            border-radius: 8px;
            box-shadow: 0 12px 36px rgba(34, 49, 72, 0.09);
            padding: 16px;
        }

        @media (max-width: 720px) {
            .calendar-hero,
            .calendar-toolbar {
                grid-template-columns: 1fr;
            }

            .calendar-hero {
                padding: 20px;
            }

            .calendar-hero__badge {
                width: 56px;
                height: 56px;
                font-size: 1.35rem;
            }

            .calendar-toolbar__nav {
                justify-content: flex-start;
            }

            .calendar-toolbar__nav .btn {
                flex: 1 1 96px;
            }
        }
    </style>

    <div class="calendar-page">
        <section class="calendar-hero">
            <div>
                <div class="calendar-hero__eyebrow"><?php echo $strings["calendar"]; ?></div>
                <h1><?php echo "$monthName $year"; ?></h1>
                <p><?php echo htmlspecialchars($calendarScope); ?></p>
            </div>
            <div class="calendar-hero__badge" aria-hidden="true">
                <i class="fa fa-calendar-days"></i>
            </div>
        </section>

        <form method="POST" action="../calendar/viewcalendar.php?dateCalend=<?php echo htmlspecialchars($dateCalend); ?>&amp;type=<?php echo htmlspecialchars($type); ?>" name="caVForm" class="calendar-toolbar">
            <label class="calendar-toolbar__field">
                <span class="form-label d-block mb-1"><?php echo $strings["view"]; ?></span>
                <select name="S_VIEW" onchange="document.caVForm.submit()" class="form-select">
<?php
    echo "<option value=\"0\"";
    if ($viewCalend == 0) {
        echo " selected";
    }
    echo ">" . $strings['cal_personal'] . "</option>";

    for ($t = 0; $t < $comptTeamList; $t++) {
        echo "<option value=\"" . $teamList->tea_project[$t] . "\"";
        if ($viewCalend == $teamList->tea_project[$t]) {
            echo " selected";
        }
        echo ">" . $strings['project'] . ":" . $teamList->tea_pro_name[$t] . "</option>";
    }

    echo "</select>";
?>
            </label>
            <div class="calendar-toolbar__nav">
                <a class="btn btn-outline-primary" href="../calendar/viewcalendar.php?viewCalend=<?php echo $viewCalend; ?>&amp;dateCalend=<?php echo $datePast; ?>"><?php echo $strings["previous"]; ?></a>
                <a class="btn btn-primary" href="../calendar/viewcalendar.php?viewCalend=<?php echo $viewCalend; ?>&amp;dateCalend=<?php echo $dateToday; ?>"><?php echo $strings["today"]; ?></a>
                <a class="btn btn-outline-primary" href="../calendar/viewcalendar.php?viewCalend=<?php echo $viewCalend; ?>&amp;dateCalend=<?php echo $dateNext; ?>"><?php echo $strings["next"]; ?></a>
            </div>
        </form>
<?php
    //--- content -----
    $block2 = new block();

    echo '<section class="calendar-month-card">';
    echo '<div class="calendar-month-card__header">';
    echo '<h2>' . $monthName . ' ' . $year . '</h2>';
    echo '<a class="btn btn-primary" href="../calendar/viewcalendar.php?viewCalend=' . $viewCalend . '&amp;dateCalend=' . $dateCalend . '&amp;type=calendEdit"><i class="fa fa-plus me-1"></i>' . $strings["add"] . '</a>';
    echo '</div>';
    echo '<div class="calendar-month-table-wrap">';
    echo "<table class=\"calendar-month-grid\"><tr>";
    for($daynumber = 1; $daynumber < 8; $daynumber++) {
        echo "<td width=14% class=calendDays>&nbsp;$dayNameArray[$daynumber]</td>";
    }
    echo "</tr>";
    // Print the calendar
    echo "<tr>";

    if ($viewCalend == 0) {
        $tmpquery = "WHERE tas.assigned_to = '" . $_SESSION['idSession'] . "' ORDER BY tas.name";
    } else {
        $tmpquery = "WHERE tas.project = '$viewCalend' ORDER BY tas.name";
    }

    $listTasks = new request();
    $listTasks->openTasks($tmpquery);
    $comptListTasks = count($listTasks->tas_id);

    if ($viewCalend == 0) {
        $tmpquery = "WHERE att.member = '" . $_SESSION['idSession'] . "'";
        $listAttendants = new request();
        $listAttendants->openAttendants($tmpquery);
        $comptListAttendants = count($listAttendants->att_id);
        $meetingIdList = "";
        for ($m = 0; $m < $comptListAttendants; $m++) {
            if ($meetingIdList != "") {
                $meetingIdList .= ", ";
            }
            $meetingIdList .= $listAttendants->att_meeting[$m];
        }
        if ($meetingIdList == "") {
            $tmpquery = "WHERE mee.id = -1";
        } else {
            $tmpquery = "WHERE mee.id IN (" . $meetingIdList . ") ORDER BY mee.name";
        }
    } else {
        $tmpquery = "WHERE mee.project = '$viewCalend' ORDER BY mee.name";
    }

    $listMeetings = new request();
    $listMeetings->openMeetings($tmpquery);
    $comptListMeetings = count($listMeetings->mee_id);

    for ($g = 0;$g < $comptListTasks;$g++) {
        if (substr($listTasks->tas_start_date[$g], 0, 7) == substr($dateCalend, 0, 7)) {
            $gantt = "true";
        }
    }

    for ($i = 1; $i < $daysmonth + $firstday; $i++) {
        $a = $i - $firstday + 1;
        $day = $i - $firstday + 1;
        if (strlen($a) == 1) {
            $a = "0$a";
        }
        if (strlen($month) == 1) {
            $month = "0$month";
        }
        $dateLink = "$year-$month-$a";
        $todayClass = "";
        $dayRecurr = _dayOfWeek(mktime(12, 12, 12, $month, $a, $year));
        $comptListCalendarScan = "0";

        if ($viewCalend == 0) {
            $tmpquery = "WHERE cal.owner = '" . $_SESSION['idSession'] . "' AND ((cal.date_start <= '$dateLink' AND cal.date_end >= '$dateLink' AND cal.recurring = '0') OR ((cal.date_start <= '$dateLink' AND cal.date_end <= '$dateLink') AND cal.recurring = '1' AND cal.recur_day = '$dayRecurr')) ORDER BY cal.shortname";
        } else {
            $tmpquery = "WHERE cal.project = '$viewCalend' AND ((cal.date_start <= '$dateLink' AND cal.date_end >= '$dateLink' AND cal.recurring = '0') OR ((cal.date_start <= '$dateLink' AND cal.date_end <= '$dateLink') AND cal.recurring = '1' AND cal.recur_day = '$dayRecurr')) ORDER BY cal.shortname";
        }

        $listCalendarScan = new request();
        $listCalendarScan->openCalendar($tmpquery);
        $comptListCalendarScan = count($listCalendarScan->cal_id);

        if (($i < $firstday) || ($a == "00")) {
            echo "<td class=\"calendar-day calendar-day--empty\">&nbsp;</td>";
        } else {
            if ($dateLink == $dateToday) {
                $classCell = "calendar-day calendar-day--today";
            } else {
                $classCell = "calendar-day";
            }

            echo "<td class=\"$classCell\"><div class=\"calendar-day__number\">" . buildLink("../calendar/viewcalendar.php?viewCalend=$viewCalend&amp;dateCalend=$dateLink&amp;type=dayList", $day, LINK_INSIDE) . "</div>";
            if ($comptListCalendarScan != "0") {
                for ($h = 0;$h < $comptListCalendarScan;$h++) {
                    echo buildLink("../calendar/viewcalendar.php?viewCalend=$viewCalend&amp;dateEnreg=" . $listCalendarScan->cal_id[$h] . "&amp;type=calendDetail&amp;dateCalend=$dateLink", $listCalendarScan->cal_shortname[$h], LINK_INSIDE) . "<br>";
                }
            }
            if ($comptListMeetings != "0") {
                for ($h = 0;$h < $comptListMeetings;$h++) {
                    if ($listMeetings->mee_date[$h] == $dateLink) {
                        echo $strings["meeting"] . ": ";
                        echo buildLink("../meetings/viewmeeting.php?id=" . $listMeetings->mee_id[$h], $listMeetings->mee_name[$h], LINK_INSIDE) . "<br>";
                    }
                }
            }
            if ($comptListTasks != "0") {
                for ($h = 0;$h < $comptListTasks;$h++) {
                    if ($listTasks->tas_start_date[$h] == $dateLink && $listTasks->tas_start_date[$h] != $listTasks->tas_due_date[$h]) {
                        echo $strings["task"] . ": ";
                        echo buildLink("../tasks/viewtask.php?id=" . $listTasks->tas_id[$h], $listTasks->tas_name[$h], LINK_INSIDE) . " (" . $strings["start_date"] . ")<br>";
                    }

                    if ($listTasks->tas_due_date[$h] == $dateLink && $listTasks->tas_start_date[$h] != $listTasks->tas_due_date[$h]) {
                        echo $strings["task"] . ": ";
                        if ($listTasks->tas_due_date[$h] <= $date && $listTasks->tas_completion[$h] != "10") {
                            echo buildLink("../tasks/viewtask.php?id=" . $listTasks->tas_id[$h], "<b>" . $listTasks->tas_name[$h] . "</b>", LINK_INSIDE) . " (" . $strings["due_date"] . ")<br>";
                        } else {
                            echo buildLink("../tasks/viewtask.php?id=" . $listTasks->tas_id[$h], $listTasks->tas_name[$h], LINK_INSIDE) . " (" . $strings["due_date"] . ")<br>";
                        }
                    }

                    if ($listTasks->tas_start_date[$h] == $dateLink && $listTasks->tas_due_date[$h] == $dateLink) {
                        echo $strings["task"] . ": ";
                        if ($listTasks->tas_due_date[$h] <= $date && $listTasks->tas_completion[$h] != "10") {
                            echo buildLink("../tasks/viewtask.php?id=" . $listTasks->tas_id[$h], "<b>" . $listTasks->tas_name[$h] . "</b>", LINK_INSIDE) . "<br>";
                        } else {
                            echo buildLink("../tasks/viewtask.php?id=" . $listTasks->tas_id[$h], $listTasks->tas_name[$h], LINK_INSIDE) . "<br>";
                        }
                    }
                }
            }
            if ($comptListTasks == "0" || $comptListMeetings == "0" || $comptListCalendarScan == "0") {
                echo "<br>";
            }
            echo "</td>";
        }

        if (($i % 7) == 0) {
            echo "</tr><tr>\n";
        }
    }

    if (($i % 7) != 1) {
        echo "</tr><tr>\n";
    }

    echo "</table>";
    echo "</div>";
    echo "</section>";

    if ($month == 1) {
        $pyear = $year - 1;
        $pmonth = 12;
    } else {
        $pyear = $year;
        $pmonth = $month - 1;
    }

    if ($month == 12) {
        $nyear = $year + 1;
        $nmonth = 1;
    } else {
        $nyear = $year;
        $nmonth = $month + 1;
    }

    $year = date("Y");
    $month = date("n");
    $day = date("j");
    if (strlen($month) == 1) {
        $month = "0$month";
    }
    if (strlen($pmonth) == 1) {
        $pmonth = "0$pmonth";
    }
    if (strlen($nmonth) == 1) {
        $nmonth = "0$nmonth";
    }
    if (strlen($day) == 1) {
        $day = "0$day";
    }
    $datePast = "$pyear-$pmonth-01";
    $dateNext = "$nyear-$nmonth-01";

    $dateToday = "$year-$month-$day";
    if ($activeJpgraph == "true" && $gantt == "true") {
        echo '<section class="calendar-gantt">';
        // show the expanded or compact Gantt Chart
        if ($_GET['base'] == 1) {
            echo "<a href='viewcalendar.php?viewCalend=$viewCalend&amp;dateCalend=$dateCalend&amp;base=0'>expand</a><br>";
        } else {
            echo "<a href='viewcalendar.php?viewCalend=$viewCalend&amp;dateCalend=$dateCalend&amp;base=1'>compact</a><br>";
        }

        echo "<img src=\"graphtasks.php?viewCalend=$viewCalend&amp;dateCalend=$dateCalend&amp;base=" . $_GET['base'] . "\" alt=\"\"><br>
<span class=\"listEvenBold\">" . buildLink("http://www.aditus.nu/jpgraph/", "JpGraph", LINK_POWERED) . "</span>";
        echo '</section>';
    }
    echo '</div>';
}

require_once("../themes/" . THEME . "/footer.php");

?>
