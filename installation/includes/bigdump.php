<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//TODO: Rewrite this so its cleaner

// BigDump ver. 0.28b from 2007-06-08
// Staggered import of an large MySQL Dump (like phpMyAdmin 2.x Dump)
// Even through the webservers with hard runtime limit and those in safe mode
// Works fine with Internet Explorer 7.0 and Firefox 2.x

// Author:       Alexey Ozerov (alexey at ozerov dot de)
//               AJAX & CSV functionalities: Krzysiek Herod (kr81uni at wp dot pl)
// Copyright:    GPL (C) 2003-2007
// More Infos:   http://www.ozerov.de/bigdump.php

// This program is free software; you can redistribute it and/or modify it under the
// terms of the GNU General Public License as published by the Free Software Foundation;
// either version 2 of the License, or (at your option) any later version.

// THIS SCRIPT IS PROVIDED AS IS, WITHOUT ANY WARRANTY OR GUARANTEE OF ANY KIND

// USAGE

// 1. Adjust the database configuration in this file
// 2. Drop the old tables on the target database if your dump doesn't contain "DROP TABLE"
// 3. Create the working directory (e.g. dump) on your web-server
// 4. Upload bigdump.php and your dump files (.sql, .gz) via FTP to the working directory
// 5. Run the bigdump.php from your browser via URL like http://www.yourdomain.com/dump/bigdump.php
// 6. BigDump can start the next import session automatically if you enable the JavaScript
// 7. Wait for the script to finish, do not close the browser window
// 8. IMPORTANT: Remove bigdump.php and your dump files from the web-server

// If Timeout errors still occure you may need to adjust the $linepersession setting in this file

// LAST CHANGES

// *** Improved error message for file open errors
// *** Handle CSV files (you have to specify $csv_insert_table)
// *** Restart script in the background using AJAX

/**
 * Big Dump Handler for Migration and Import
 * Rewritten by Sam Moffatt from original work by Alexey Ozerov) for Joomla! 1.5
 */

//defined('_JEXEC') or die('Access Denied');

// Database configuration

$db_server = '';
$db_name = '';
$db_username = '';
$db_password = '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title><?php JText::_('Migration load script') ?></title>
		<script type="text/javascript" src="includes/js/installation.js"></script>
		</head>
	<body>
<?php

// Other Settings

$csv_insert_table = ''; // Destination table for CSV files
$ajax = false; // AJAX mode: import will be done without refreshing the website
//$filename         = '';     // Specify the dump filename to suppress the file selection dialog
$linespersession = 3000; // Lines to be executed per one import session
$delaypersession = 0; // You can specify a sleep time in milliseconds after each session
// Works only if JavaScript is activated. Use to reduce server overrun

// Allowed comment delimiters: lines starting with these strings will be dropped by BigDump

$comment[] = '#'; // Standard comment lines are dropped by default
$comment[] = '-- ';
// $comment[]='---';      // Uncomment this line if using proprietary dump created by outdated mysqldump
// $comment[]='/*!';         // Or add your own string to leave out other proprietary things

// Connection character set should be the same as the dump file character set (utf8, latin1, cp1251, koi8r etc.)
// See http://dev.mysql.com/doc/refman/5.0/en/charset-charsets.html for the full list

$db_connection_charset = '';

// *******************************************************************************************
// If not familiar with PHP please don't change anything below this line
// *******************************************************************************************

ob_start();

define('VERSION', '0.28b');
define('DATA_CHUNK_LENGTH', 16384); // How many chars are read per time
define('MAX_QUERY_LINES', 300); // How many lines may be considered to be one query (except text lines)
define('TESTMODE', false); // Set to true to process the file without actually accessing the database

