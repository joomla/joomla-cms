<?php
/*************************************************************************************
** - Copyright (c) 2006 Belus Technology Inc.
** -
** - By using the software and documentation, the user expressly agrees that
** - the use of the software documentation is at its sole risk. The software
** - and documentation is made available on an "as is" basis. Copyright owner
** - does not warrant that the software and documentation will meet the user's
** - requirements, or that the operation of the software will be uninterrupted
** - or error-free and does not make any warranty whatsoever regarding the
** - software and documentation, any information, services or products provided
** - through or in connection with the software and documentation, or any
** - results to be obtained through the use thereof, and hereby expressly
** - disclaims on behalf of itself and all suppliers any and all warranties,
** - including without limitation: any express or implied warranties of:
** - 1) merchantability; 2) fitness for a particular purpose; 3) effort to
** - achieve purpose; 4) quality; 5) accuracy; 6) non-infringement. Copyright
** - owner shall not be liable to the user, or to any third party, for any loss
** - of data, profits, loss of use, interruption of business, error, omission,
** - deletion, defect, delay in operation or transmission, computer virus,
** - communications line failure, theft or destruction or unauthorized access to,
** - alteration of, or use of records, whether for breach of contract, tortious
** - behavior, negligence, or under any other cause of action.
** -
** - All right, title and interest including, but not limited to, copyright and
** - other intellectual property rights in and to the software and documentation
** - are owned by Copyright owner and the use of or modification to the software
** - and documentation does not pass to the user any title to or any proprietary
** - rights in the software and documentation.
** -
** - Permission is granted to copy, modify and distribute the software and
** - documentation for any purpose and royalty-free, subject to the following:
** - copyright and other intellectual property rights in and to the software and
** - documentation must not be misrepresented and this notice may not be removed
** - from any source distribution of the software or documentation.
*************************************************************************************/

/****************************************************************************************
** - Purpose: Image Library
** - Version: 1.00
** - Date: 2006-01-30
** - Documentation: http://xstandard.com/xstandard-lite-for-partner-cms/
****************************************************************************************/

$base_path = "../../../";
require_once( $base_path . 'configuration.php' );

/*************************** OPTIONAL - CHANGE THESE SETTINGS **************************/
define("XS_LIBRARY_FOLDER", $base_path . 'images/stories/'); // Root library folder
define("XS_BASE_URL", 'images/stories/'); // Base URL to create for files. Relative URLs are okay, for example: "images/".
define("XS_ACCEPTED_FILE_TYPES", "gif jpeg jpg png bmp"); // A list of accepted file extensions.
define("XS_GET_DATE_LAST_MODIFIED", true); //Provide the last modified date for files.  For large libraries, turning this off can improve performance.
define("XS_GET_FILE_SIZE", true); //Provide file size. For large libraries, turning this off can improve performance.
define("XS_GET_IMAGE_DIMENSIONS", true); //Provide image dimensions. For large libraries, turning this off can improve performance.
define("XS_DEFAULT_IMAGE_IS_DECORATIVE", false); //Flag to indicate if images should be treated as decorative by default.
define("XS_HIDDEN_FOLDERS", "CVS,_vti_cnf"); //Comma delimited list of hidden folders
define("XS_HIDDEN_FILES", ""); //Comma delimited list of hidden files
/*************************** OPTIONAL - CHANGE THESE SETTINGS ***************************/


function xs_build_path($path, $name) {
	$p = str_replace("\\", "/", trim($path));
	$n = trim($name);

	if (strlen($p) > 0 and strlen($n) > 0) {
		if (substr($p, strlen($p) - 1, 1) == "/") {
			return $p . $n;
		} else {
			return $p . "/" . $n;
		}
	} else {
		return $p . $n;
	}
}

function xs_is_accepted_file_type($file_name) {
	$pos = strrpos($file_name, ".");
	$ext = "";
	if ($pos !== false) {
		$ext = strtolower(substr($file_name, $pos + 1));
	}

	$accepted_file_types = explode(" ", strtolower(XS_ACCEPTED_FILE_TYPES));
	foreach ($accepted_file_types as $accepted_file_type) {
		if ($accepted_file_type == $ext or $accepted_file_type == "*") {
			return true;
		}
	}

	return false;
}


