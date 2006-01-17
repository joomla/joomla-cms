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
 * Static class to handle module view logic
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
		global $database;

		$filter 			= JRequest::getVar( 'filter' );
		$select[] 			= mosHTML :: makeOption('', JText :: _('All'));
		$select[] 			= mosHTML :: makeOption('0', JText :: _('Site Modules'));
		$select[] 			= mosHTML :: makeOption('1', JText :: _('Admin Modules'));
		$lists['filter'] 	= mosHTML :: selectList($select, 'filter', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $filter);
		if ($filter == NULL) {
			$and = '';
		} else
			if (!$filter) {
				$and = "\n AND client_id = 0";
			} else
				if ($filter) {
					$and = "\n AND client_id = 1";
				}

		$query = "SELECT id, module, client_id"."\n FROM #__modules"."\n WHERE module LIKE 'mod_%' AND iscore='0'".$and."\n GROUP BY module, client_id"."\n ORDER BY client_id, module";
		$database->setQuery($query);
		$rows = $database->loadObjectList();

		$n = count($rows);
		for ($i = 0; $i < $n; $i ++) {
			$row = & $rows[$i];

			// path to module directory
			if ($row->client_id == "1") {
				$moduleBaseDir = JPath :: clean(JPath :: clean(JPATH_ADMINISTRATOR)."modules");
			} else {
				$moduleBaseDir = JPath :: clean(JPath :: clean(JPATH_SITE)."modules");
			}

			// xml file for module
			$xmlfile = $moduleBaseDir."/".$row->module.".xml";

			if (file_exists($xmlfile)) {
				$xmlDoc = & JFactory :: getXMLParser();
				$xmlDoc->resolveErrors(true);
				if (!$xmlDoc->loadXML($xmlfile, false, true)) {
					continue;
				}

				$root = & $xmlDoc->documentElement;

				if ($root->getTagName() != 'mosinstall' && $root->getTagName() != 'install') {
					continue;
				}
				if ($root->getAttribute("type") != "module") {
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

		JInstallerScreens_module :: showInstalled($rows, $lists);
	}

}

/**
 * Static class to handle module view display
 * 
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.1
 */
class JInstallerScreens_module {
	
	function showInstalled( &$rows, &$lists ) {
		if (count($rows)) {
			?>
		<div id="treecell">
			<?php require_once(dirname(__FILE__).DS.'tree.html'); ?>
		</div>
		<div id="datacell">
			<fieldset title="<?php echo JText::_('Installed Modules'); ?>">
				<legend>
					<?php echo JText::_('Installed Modules'); ?>
				</legend>
				<form action="index2.php" method="post" name="adminForm">
				<table class="adminheading">
				<tr>
					<td>
					<?php echo JText::_( 'Filter' ); ?>:
					</td>
					<td align="right">
					<?php echo $lists['filter'];?>
					</td>
				</tr>
				<tr>
					<td colspan="3">
					<?php echo JText::_( 'DESCMODULES' ); ?>
					<br /><br />
					</td>
				</tr>
				</table>
	
				<table class="adminlist">
				<tr>
					<th width="20%" class="title">
					<?php echo JText::_( 'Module File' ); ?>
					</th>
					<th width="10%"  class="title">
					<?php echo JText::_( 'Client' ); ?>
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
				for ($i = 0, $n = count( $rows ); $i < $n; $i++) {
					$row =& $rows[$i];
					?>
					<tr class="<?php echo "row$rc"; ?>">
						<td>
						<input type="checkbox" id="cb<?php echo $i;?>" name="eid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);"><span class="bold"><?php echo $row->module; ?></span></td>
						<td>
						<?php echo $row->client_id == "0" ? JText::_( 'Site' ) : JText::_( 'Administrator' ); ?>
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
						<?php echo @$row->authorUrl != "" ? "<a href=\"" .(substr( $row->authorUrl, 0, 7) == 'http://' ? $row->authorUrl : 'http://'.$row->authorUrl) ."\" target=\"_blank\">$row->authorUrl</a>" : "&nbsp;"; ?>
						</td>
					</tr>
					<?php
					$rc = $rc == 0 ? 1 : 0;
				}
			} else {
				?>
				<td class="small">
				<?php echo JText::_( 'No custom modules installed' ); ?>
				</td>
				<?php
			}
			?>
			</table>
	
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="option" value="com_installer" />
			<input type="hidden" name="extension" value="module" />
			</form>
		</fieldset>
	</div>
		<?php
	}
}
?>
