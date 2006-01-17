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
 * Static class to handle template view logic
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
	function showInstalled() 
	{
		global $mainframe;
		
		$option		= JRequest::getVar( 'option' );
		$filter 	= $mainframe->getUserStateFromRequest( "$option.template.filter", 'filter', '-1' );
		$limit 		= $mainframe->getUserStateFromRequest( 'limit', 'limit', $mainframe->getCfg('list_limit') );
		$limitstart = $mainframe->getUserStateFromRequest( "$option.limitstart", 'limitstart', 0 );

		$select[] 			= mosHTML :: makeOption('-1', JText :: _('All'));
		$select[] 			= mosHTML :: makeOption('0', JText :: _('Site Templates'));
		$select[] 			= mosHTML :: makeOption('1', JText :: _('Admin Templates'));
		$lists['filter'] 	= mosHTML :: selectList($select, 'filter', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $filter);

		if ($filter == '-1') {
			$client = 'all';
			// Get the site templates
			$templateDirs = JFolder::folders(JPATH_SITE.'/templates');
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 0;
				$template->baseDir = JPATH_SITE.'/templates';
				
				$templates[] = $template;				
			}			
			// Get the admin templates
			$templateDirs = JFolder::folders(JPATH_ADMINISTRATOR.'/templates');
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 1;
				$template->baseDir = JPATH_ADMINISTRATOR.'/templates';
				
				$templates[] = $template;				
			}			
		} elseif ($filter == '0') {
			$client = 'site';
			$templateDirs = JFolder::folders(JPATH_SITE.'/templates');
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 0;
				$template->baseDir = JPATH_SITE.'/templates';
				
				$templates[] = $template;				
			}			
		} elseif ($filter == '1') {
			$client = 'administrator';
			$templateDirs = JFolder::folders(JPATH_ADMINISTRATOR.'/templates');
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 1;
				$template->baseDir = JPATH_ADMINISTRATOR.'/templates';
				
				$templates[] = $template;				
			}			
		}
		
		$rows = array();
		$rowid = 0;
		// Check that the directory contains an xml file
		foreach($templates as $template) {
			$dirName = JPath::clean($template->baseDir .DS. $template->folder);
			$xmlFilesInDir = JFolder::files($dirName,'.xml$');
	
			foreach($xmlFilesInDir as $xmlfile) {
				// Read the file to see if it's a valid template XML file
				$xmlDoc =& JFactory::getXMLParser();
				$xmlDoc->resolveErrors( true );
				if (!$xmlDoc->loadXML( $dirName . $xmlfile, false, true )) {
					continue;
				}
	
				$root = &$xmlDoc->documentElement;
	
				if ($root->getTagName() != 'mosinstall' && $root->getTagName() != 'install') {
					continue;
				}
				if ($root->getAttribute( 'type' ) != 'template') {
					continue;
				}
	
				$row = new StdClass();
				$row->id 		= $rowid;
				$row->client_id	= $template->client;
				$row->directory = $template->folder;
				$element 		= &$root->getElementsByPath('name', 1 );
				$row->name 		= $element->getText();
	
				$element 		= &$root->getElementsByPath('creationDate', 1);
				$row->creationdate = $element ? $element->getText() : 'Unknown';
	
				$element 		= &$root->getElementsByPath('author', 1);
				$row->author 	= $element ? $element->getText() : 'Unknown';
	
				$element 		= &$root->getElementsByPath('copyright', 1);
				$row->copyright = $element ? $element->getText() : '';
	
				$element 		= &$root->getElementsByPath('authorEmail', 1);
				$row->authorEmail = $element ? $element->getText() : '';
	
				$element 		= &$root->getElementsByPath('authorUrl', 1);
				$row->authorUrl = $element ? $element->getText() : '';
	
				$element 		= &$root->getElementsByPath('version', 1);
				$row->version 	= $element ? $element->getText() : '';
	
				$row->checked_out = 0;
				$row->jname = strtolower( str_replace( ' ', '_', $row->name ) );
	
				$rows[] = $row;
				$rowid++;
				
				unset($xmlDoc,$root);
			}
		}
	
		/*
		 * Take care of the pagination
		 */	
		jimport('joomla.pagination');
		$page = new JPagination( count( $rows ), $limitstart, $limit );
		$rows = array_slice( $rows, $page->limitstart, $page->limit );
	
		JInstallerScreens_template :: showInstalled($rows, $page, $client, $lists);
	}

}

/**
 * Static class to handle template view display
 * 
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.1
 */
class JInstallerScreens_template {
	
	function showInstalled( &$rows, &$page, $client, $lists ) {
		if (count($rows)) {
		global $my;

		?>
		<div id="treecell">
			<?php require_once(dirname(__FILE__).DS.'tree.html'); ?>
		</div>
		<div id="datacell">
			<fieldset title="<?php echo JText::_('Installed Templates'); ?>">
				<legend>
					<?php echo JText::_('Installed Templates'); ?>
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
				<?php echo JText::_( 'DESCTEMPLATES' ); ?>
				<br /><br />
				</td>
			</tr>
			</table>
	
			<table class="adminlist">
				<tr>
					<th width="20%" class="title">
					<?php echo JText::_( 'Template' ); ?>
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
						<input type="checkbox" id="cb<?php echo $i;?>" name="eid[]" value="<?php echo $row->directory; ?>" onclick="isChecked(this.checked);"><span class="bold"><?php echo $row->name; ?></span></td>
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
			?>
			</table>
			<?php echo $page->getListFooter(); ?>
	
			<input type="hidden" name="extension" value="template" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="option" value="com_installer" />
			<input type="hidden" name="client" value="<?php echo $client;?>" />
			</form>
		</fieldset>
	</div>
		<?php
		}
	}
}
?>