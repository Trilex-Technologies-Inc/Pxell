<?php // $Revision: 1.19 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: block.class.php,v 1.19 2005/05/27 19:42:52 madbear Exp $
 *
 * Copyright (c) 2003 by the NetOffice developers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

class block {
    function block() {
        $this->iconWidth = "23";
        $this->iconHeight = "23";
        $this->bgColor = "#5B7F93";
        $this->fgColor = "#C4D3DB";
        $this->oddColor = "#F5F5F5";
        $this->evenColor = "#EFEFEF";
        $this->highlightOn = "#FFFEE7";

        $this->class = "table-light";
        $this->highlightOff = $this->oddColor;
        $this->theme = THEME;
        $this->pathImg = "../themes";
    }

    /**
     * Print tooltips
     *
     * @param string $item Text printed in tooltip
     * @access public
     */
    function printHelp($item) {
        global $help, $strings;
        return ' <a href="#" class="text-decoration-none" data-bs-toggle="tooltip" title="' . (addslashes($help[$item])) . '">' . $strings["help"] . '</a>';
    }

    function note($content) {
        echo '<div class="alert alert-info">' . $content . '</div>';
    }

    //=== heading with embedded icon-palette ============
    function heading($title) {
        echo '<div class="card-header p-2 bg-primary text-white d-flex justify-content-between align-items-center">';
        echo '<h5 class="mb-0">' . ($title) . '</h5>';
        echo '<div class="btn-group">';
    }

    function heading_close(){
        echo '</div></div>';
    }

    function block_close() {
        echo '</div>';
    }

    //=== heading with embedded icon-palette ============
    function headingForm($title) {
        echo '<div class="card mb-4">';
        echo '<div class="card-header p-2 bg-primary text-white d-flex justify-content-between align-items-center">';
        echo '<h5 class="mb-0">' . ($title) . '</h5>';
        echo '<div class="ms-3">&nbsp;</div>';
        echo '</div>';
        echo '<div class="card-body">';
    }

    function headingForm_close() {
        echo '</div></div>';
    }

    function closeToggle() {
        echo "</div>\n\n";
    }

    //=== open headingToggle with embedded icon-palette ================================
    function headingToggle($title) {
        if ($_COOKIE[$this->form] == "c") {
            $style = "none";
            $arrow = "closed";
            $blockStyle="";
        }
        else {
            $style = "block";
            $arrow = "open";
            $blockStyle="";
        }
        $this->toggle=true;

        echo '<div class="card mb-3">';
        echo '<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" id="' . $this->form . 'Head" data-bs-toggle="collapse" data-bs-target="#' . $this->form . 'Body">';
        echo '<h5 class="mb-0">';
        echo '<a href="#" class="text-white text-decoration-none">';
        echo '<i class="bi bi-chevron-' . ($arrow == 'open' ? 'down' : 'right') . ' me-2"></i>';
        echo ($title);
        echo '</a></h5>';
        echo '<div class="btn-group">';
    }

    function headingToggle_close() {
        if ($this->toggle && $_COOKIE[$this->form] == 'c') {
            $style = 'collapse';
            $arrow = 'closed';
        } else {
            $style = 'collapse show';
            $arrow = 'open';
        }

        echo '</div></div>';
        echo '<div id="' . $this->form . 'Body" class="' . $style . '">';
        echo '<div class="card-body">';
    }

    /**
     * Print error heading
     */
    function headingError($title) {
        echo '<h1 class="text-danger mb-4">' . $title . '</h1>';
    }