function xs_xhtml_escape($text) {
	return str_replace(array("&", "<", ">", "\""), array("&amp;", "&lt;", "&gt;", "&quot;"), $text);
}

function xs_urlencode($text) {
	$parts = explode("/", $text);
	$count = count($parts);

	for($i = 0; $i < $count; $i++) {
		$parts[$i] = str_replace("+", "%20", urlencode($parts[$i]));
	}

	return implode("/", $parts);
}



//Process request
$rootFolderPath = "";
$rootFilePath = "";

//Get sub-folder to browse
if (isset($_SERVER["HTTP_X_CMS_LIBRARY_PATH"])) {
	if ($_SERVER["HTTP_X_CMS_LIBRARY_PATH"] == "") {
		$rootFolderPath = XS_LIBRARY_FOLDER;
		$rootFilePath = XS_LIBRARY_FOLDER;
	} else {
		$rootFolderPath = xs_build_path(XS_LIBRARY_FOLDER, $_SERVER["HTTP_X_CMS_LIBRARY_PATH"]);
		$rootFilePath = xs_build_path(XS_LIBRARY_FOLDER, $_SERVER["HTTP_X_CMS_LIBRARY_PATH"]);
	}
} else {
	$rootFolderPath = XS_LIBRARY_FOLDER;
	$rootFilePath = XS_LIBRARY_FOLDER;
}


$hidden_folders = explode(",", XS_HIDDEN_FOLDERS);
$hidden_files = explode(",", XS_HIDDEN_FILES);



