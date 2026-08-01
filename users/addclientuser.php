<?php // $Revision: 1.5 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: addclientuser.php,v 1.5 2005/01/20 16:41:58 madbear Exp $
 */

$checkSession = true;
require_once("../includes/library.php");

// these user levels can't perform this action
if (
    ($_SESSION['profilSession'] == 4) ||
    ($_SESSION['profilSession'] == 3) ||
    ($_SESSION['profilSession'] == 2)
) {
    header("Location: ../general/home.php?msg=permissiondenied");
    exit;
}

$tmpquery = "WHERE org.id = '$organization'";
$clientDetail = new request();
$clientDetail->openOrganizations($tmpquery);

// case add client user
if ($action == "add" && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!preg_match('/^[A-Za-z0-9]+$/', $un)) {
        $error = $strings["alpha_only"];
    } else {
        $tmpquery = "WHERE mem.login = '$un'";
        $existsUser = new request();
        $existsUser->openMembers($tmpquery);

        if (count($existsUser->mem_id) != 0) {
            $error = $strings["user_already_exists"];
        } else {
            if ($pw != $pwa || $pw == "") {
                $error = $strings["new_password_error"];
            } else {
                $fn  = convertData($fn);
                $tit = convertData($tit);
                $c   = convertData($c);
                $pw  = get_password($pw);

                $tmpquery1 = "INSERT INTO " . $tableCollab["members"] . "
                (organization,login,name,title,email_work,phone_work,phone_home,mobile,fax,comments,password,profil,created,timezone)
                VALUES
                ('$clod','$un','$fn','$tit','$em','$wp','$hp','$mp','$fax','$c','$pw','3','$dateheure','0')";

                connectSql($tmpquery1);
                last_id($tableCollab["members"]);
                $num = $lastId[0];
                unset($lastId);

                $tmpquery3 = "INSERT INTO " . $tableCollab["notifications"] . "
                (member,taskAssignment,removeProjectTeam,addProjectTeam,newTopic,newPost,statusTaskChange,priorityTaskChange,duedateTaskChange,clientAddTask)
                VALUES ('$num','0','0','0','0','0','0','0','0','0')";
                connectSql($tmpquery3);

                header("Location: ../clients/viewclient.php?id=$clod&msg=add");
                exit;
            }
        }
    }
}

// Header
$breadcrumbs[] = buildLink("../clients/listclients.php?", $strings["clients"], LINK_INSIDE);
$breadcrumbs[] = buildLink("../clients/viewclient.php?id=" . $clientDetail->org_id[0], $clientDetail->org_name[0], LINK_INSIDE);
$breadcrumbs[] = $strings["add_client_user"];

$bodyCommand = "onLoad=\"document.client_user_addForm.un.focus();\"";
require_once("../themes/" . THEME . "/header.php");

// Content
$block1 = new block();
$block1->form = "client_user_add";
$block1->openForm("../users/addclientuser.php?organization=$organization&action=add");

if ($error != "") {
    $block1->headingError($strings["errors"]);
    $block1->contentError($error);
}

$block1->headingForm($strings["add_client_user"]);
$block1->openContent();
$block1->contentTitle($strings["enter_user_details"]);

$block1->contentRow(
    $strings["user_name"],
    "<input type=\"text\" name=\"un\" maxlength=\"16\" value=\"$un\" class=\"form-control\">"
);

$block1->contentRow(
    $strings["full_name"],
    "<input type=\"text\" name=\"fn\" maxlength=\"64\" value=\"$fn\" class=\"form-control\">"
);

$block1->contentRow(
    $strings["title"],
    "<input type=\"text\" name=\"tit\" maxlength=\"64\" value=\"$tit\" class=\"form-control\">"
);

// Organization select
$selectOrganization = "<select name=\"clod\" class=\"form-select\">";
$tmpquery = "WHERE org.id != '1' ORDER BY org.name";
$listOrganizations = new request();
$listOrganizations->openOrganizations($tmpquery);

for ($i = 0; $i < count($listOrganizations->org_id); $i++) {
    $selected = ($organization == $listOrganizations->org_id[$i]) ? "selected" : "";
    $selectOrganization .= "<option value=\"{$listOrganizations->org_id[$i]}\" $selected>
        {$listOrganizations->org_name[$i]}
    </option>";
}
$selectOrganization .= "</select>";

$block1->contentRow($strings["organization"], $selectOrganization);

$block1->contentRow(
    $strings["email"],
    "<input type=\"text\" name=\"em\" maxlength=\"128\" value=\"$em\" class=\"form-control\">"
);

$block1->contentRow(
    $strings["work_phone"],
    "<input type=\"text\" name=\"wp\" maxlength=\"32\" value=\"$wp\" class=\"form-control\">"
);

$block1->contentRow(
    $strings["home_phone"],
    "<input type=\"text\" name=\"hp\" maxlength=\"32\" value=\"$hp\" class=\"form-control\">"
);

$block1->contentRow(
    $strings["mobile_phone"],
    "<input type=\"text\" name=\"mp\" maxlength=\"32\" value=\"$mp\" class=\"form-control\">"
);

$block1->contentRow(
    $strings["fax"],
    "<input type=\"text\" name=\"fax\" maxlength=\"32\" value=\"$fax\" class=\"form-control\">"
);

$block1->contentRow(
    $strings["comments"],
    "<textarea name=\"c\" rows=\"3\" class=\"form-control\">$c</textarea>"
);

$block1->contentTitle($strings["enter_password"]);

$block1->contentRow(
    $strings["password"],
    "<input type=\"password\" name=\"pw\" maxlength=\"16\" class=\"form-control\">"
);

$block1->contentRow(
    $strings["confirm_password"],
    "<input type=\"password\" name=\"pwa\" maxlength=\"16\" class=\"form-control\">"
);

$block1->contentRow(
    "",
    "<button type=\"submit\" name=\"Save\" class=\"btn btn-primary\">
        {$strings["save"]}
     </button>"
);

$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once("../themes/" . THEME . "/footer.php");
?>