header("Expires: Mon, 1 Dec 2003 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//@ini_set('auto_detect_line_endings', true);
//@set_time_limit(0);

// Clean and strip anything we don't want from user's input [0.27b]
foreach ($_REQUEST as $key => $val) {
	$val = preg_replace("/[^_A-Za-z0-9-\.&=]/i", '', $val);
	$_REQUEST[$key] = $val;
}

// Determine filename to execute for loading...
$filename = JPATH_BASE . DS . 'sql' . DS . 'migration' . DS . 'migrate.sql';
$_REQUEST['fn'] = $filename;
$error = false;
$file = false;
// Single file mode

if (!$error && !isset ($_REQUEST["fn"]) && $filename != "") {
	echo ("<p><a href=\"" . $_SERVER["PHP_SELF"] . "?start=1&amp;fn=$filename&amp;foffset=0&amp;totalqueries=0\">Start Import</a> from $filename into $db_name at $db_server</p>\n");
}

// Open the file

if (!$error && isset ($_REQUEST["fn"])) {

	// Recognize GZip filename

	if (eregi("\.gz$", $_REQUEST["fn"]))
		$gzipmode = true;
	else
		$gzipmode = false;
	if ((!$gzipmode && !$file = fopen($_REQUEST["fn"], "rt")) || ($gzipmode && !$file = gzopen($_REQUEST["fn"], "rt"))) {
		echo ("<p class=\"error\">". JText::sprintf("Cant open file for import", $_REQUEST["fn"]) ."</p>\n");
		echo ("<p>". JText::_('CHECKDUMPFILE') .
		" .<br />". JText::_('NEEDTOUPLOADFILE')."</p>\n");
		$error = true;
	}

	// Get the file size (can't do it fast on gzipped files, no idea how)

	else
		if ((!$gzipmode && fseek($file, 0, SEEK_END) == 0) || ($gzipmode && gzseek($file, 0) == 0)) {
			if (!$gzipmode)
				$filesize = ftell($file);
			else
				$filesize = gztell($file); // Always zero, ignore
		} else {
			echo ("<p class=\"error\">". JText::_('FILESIZEUNKNOWN') . $_REQUEST["fn"] . "</p>\n");
			$error = true;
		}
}

// *******************************************************************************************
// START IMPORT SESSION HERE
// *******************************************************************************************
if (!$error && isset ($_REQUEST["start"]) && isset ($_REQUEST["foffset"]) && eregi("(\.(sql|gz|csv))$", $_REQUEST["fn"])) {

	// Check start and foffset are numeric values

	if (!is_numeric($_REQUEST["start"]) || !is_numeric($_REQUEST["foffset"])) {
		echo ("<p class=\"error\">". JText::_('NONNUMERICOFFSET') ."</p>\n");
		$error = true;
	}

	if (!$error) {
		$_REQUEST["start"] = floor($_REQUEST["start"]);
		$_REQUEST["foffset"] = floor($_REQUEST["foffset"]);
	}

	// Check $_REQUEST["foffset"] upon $filesize (can't do it on gzipped files)

	if (!$error && !$gzipmode && $_REQUEST["foffset"] > $filesize) {
		echo ("<p class=\"error\">".JText::_('POINTEREOF')."</p>\n");
		$error = true;
	}

	// Set file pointer to $_REQUEST["foffset"]

	if (!$error && ((!$gzipmode && fseek($file, $_REQUEST["foffset"]) != 0) || ($gzipmode && gzseek($file, $_REQUEST["foffset"]) != 0))) {
		echo ("<p class=\"error\">". JText::_('UNABLETOSETOFFSET') . $_REQUEST["foffset"] . "</p>\n");
		$error = true;
	}

	// Start processing queries from $file

	if (!$error) {
		$query = "";
		$queries = 0;
		$totalqueries = $_REQUEST["totalqueries"];
		$linenumber = $_REQUEST["start"];
		$querylines = 0;
		$inparents = false;

		// Stay processing as long as the $linespersession is not reached or the query is still incomplete

		while ($linenumber < $_REQUEST["start"] + $linespersession || $query != "") {

			// Read the whole next line

			$dumpline = "";
			while (!feof($file) && substr($dumpline, -1) != "\n") {
				if (!$gzipmode)
					$dumpline .= fgets($file, DATA_CHUNK_LENGTH);
				else
					$dumpline .= gzgets($file, DATA_CHUNK_LENGTH);
			}
			if ($dumpline === "")
				break;

			// Handle DOS and Mac encoded linebreaks (I don't know if it will work on Win32 or Mac Servers)

			$dumpline = str_replace("\r\n", "\n", $dumpline);
			$dumpline = str_replace("\r", "\n", $dumpline);

			// DIAGNOSTIC
			// echo ("<p>Line $linenumber: $dumpline</p>\n");

			// Skip comments and blank lines only if NOT in parents

			if (!$inparents) {
				$skipline = false;
				reset($comment);
				foreach ($comment as $comment_value) {
					if (!$inparents && (trim($dumpline) == "" || strpos($dumpline, $comment_value) === 0)) {
						$skipline = true;
						break;
					}
				}
				if ($skipline) {
					$linenumber++;
					continue;
				}
			}

			// Remove double back-slashes from the dumpline prior to count the quotes ('\\' can only be within strings)

			$dumpline_deslashed = str_replace("\\\\", "", $dumpline);

			// Count ' and \' in the dumpline to avoid query break within a text field ending by ;
			// Please don't use double quotes ('"')to surround strings, it wont work

			$parents = substr_count($dumpline_deslashed, "'") - substr_count($dumpline_deslashed, "\\'");
			if ($parents % 2 != 0)
				$inparents = !$inparents;

			// Add the line to query

			$query .= $dumpline;

			// Don't count the line if in parents (text fields may include unlimited linebreaks)

			if (!$inparents)
				$querylines++;

			// Stop if query contains more lines as defined by MAX_QUERY_LINES

			if ($querylines > MAX_QUERY_LINES) {
				echo ("<p class=\"error\">". JText::_('STOPPEDATLINE') ." $linenumber. </p>");
				echo ("<p>". JText::sprintf('TOOMANYLINES',MAX_QUERY_LINES)."</p>");
				$error = true;
				break;
			}
			$vars = $this->getVars();
		$DBtype 	= JArrayHelper::getValue($vars, 'DBtype', 'mysql');
		$DBhostname = JArrayHelper::getValue($vars, 'DBhostname', '');
		$DBuserName = JArrayHelper::getValue($vars, 'DBuserName', '');
		$DBpassword = JArrayHelper::getValue($vars, 'DBpassword', '');
		$DBname 	= JArrayHelper::getValue($vars, 'DBname', '');
		$DBPrefix 	= JArrayHelper::getValue($vars, 'DBPrefix', 'jos_');
		$DBOld 		= JArrayHelper::getValue($vars, 'DBOld', 'bu');
		//$migration 		= JArrayHelper::getValue($vars, 'migration', '0');
		$migration = JRequest::getVar( 'migration', 0, 'post', 'bool' );

			$db = & JInstallationHelper::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);
			if (JError::isError($db)) jexit(JText::_('CONNECTION FAIL'));

			// Execute query if end of query detected (; as last character) AND NOT in parents

			if (ereg(";$", trim($dumpline)) && !$inparents) {
				if (!TESTMODE) {
					$db->setQuery(trim($query));
//					echo $query . '<br />';
					if (!$db->Query()) {
						echo ("<p class=\"error\">".JText::_('Error at the line') ." $linenumber: ". trim($dumpline) . "</p>\n");
						echo ("<p>".JText::_('Query:') .  trim(nl2br(htmlentities($query))) ."</p>\n");
						echo ("<p>MySQL: " . mysql_error() . "</p>\n");
						$error = true;
						break;
					}
					$totalqueries++;
					$queries++;
					$query = "";
					$querylines = 0;
				}
			}
			$linenumber++;
		}
	}

	// Get the current file position

	if (!$error) {
		if (!$gzipmode)
			$foffset = ftell($file);
		else
			$foffset = gztell($file);
		if (!$foffset) {
			echo ("<p class=\"error\">".JText::_('CANTREADPOINTER')."</p>\n");
			$error = true;
		}
	}

	// Print statistics

	if (!$error) {
		$lines_this = $linenumber - $_REQUEST["start"];
		$lines_done = $linenumber -1;
		$lines_togo = ' ? ';
		$lines_tota = ' ? ';

		$queries_this = $queries;
		$queries_done = $totalqueries;
		$queries_togo = ' ? ';
		$queries_tota = ' ? ';

		$bytes_this = $foffset - $_REQUEST["foffset"];
		$bytes_done = $foffset;
		$kbytes_this = round($bytes_this / 1024, 2);
		$kbytes_done = round($bytes_done / 1024, 2);
		$mbytes_this = round($kbytes_this / 1024, 2);
		$mbytes_done = round($kbytes_done / 1024, 2);

		if (!$gzipmode) {
			$bytes_togo = $filesize - $foffset;
			$bytes_tota = $filesize;
			$kbytes_togo = round($bytes_togo / 1024, 2);
			$kbytes_tota = round($bytes_tota / 1024, 2);
			$mbytes_togo = round($kbytes_togo / 1024, 2);
			$mbytes_tota = round($kbytes_tota / 1024, 2);

			$pct_this = ceil($bytes_this / $filesize * 100);
			$pct_done = ceil($foffset / $filesize * 100);
			$pct_togo = 100 - $pct_done;
			$pct_tota = 100;

			if ($bytes_togo == 0) {
				$lines_togo = '0';
				$lines_tota = $linenumber -1;
				$queries_togo = '0';
				$queries_tota = $totalqueries;
			}

			$pct_bar = "<div style=\"height:15px;width:$pct_done%;background-color:#000080;margin:0px;\"></div>";
		} else {
			$bytes_togo = ' ? ';
			$bytes_tota = ' ? ';
			$kbytes_togo = ' ? ';
			$kbytes_tota = ' ? ';
			$mbytes_togo = ' ? ';
			$mbytes_tota = ' ? ';

			$pct_this = ' ? ';
			$pct_done = ' ? ';
			$pct_togo = ' ? ';
			$pct_tota = 100;
			$pct_bar = str_replace(' ', '&nbsp;', '<tt>[         Not available for gzipped files          ]</tt>');
		}

		// Finish message and restart the script

		if ($linenumber < $_REQUEST["start"] + $linespersession) { ?>
		<div id="installer"><p class="successcentr"><?php echo JText::_('CONGRATSEOF');?></p>
		<?php
			// Do migration
			if ($migration) { ?>
			<br />Migration will continue shortly...</div>
			<form action="index.php" method="post" name="migrateForm" id="migrateForm" class="form-validate" target="migrationtarget">
				<input type="hidden" name="task" value="postmigrate" />
				<input type="hidden" name="migration" value="<?php echo $migration ?>" />
			  	<input type="hidden" name="loadchecked" value="1" />
			  	<input type="hidden" name="dataLoaded" value="1" />
			  	<input type="hidden" name="DBtype" value="<?php echo $DBtype ?>" />
			  	<input type="hidden" name="DBhostname" value="<?php echo $DBhostname ?>" />
			  	<input type="hidden" name="DBuserName" value="<?php echo $DBuserName ?>" />
			  	<input type="hidden" name="DBpassword" value="<?php echo $DBpassword ?>" />
			  	<input type="hidden" name="DBname" value="<?php echo $DBname ?>" />
			  	<input type="hidden" name="DBPrefix" value="<?php echo $DBPrefix ?>" />
			</form>
  			<script language="JavaScript" type="text/javascript">window.setTimeout('submitForm(this.document.migrateForm,"postmigrate")',500);</script>
			<?php
			} else echo '<br />'. JText::_('FINALIZEINSTALL').'</div>';
			$error = true;
		} else {
			if ($delaypersession != 0)
				echo ("<p class=\"centr\">".JText::sprintf('DELAYMSG',$delaypersession)."</p>\n");
			?><script language="JavaScript" type="text/javascript">window.setTimeout('submitForm(this.document.migrateForm,"dumpLoad")',500);</script>
			<div id="installer"><p><?php echo JText::_('LOADSQLFILE') ?></p></div>

			<form action="index.php" method="post" name="migrateForm" id="migrateForm" class="form-validate" target="migrationtarget">
				<input type="hidden" name="task" value="dumpLoad" />
				<input type="hidden" name="migration" value="<?php echo $migration ?>" />
			  	<input type="hidden" name="loadchecked" value="1" />
			  	<input type="hidden" name="dataLoaded" value="1" />
			  	<input type="hidden" name="DBtype" value="<?php echo $DBtype ?>" />
			  	<input type="hidden" name="DBhostname" value="<?php echo $DBhostname ?>" />
			  	<input type="hidden" name="DBuserName" value="<?php echo $DBuserName ?>" />
			  	<input type="hidden" name="DBpassword" value="<?php echo $DBpassword ?>" />
			  	<input type="hidden" name="DBname" value="<?php echo $DBname ?>" />
			  	<input type="hidden" name="DBPrefix" value="<?php echo $DBPrefix ?>" />
				<input type="hidden" name="start" value="<?php echo $linenumber ?>" />
				<input type="hidden" name="foffset" value="<?php echo $foffset ?>" />
				<input type="hidden" name="totalqueries" value="<?php echo $totalqueries ?>" />
			</form>
  <?php
		}
	} else
		echo ("<p class=\"error\">".JText::_('STOPPEDONERROR')."</p>\n");

}

if ($file && !$gzipmode) {
	fclose($file);
}
else if ($file && $gzipmode) {
	gzclose($file);
}
