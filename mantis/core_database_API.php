<?php
#Application name: PhpCollab
#Status page: 0
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Database
	###########################################################################

	# This in the general interface for all database calls.
	# The actual SQL queries are found in the pages.
	# Use this as a starting point to port to other databases

	# Global connection handle for the mantis database wrapper
	$GLOBALS['mantis_dbh'] = null;

	# --------------------
	# connect to database and select database
	function db_connect($p_hostname="", $p_username="",
						$p_password="", $p_database="",
						$p_port="" ) {

		global $g_hostname, $g_db_username, $g_db_password, $g_database_name, $g_port;

		if ( empty( $p_hostname ) ) {
			$p_hostname = $g_hostname;
		}
		if ( empty( $p_username ) ) {
			$p_username = $g_db_username;
		}
		if ( empty( $p_password ) ) {
			$p_password = $g_db_password;
		}
		if ( empty( $p_database ) ) {
			$p_database = $g_database_name;
		}
		if ( empty( $p_port ) ) {
			$p_port = $g_port;
		}

		$t_result = mysqli_connect( $p_hostname, $p_username, $p_password, $p_database, (int)$p_port );

		if ( !$t_result ) {
			echo "ERROR: FAILED CONNECTION TO DATABASE: ";
			echo db_error();
			exit;
		}

		$GLOBALS['mantis_dbh'] = $t_result;
	}
	# --------------------
	# persistent connect to database and select database
	function db_pconnect($p_hostname="localhost", $p_username="root",
						$p_password="", $p_database="mantis",
						$p_port=3306 ) {

		$t_result = mysqli_connect( 'p:' . $p_hostname, $p_username, $p_password, $p_database, (int)$p_port );

		if ( !$t_result ) {
			echo "ERROR: FAILED CONNECTION TO DATABASE: ";
			echo db_error();
			exit;
		}

		$GLOBALS['mantis_dbh'] = $t_result;
	}
	# --------------------
	# execute query, requires connection to be opened,
	function db_query( $p_query ) {

		$t_result = mysqli_query( $GLOBALS['mantis_dbh'], $p_query );

		if ( !$t_result ) {
			echo "ERROR: FAILED QUERY: ".$p_query." : ";
			echo db_error();
			exit;
		} else {
			return $t_result;
		}
	}
	# --------------------
	function db_select_db( $p_db_name ) {
		return mysqli_select_db( $GLOBALS['mantis_dbh'], $p_db_name );
	}
	# --------------------
	function db_num_rows( $p_result ) {
		return mysqli_num_rows( $p_result );
	}
	# --------------------
	function db_fetch_array( $p_result ) {
		return mysqli_fetch_array( $p_result );
	}
	# --------------------
	function db_result( $p_result, $p_index1=0, $p_index2=0 ) {
		if ( $p_result && ( db_num_rows( $p_result ) > 0 ) ) {
			mysqli_data_seek( $p_result, $p_index1 );
			$row = mysqli_fetch_array( $p_result );
			if ( is_numeric( $p_index2 ) ) {
				return $row[$p_index2];
			}
			return $row[$p_index2];
		} else {
			return false;
		}
	}
	# --------------------
	# return the last inserted id
	# For MS SQL use: SELECT @@IDENTITY AS 'id'
	function db_insert_id() {
		return mysqli_insert_id( $GLOBALS['mantis_dbh'] );
	}
	# --------------------
	function db_error_num() {
		return mysqli_errno( $GLOBALS['mantis_dbh'] );
	}
	# --------------------
	function db_error_msg() {
		return mysqli_error( $GLOBALS['mantis_dbh'] );
	}
	# --------------------
	# display both the error num and error msg
	function db_error() {
		return "<p>".db_error_num().": ".db_error_msg()."<p>";
	}
	# --------------------
	# close the connection.
	# Not really necessary most of the time since a connection is
	# automatically closed when a page finishes loading.
	function db_close() {
		if ( $GLOBALS['mantis_dbh'] ) {
			mysqli_close( $GLOBALS['mantis_dbh'] );
			$GLOBALS['mantis_dbh'] = null;
		}
	}
	# --------------------
?>