    /**
     * Print error message in table
     */
    function contentError($content) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
        echo $content;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }

    function returnBorne($current) {
        global ${'borne'.$current};
        return ${'borne'.$current} ?: "0";
    }

    /**
     * Print page-per-page in bottom of list block
     */
    function bornesFooter($current, $total, $showall, $parameters) {
        global $strings;
        if ($this->rowsLimit < $this->recordsTotal) {
            echo '<div class="d-flex justify-content-between align-items-center mt-3">';
            echo '<div class="pagination">';

            $nbpages = ceil($this->recordsTotal / $this->rowsLimit);
            $j = "0";

            echo '<ul class="pagination pagination-sm mb-0">';
            for($i = 1; $i <= $nbpages; $i++) {
                if ($this->borne == $j) {
                    echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                } else {
                    echo '<li class="page-item">';
                    echo '<a class="page-link" href="' . $PHP_SELF . '?';
                    for ($k = 1; $k <= $total; $k++) {
                        global ${'borne'.$k};
                        if ($k != $current) {
                            echo "&borne$k=" . ${'borne'.$k};
                        } else if ($k == $current) {
                            echo "&borne$k=$j";
                        }
                    }
                    echo "&$parameters#" . $this->form . "Anchor\">$i</a></li>";
                }
                $j = $j + $this->rowsLimit;
            }
            echo '</ul></div>';

            if ($showall != "") {
                echo '<a href="' . $showall . '" class="btn btn-sm btn-outline-primary">' . $strings["show_all"] . '</a>';
            }
            echo '</div>';
        }
    }

    //==== Print Message table =====================
    function messageBox($msgLabel) {
        echo '<div class="alert alert-info">' . $msgLabel . '</div>';
    }

    /**
     * Open icons table
     */
    function openPaletteIcon() {
        echo '<div class="btn-group ms-auto">';
    }

    //==== Close icons table ====================================
    function closePaletteIcon()  {
        echo '</div>';
        $this->headingToggle_close();
    }

    /**
     * Open icons script
     */
    function openPaletteScript() {
        echo "<script>
document." . $this->form . "Form.buttons = new Array();\n";
    }

    /**
     * Close icons script
     */
    function closePaletteScript($compt, $values) {
        echo "MM_updateButtons(document." . $this->form . "Form, 0);document." . $this->form . "Form.checkboxes = new Array();";
        for ($i = 0; $i < $compt; $i++) {
            echo "document." . $this->form . "Form.checkboxes[document." . $this->form . "Form.checkboxes.length] = new MMCheckbox('$values[$i]',document." . $this->form . "Form,'" . $this->form . "cb$values[$i]');";
        }
        echo "document." . $this->form . "Form.tt = '" . $this->form . "tt';
</script>\n\n";
    }

    /**
     * Define sorting to apply on a list block
     */
    function sorting($sortingRef, $sortingValue, $sortingDefault, $sortingFields) {
        if ($sortingRef != "") {
            $this->sortingRef = $sortingRef;
        }

        if ($sortingValue != "") {
            $this->sortingValue = $sortingValue;
        }

        if ($sortingDefault != "") {
            $this->sortingDefault = $sortingDefault;
        }

        if ($sortingFields != "") {
            $this->sortingFields = $sortingFields;
        }

        global $sortingOrders, $sortingFields, $sortingArrows, $sortingStyles, $explode;

        if (isset($this->sortingValue) != "") {
            $explode = explode(" ", $this->sortingValue);
        } else {
            $this->sortingValue = $this->sortingDefault;
            $explode = explode(" ", $this->sortingValue);
        }

        for ($i = 0; $i < count($sortingFields); $i++) {
            if ($sortingFields[$i] == $explode[0] && $explode[1] == "DESC") {
                $sortingOrders[$i] = "ASC";
                $sortingArrows[$i] = ' <i class="bi bi-sort-down"></i>';
                $sortingStyles[$i] = "bg-light";
            } else if ($sortingFields[$i] == $explode[0] && $explode[1] == "ASC") {
                $sortingOrders[$i] = "DESC";
                $sortingArrows[$i] = ' <i class="bi bi-sort-up"></i>';
                $sortingStyles[$i] = "bg-light";
            } else {
                $sortingOrders[$i] = "ASC";
                $sortingArrows[$i] = "";
                $sortingStyles[$i] = "";
            }
        }

        if ($sortingOrders != "") {
            $this->sortingOrders = $sortingOrders;
        }

        if ($sortingArrows != "") {
            $this->sortingArrows = $sortingArrows;
        }

        if ($sortingStyles != "") {
            $this->sortingStyles = $sortingStyles;
        }
    }

    /**
     * Open a standard form
     */
    function openForm($address)  {
        echo '<a id="' . $this->form . 'Anchor"></a>';
        echo '<form method="POST" action="' . $address . '" name="' . $this->form . 'Form">';
    }

    /**
     * Close a form used with a list block
     */
    function closeFormResults(){
        echo '<input type="hidden" name="sor_cible" value="' . $this->sortingRef . '">';
        echo '<input type="hidden" name="sor_champs" value="">';
        echo '<input type="hidden" name="sor_ordre" value="">';
        echo '</form>';
    }

    /**
     * Define column labels in a list block
     */
    function labels($labels, $published, $sorting = "true", $sortingOff = "") {
        global $labels, $sortingOrders, $sortingFields, $sortingArrows, $sortingStyles, $strings, $sitePublish;

        $sortingFields = $this->sortingFields;
        $sortingOrders = $this->sortingOrders;
        $sortingArrows = $this->sortingArrows;
        $sortingStyles = $this->sortingStyles;

        if ($sitePublish == "false" && $published == "true") {
            $comptLabels = count($labels) - 1;
        } else {
            $comptLabels = count($labels);
        }

        for ($i = 0; $i < $comptLabels; $i++) {
            if ($sorting == "true") {
                echo '<th class="' . $sortingStyles[$i] . '">';
                echo '<a href="javascript:document.' . $this->form . 'Form.sor_cible.value=\'' . $this->sortingRef . '\';document.' . $this->form . 'Form.sor_champs.value=\'' . $sortingFields[$i] . '\';document.' . $this->form . 'Form.sor_ordre.value=\'' . $sortingOrders[$i] . '\';document.' . $this->form . 'Form.submit();" class="text-decoration-none">';
                echo trim($labels[$i]);
                echo $sortingArrows[$i];
                echo '</a></th>';
            } else {
                if ($sortingOff[1] == "ASC") {
                    $sortingArrow = ' <i class="bi bi-sort-up"></i>';
                } else if ($sortingOff[1] == "DESC") {
                    $sortingArrow = ' <i class="bi bi-sort-down"></i>';
                }
                if ($i == $sortingOff[0]) {
                    echo '<th class="bg-light">' . $labels[$i] . $sortingArrow . '</th>';
                } else {
                    echo '<th>' . $labels[$i] . '</th>';
                }
            }
        }
        echo '</tr>';
    }

    /**
     * Open results list
     */
    function openResults($checkbox = "true") {
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover table-striped">';
        echo '<thead><tr>';
        if ($checkbox == "true") {
            echo '<th scope="col" width="1%">';
            echo '<div class="form-check">';
            echo '<input class="form-check-input" type="checkbox" onclick="MM_toggleSelectedItems(document.' . $this->form . 'Form,\'' . $this->theme . '\')">';
            echo '</div></th>';
        } else {
            echo '<th scope="col" width="1%">&nbsp;</th>';
        }
    }

    function closeResults()  {
        echo '</table></div>';
    }

    function noresults() {
        global $strings;
        echo '<div class="alert alert-warning">' . $strings["no_items"] . '</div>';
    }

    /**
     * Display an icon (html)
     */
      function paletteIcon($num, $type, $text)  {
        echo "<a href=\"javascript:var b = MM_getButtonWithName(document." . $this->form . "Form, '" . $this->form . "$num'); if (b) b.click();\" onMouseOver=\"var over = MM_getButtonWithName(document." . $this->form . "Form, '" . $this->form . "$num'); if (over) over.over(); return true; \" onMouseOut=\"var out = MM_getButtonWithName(document." . $this->form . "Form, '" . $this->form . "$num'); if (out) out.out(); return true; \">";
        echo "<img width=\"$this->iconWidth\" height=\"$this->iconHeight\" border=\"0\" name=\"" . $this->form . "$num\" src=\"$this->pathImg/$this->theme/btn_" . $type . "_norm.gif\" alt=\"$text\">";
        echo "</a>";

        #echo "<td class=\"commandBtn\">";
        #echo "<a href=\"javascript:var b = MM_getButtonWithName(document." . $this->form . "Form, '" . $this->form . "$num'); if (b) b.click();\" onMouseOver=\"var over = MM_getButtonWithName(document." . $this->form . "Form, '" . $this->form . "$num'); if (over) over.over(); return true; \" onMouseOut=\"var out = MM_getButtonWithName(document." . $this->form . "Form, '" . $this->form . "$num'); if (out) out.out(); return true; \">";
        #echo "<img width=\"$this->iconWidth\" height=\"$this->iconHeight\" border=\"0\" name=\"" . $this->form . "$num\" src=\"$this->pathImg/$this->theme/btn_" . $type . "_norm.gif\" alt=\"$text\">";
        #echo "</a>";
        #echo "</td>";
    }

    /**
     * Display an icon (JavaScript)
     */
     function paletteScript($num, $type, $link, $options, $text) {
	 	echo "document." . $this->form . "Form.buttons[document." . $this->form . "Form.buttons.length] = new MMCommandButton('" . $this->form . "$num',document." . $this->form . "Form,'" . $link . "','$this->pathImg/$this->theme/btn_" . $type . "_norm.gif','$this->pathImg/$this->theme/btn_" . $type . "_over.gif','$this->pathImg/$this->theme/btn_" . $type . "_down.gif','$this->pathImg/$this->theme/btn_" . $type . "_dim.gif',$options,'','" . "".$text."" . "',false,'');\n";

       	#echo "document." . $this->form . "Form.buttons[document." . $this->form . "Form.buttons.length] = ";
        #echo "new MMCommandButton('";
        #echo $this->form . "$num', ";
        #echo "document." . $this->form . "Form,'";
        #echo $link ."','$this->pathImg/$this->theme/btn_" . $type . "_norm.gif',";
        #echo "'$this->pathImg/$this->theme/btn_" . $type . "_over.gif',";
        #echo "'$this->pathImg/$this->theme/btn_" . $type . "_down.gif',";
        #echo "'$this->pathImg/$this->theme/btn_" . $type . "_dim.gif',";
        #echo "$options,'','" . "<nobr>".$text."</nobr>" . "',false,'');\n";

    }

    /**
     * Start a div container to display sheet/form
     */
    function openContent() {
        echo '<div class="card-body">';
    }

    /**
     * Display a row in sheet/form using Bootstrap classes
     */
    function contentRow($left, $right, $altern = false) {
        if ($this->class == "") {
            $this->class = "";
        }

        $bgClass = $altern ? "bg-light" : "";

        if (trim($left) === "") {
            echo '<div class="card mb-3 ' . $bgClass . '">';
            echo '<div class="card-body">' . $right . '</div>';
            echo '</div>';
        } else {
            echo '<div class="row mb-2 ' . $bgClass . '">';
            echo '<div class="col-md-3 fw-bold">' . $left . ':</div>';
            echo '<div class="col-md-9">' . $right . '</div>';
            echo '</div>';
        }

        if ($altern) {
            $this->class = ($this->class == "") ? "bg-light" : "";
        }
    }

    function formRow($label, $input, $alternate = false) {
        $bg = $alternate ? "bg-light" : "";

        echo '<div class="row mb-3 ' . $bg . '">';
        echo '<div class="col-md-12">';
        if ($label) {
            echo '<label class="form-label fw-bold">' . $label . '</label>';
        }
        echo $input;
        echo '</div></div>';
    }

    /**
     * Open a clickable row
     */
    function openRow($ref = -1) {
        $class = $this->class == "table-light" ? "" : "table-light";
        echo '<tr class="' . $class . '" ';

        if ($ref != -1) {
            echo 'onclick="MM_toggleItem(document.' . $this->form . 'Form, \'' . $ref . '\', \'' . $this->form . 'cb' . $ref . '\',\'' . $this->theme . '\')" ';
        }

        echo 'style="cursor: pointer;">';

        $this->class = ($this->class == "table-light") ? "" : "table-light";
    }

    /**
     * Display a checkbox in a row
     */
    function checkboxRow($ref, $checkbox = true) {
        echo '<td>';
        if ($checkbox) {
            echo '<div class="form-check">';
            echo '<input class="form-check-input" type="checkbox" name="' . $this->form . 'cb' . $ref . '" id="' . $this->form . 'cb' . $ref . '">';
            echo '</div>';
        }
        echo '</td>';
    }

    /**
     * Display a single cell (column)
     */
    function cellRow($content = null, $width = null, $nowrap = false) {
        $style = "";
        if ($nowrap) $style .= "white-space: nowrap;";
        if ($width) $style .= "width: " . $width . "%;";

        echo '<td style="' . $style . '">' . $content . '</td>';
    }

    /**
     * Close the opened row
     */
    function closeRow() {
        echo '</tr>';
    }

    /**
     * Display a section title
     */
    function contentTitle($title) {
        echo '<div class="row"><div class="col-12">';
        echo '<h5 class="mt-4 mb-3 border-bottom pb-2">' . $title . '</h5>';
        echo '</div></div>';
    }

    /**
     * Close the content container
     */
    function closeContent() {
        echo '</div>';
    }

    function closeForm() {
        echo '</form>';
    }

    //======================================================================
    // sections (top navigation)
    //======================================================================
    function openNavigation()  {
        echo '<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">';
        echo '<div class="container-fluid">';
        echo '<div class="navbar-nav">';
    }

    function itemNavigation($url, $label) {
        echo '<a class="nav-link" href="' . $url . '">' . $label . '</a>';
    }

    function itemNavigationCurrent($url, $label) {
        echo '<a class="nav-link active" aria-current="page" href="' . $url . '">' . $label . '</a>';
    }

    function closeNavigation() {
        echo '</div></div></nav>';
    }

    //======================================================================
    // breadcrumbs
    //======================================================================
    function openBreadcrumbs()  {
        echo '<nav aria-label="breadcrumb" class="mb-4">';
        echo '<ol class="breadcrumb">';
    }

    function itemBreadcrumbs($content) {
        echo '<li class="breadcrumb-item">' . $content . '</li>';
    }

    function closeBreadcrumbs() {
        echo '</ol></nav>';
    }

    //======================================================================
    // account
    //======================================================================
    function openAccount()   {
        echo '<div class="d-flex justify-content-end mb-3">';
        echo '<div class="btn-group">';
    }

    function itemAccount($content) {
        if ($this->accountTotal == "") {
            $this->accountTotal = "0";
        }
        $this->account[$this->accountTotal] = $content;
        $this->accountTotal = $this->accountTotal + 1;
    }

    function closeaccount() {
        $items = $this->accountTotal;
        for ($i = 0; $i < $items; $i++) {
            echo $this->account[$i];
            if ($items-1 != $i) {
                echo ' ';
            }
        }
        echo '</div></div>';
    }
}

//--- define standard instance to simulate static class in php4 ---
$template = new block();

//======================================================================
// links
//======================================================================
function buildLink($url, $label, $type = LINK_INSIDE) {
    switch($type) {
        case LINK_INSIDE:
            return '<a href="' . $url . '" class="link-primary text-decoration-none">' . $label . '</a>';

        case LINK_STRIKE:
            return '<a href="' . $url . '" class="link-secondary text-decoration-line-through">' . $label . '</a>';

        case LINK_BLANK:
        case LINK_OUT:
            return '<a href="' . $url . '" target="_blank" class="link-primary text-decoration-none">' . $label . '</a>';

        case LINK_ICON:
            return '<a href="' . $url . '" class="btn btn-sm btn-outline-primary">' . $label . '</a>';

        case LINK_POWERED:
            return 'Powered by <a href="' . $url . '" target="_blank" class="link-primary">' . $label . '</a>';

        case LINK_MAIL:
            return '<a href="mailto:' . $url . '" class="link-primary text-decoration-none"><i class="bi bi-envelope me-1"></i>' . $label . '</a>';

        default:
            return '<a href="' . $url . '" class="link-primary">' . $label . '</a>';
    }
}

?>