// Respond
if (get_magic_quotes_runtime() != 0) {
	set_magic_quotes_runtime(0);
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
echo "<library>";
	echo "<containers>";
		// Process folders
		$folder_list = array();
		if (file_exists($rootFolderPath)) {
			if (false !== ($handle = @opendir($rootFolderPath))) {
				while (false !== ($fs_object = readdir($handle))) {
					if ($fs_object != "." && $fs_object != "..") {
						$found = false;
						foreach($hidden_folders as $hidden_folder) {
							if(strtolower($fs_object) == strtolower(trim($hidden_folder))) {
								$found = true;
							}
						}

						if (is_dir(xs_build_path($rootFolderPath, $fs_object))) {
							if ($found === false) {
								$folder_list[] = $fs_object;
							}
						}
					}
				}
				closedir($handle);
			}
		}
		natcasesort($folder_list);
		reset($folder_list);
		foreach ($folder_list as $key => $fs_object) {
			echo "<container>";
				//Folder name
				echo "<objectName>" . xs_xhtml_escape($fs_object) . "</objectName>";

				//Path to parent folder
				echo "<path>";
					if (isset($_SERVER["HTTP_X_CMS_LIBRARY_PATH"])) {
						if ($_SERVER["HTTP_X_CMS_LIBRARY_PATH"] != "") {
							echo xs_xhtml_escape($_SERVER["HTTP_X_CMS_LIBRARY_PATH"]);
						}
					}
				echo "</path>";

				//Display label
				echo "<label>" . xs_xhtml_escape($fs_object) . "</label>";

				//Base URL to this folder
				echo "<baseURL>";
						$temp = $fs_object;

						if (isset($_SERVER["HTTP_X_CMS_LIBRARY_PATH"])) {
							if ($_SERVER["HTTP_X_CMS_LIBRARY_PATH"] != "") {
								$temp = $_SERVER["HTTP_X_CMS_LIBRARY_PATH"] . "/" . $fs_object;
							}
						}

						$url = xs_build_path(XS_BASE_URL, xs_urlencode($temp)) . "/";

						echo $url;
				echo "</baseURL>";

				//Is folder empty (not implemented yet)
				echo "<empty>false</empty>";

				//Icon ID defined in icons.xml
				echo "<icon>folder</icon>";

				//Reserved for future use
				echo "<metadata></metadata>";

				//Reserved for future use
				echo "<options>0</options>";
			echo "</container>";
		}
	echo "</containers>";
	echo "<objects>";
		// Process files
		$file_list = array();
		if (file_exists($rootFilePath)) {
			if (false !== ($handle = @opendir($rootFilePath))) {
				while (false !== ($fs_object = readdir($handle))) {
					if ($fs_object != "." && $fs_object != "..") {
						$found = false;
						foreach($hidden_files as $hidden_file) {
							if(strtolower($fs_object) == strtolower(trim($hidden_file))) {
								$found = true;
							}
						}

						if (is_file(xs_build_path($rootFilePath, $fs_object))) {
							if (xs_is_accepted_file_type($fs_object)) {
								if ($found === false) {
									$file_list[] = $fs_object;
								}
							}
						}
					}
				}
				closedir($handle);
			}
		}
		natcasesort($file_list);
		reset($file_list);
		foreach ($file_list as $key => $fs_object) {
			echo "<object>";
				//Folder name
				echo "<objectName>" . xs_xhtml_escape($fs_object) . "</objectName>";

				//Path to parent folder
				echo "<path>";
					if (isset($_SERVER["HTTP_X_CMS_LIBRARY_PATH"])) {
						if ($_SERVER["HTTP_X_CMS_LIBRARY_PATH"] != "") {
							echo xs_xhtml_escape($_SERVER["HTTP_X_CMS_LIBRARY_PATH"]);
						}
					}
				echo "</path>";

				//Display label
				echo "<label>" . xs_xhtml_escape($fs_object) . "</label>";

				//Icon ID defined in icons.xml
				echo "<icon>image</icon>";

				//Reserved for future use
				echo "<metadata></metadata>";

				//Reserved for future use
				echo "<options>0</options>";

				//Attributes
				echo "<attrs>";
					//src attribute
					echo "<attr>";
						echo "<name>src</name>";
						echo "<value>";
							if (isset($_SERVER["HTTP_X_CMS_LIBRARY_PATH"])) {
								if ($_SERVER["HTTP_X_CMS_LIBRARY_PATH"] == "") {
									echo xs_build_path(XS_BASE_URL, xs_urlencode($fs_object));
								} else {
									echo xs_build_path(xs_build_path(XS_BASE_URL, $_SERVER["HTTP_X_CMS_LIBRARY_PATH"]), xs_urlencode($fs_object));
								}
							} else {
								echo xs_build_path(XS_BASE_URL, xs_urlencode($fs_object));
							}
						echo "</value>";
					echo "</attr>";

					//Image dimensions
					if (XS_GET_IMAGE_DIMENSIONS) {
						if (false === (list($width, $height) = @getimagesize(xs_build_path($rootFilePath, $fs_object)))) {

						} else {
							//Width
							echo "<attr>";
								echo "<name>width</name>";
								echo "<value>" . $width . "</value>";
							echo "</attr>";

							//Height
							echo "<attr>";
								echo "<name>height</name>";
								echo "<value>" . $height . "</value>";
							echo "</attr>";
						}

					}
				echo "</attrs>";

				//Properties
				echo "<props>";
					//File size
					if (XS_GET_FILE_SIZE) {
						echo "<prop>";
							echo "<name>size</name>";
							echo "<value>" . filesize(xs_build_path($rootFilePath, $fs_object)) . "</value>";
						echo "</prop>";
					}

					//Last modified date
					if (XS_GET_DATE_LAST_MODIFIED) {
						echo "<prop>";
							echo "<name>date</name>";
							echo "<value>" . date("Y-m-d H:i:s", filemtime(xs_build_path($rootFilePath, $fs_object))) . "</value>";
						echo "</prop>";
					}

					//Decorative image flag
					echo "<prop>";
						echo "<name>decorative</name>";
						echo "<value>";
						if (XS_DEFAULT_IMAGE_IS_DECORATIVE) {
							echo "true";
						} else {
							echo "false";
						}
						echo "</value>";
					echo "</prop>";
				echo "</props>";
			echo "</object>";
		}
	echo "</objects>";
echo "</library>";
?>
