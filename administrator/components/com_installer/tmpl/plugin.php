<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Installer
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Static class to handle plugin view logic
 * 
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category Controller
 * @since 1.1
 */
class JInstallerExtensionTasks {

	/**
	 * @param string The URL option
	 */
	function showInstalled() {
		global $mainframe;

		/*
		 * Get a database connector
		 */
		$db = & $mainframe->getDBO();

		$query = 	"SELECT id, name, folder, element, client_id" .
					"\n FROM #__plugins" .
					"\n WHERE iscore = 0" .
					"\n ORDER BY folder, name";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		/*
		 * Get the plugin base directory
		 */
		$baseDir = JPath :: clean(JPATH_SITE.DS.'plugins');

		$numRows = count($rows);
		for ($i = 0; $i < $numRows; $i ++) {
			$row = & $rows[$i];

			/*
			 * Get the plugin xml file
			 */
			$xmlfile = $baseDir.DS.$row->folder.DS.$row->element.".xml";

			if (file_exists($xmlfile)) {

				$xmlDoc = & JFactory :: getXMLParser();
				$xmlDoc->resolveErrors(true);

				if (!$xmlDoc->loadXML($xmlfile, false, true)) {
					// Free up xml parser memory and return null
					unset ($xmlDoc);
					continue;
				}

				// Get the root document node
				$root = & $xmlDoc->documentElement;

				/*
				 * Check for a valid XML root tag.
				 * 
				 * Should be 'install', but for backward compatability we will accept 'mosinstall'.
				 */
				if ($root->getTagName() != 'install' && $root->getTagName() != 'mosinstall') {
					// Free up xml parser memory and return null
					unset ($xmlDoc);
					continue;
				}

				if ($root->getAttribute("type") != "plugin" && $root->getAttribute("type") != "mambot") {
					// Free up xml parser memory and return null
					unset ($xmlDoc);
					continue;
				}

				$element = & $root->getElementsByPath('creationDate', 1);
				$row->creationdate = $element ? $element->getText() : '';

				$element = & $root->getElementsByPath('author', 1);
				$row->author = $element ? $element->getText() : '';

				$element = & $root->getElementsByPath('copyright', 1);
				$row->copyright = $element ? $element->getText() : '';

				$element = & $root->getElementsByPath('authorEmail', 1);
				$row->authorEmail = $element ? $element->getText() : '';

				$element = & $root->getElementsByPath('authorUrl', 1);
				$row->authorUrl = $element ? $element->getText() : '';

				$element = & $root->getElementsByPath('version', 1);
				$row->version = $element ? $element->getText() : '';
			}
		}

		JInstallerScreens_plugin :: showInstalled($rows);

	}
}

/**
 * Static class to handle plugin view display
 * 
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.1
 */
class JInstallerScreens_plugin {

	/**
	 * Displays the installed non-core Plugins
	 * 
	 * @param array An array of plugin objects
	 * @return void
	 */
	function showInstalled(& $rows) {
?>
		<table class="adminheading">
		<tr>
			<td>
			<?php echo JText::_( 'DESCPLUGINS' ); ?>
			<br /><br />
			</td>
		</tr>
		</table>
		<?php


			if (count($rows)) {
?>
			<form action="index2.php" method="post" name="adminForm">
			<table class="adminlist">
			<tr>
				<th width="20%" class="title">
				<?php echo JText::_( 'Plugin' ); ?>
				</th>
				<th width="10%" class="title">
				<?php echo JText::_( 'Type' ); ?>
				</th>
				<!--
				Currently Unsupported
				<th width="10%" >
				Client
				</th>
				-->
				<th width="10%"  class="title">
				<?php echo JText::_( 'Author' ); ?>
				</th>
				<th width="5%" align="center">
				<?php echo JText::_( 'Version' ); ?>
				</th>
				<th width="10%" align="center">
				<?php echo JText::_( 'Date' ); ?>
				</th>
				<th width="15%"  class="title">
				<?php echo JText::_( 'Author Email' ); ?>
				</th>
				<th width="15%"  class="title">
				<?php echo JText::_( 'Author URL' ); ?>
				</th>
			</tr>
			<?php


				$rc = 0;
				$n = count($rows);
				for ($i = 0; $i < $n; $i ++) {
					$row = & $rows[$i];
?>
				<tr class="<?php echo "row$rc"; ?>">
					<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="eid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);">
					<span class="bold">
					<?php echo $row->name; ?>
					</span>
					</td>
					<td>
					<?php echo $row->folder; ?>
					</td>
					<!--
					Currently Unsupported
					<td>
					<?php

?>


					</td>
					-->
					<td>
					<?php echo @$row->author != '' ? $row->author : "&nbsp;"; ?>
					</td>
					<td align="center">
					<?php echo @$row->version != '' ? $row->version : "&nbsp;"; ?>
					</td>
					<td align="center">
					<?php echo @$row->creationdate != '' ? $row->creationdate : "&nbsp;"; ?>
					</td>
					<td>
					<?php echo @$row->authorEmail != '' ? $row->authorEmail : "&nbsp;"; ?>
					</td>
					<td>
					<?php echo @$row->authorUrl != "" ? "<a href=\"" .(substr( $row->authorUrl, 0, 7) == 'http://' ? $row->authorUrl : 'http://'.$row->authorUrl). "\" target=\"_blank\">$row->authorUrl</a>" : "&nbsp;";?>
					</td>
				</tr>
				<?php


					$rc = 1 - $rc;
				}
?>
			</table>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="option" value="com_installer" />
			<input type="hidden" name="extension" value="plugin" />
			</form>
			<?php


		} else {
			echo JText :: _('WARNNONONCORE');
		}
	}
}
?>
