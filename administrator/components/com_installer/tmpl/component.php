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
 * Static class to handle component view logic
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
		$db =& $mainframe->getDBO();

		$query = 	"SELECT *" .
					"\n FROM #__components" .
					"\n WHERE parent = 0" .
					"\n AND iscore = 0" .
					"\n ORDER BY name";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		/*
		 * Get the component base directory
		 */
		$baseDir = JPath :: clean (JPATH_ADMINISTRATOR .DS. 'components');
		
		$numRows = count($rows);
		for($i=0;$i < $numRows; $i++) {
			$row =& $rows[$i];
			
			/*
			 * Get the component folder and list of xml files in folder
			 */
			$folder = $baseDir . $row->option;
			$xmlFilesInDir = JFolder :: files($folder, '.xml$');

			foreach ($xmlFilesInDir as $xmlfile) {
				// Read the file to see if it's a valid component XML file
				$xmlDoc = & JFactory :: getXMLParser();
				$xmlDoc->resolveErrors(true);

				if (!$xmlDoc->loadXML($folder.DS.$xmlfile, false, true)) {
					// Free up xml parser memory and return null
					unset ($xmlDoc);
					continue;
				}

				// Get the root node of the xml document
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

				if ($root->getAttribute("type") != "component") {
					// Free up xml parser memory and return null
					unset ($xmlDoc);
					continue;
				}

				$element = & $root->getElementsByPath('creationDate', 1);
				$row->creationdate = $element ? $element->getText() : 'Unknown';

				$element = & $root->getElementsByPath('author', 1);
				$row->author = $element ? $element->getText() : 'Unknown';

				$element = & $root->getElementsByPath('copyright', 1);
				$row->copyright = $element ? $element->getText() : '';

				$element = & $root->getElementsByPath('authorEmail', 1);
				$row->authorEmail = $element ? $element->getText() : '';

				$element = & $root->getElementsByPath('authorUrl', 1);
				$row->authorUrl = $element ? $element->getText() : '';

				$element = & $root->getElementsByPath('version', 1);
				$row->version = $element ? $element->getText() : '';

				$row->jname = strtolower(str_replace(" ", "_", $row->name));
			}
		}

		JInstallerScreens_component :: showInstalled($rows);
	}

}

/**
 * Static class to handle component view display
 * 
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.1
 */
class JInstallerScreens_component {
	/**
	* @param array An array of records
	* @param string The URL option
	*/
	function showInstalled(&$rows) {
		if (count($rows)) {
?>
		<div id="treecell">
			<?php require_once(dirname(__FILE__).DS.'tree.html'); ?>
		</div>
		<div id="datacell">
			<fieldset title="<?php echo JText::_('Installed Components'); ?>">
				<legend>
					<?php echo JText::_('Installed Components'); ?>
				</legend>
				<form action="index2.php" method="post" name="adminForm">
				<table class="adminlist">
				<tr>
					<th width="20%" class="title">
					<?php echo JText::_( 'Currently Installed' ); ?>
					</th>
					<th width="20%" class="title">
					<?php echo JText::_( 'Component Menu Link' ); ?>
					</th>
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
				for ($i = 0, $n = count($rows); $i < $n; $i ++) {
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
						<?php echo @$row->link != "" ? $row->link : "&nbsp;"; ?>
						</td>
						<td>
						<?php echo @$row->author != "" ? $row->author : "&nbsp;"; ?>
						</td>
						<td align="center">
						<?php echo @$row->version != "" ? $row->version : "&nbsp;"; ?>
						</td>
						<td align="center">
						<?php echo @$row->creationdate != "" ? $row->creationdate : "&nbsp;"; ?>
						</td>
						<td>
						<?php echo @$row->authorEmail != "" ? $row->authorEmail : "&nbsp;"; ?>
						</td>
						<td>
						<?php echo @$row->authorUrl != "" ? "<a href=\"" .(substr( $row->authorUrl, 0, 7) == 'http://' ? $row->authorUrl : 'http://'.$row->authorUrl). "\" target=\"_blank\">$row->authorUrl</a>" : "&nbsp;";?>
						</td>
					</tr>
					<?php
	
					$rc = 1 - $rc;
				}
			} else {
	?>
				<td class="small">
				<?php echo JText::_( 'There are no custom components installed' ); ?>
				</td>
				<?php
	
			}
	?>
			</table>
	
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="option" value="com_installer" />
			<input type="hidden" name="extension" value="component" />
			</form>
		</fieldset>
	</div>
		<?php

	}
}
?>