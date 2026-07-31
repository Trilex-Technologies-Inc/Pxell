<?php
#Application name: PhpCollab
#Status page: 0
/* $Id: grab_globals.lib.php,v 1.2 2004/12/15 21:21:18 madbear Exp $ */


/**
 * This library grabs the names and values of the variables sent or posted to a
 * script in the '$_GET', '$_POST', etc. arrays and sets simple globals variables from
 * them
 */
if (!defined('PMA_GRAB_GLOBALS_INCLUDED')) {
    define('PMA_GRAB_GLOBALS_INCLUDED', 1);

    if (!empty($_GET)) {
        extract($_GET);
    } // end if

    if (!empty($_POST)) {
        extract($_POST);
    } // end if

    if (!empty($_FILES)) {
        foreach ($_FILES as $name => $value) {
            $$name = $value['tmp_name'];
        }
    } // end if

} // $__PMA_GRAB_GLOBALS_LIB__
?>