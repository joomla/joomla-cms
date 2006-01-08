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
	function showInstalled() {
		global $mainframe;
		
		$client		= mosGetParam( $_REQUEST, 'client', 'site');

		$filter = mosGetParam($_POST, 'filter', '');
		$select[] = mosHTML :: makeOption('all', JText :: _('All'));
		$select[] = mosHTML :: makeOption('site', JText :: _('Site Templates'));
		$select[] = mosHTML :: makeOption('administrator', JText :: _('Admin Templates'));
		$lists['filter'] = mosHTML :: selectList($select, 'filter', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $filter);

		if ($filter != '' && $filter != 'all') {
			$client = $filter;	
		}
		
		$limit = $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg('list_limit') );
		$limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	
		
		$templateBaseDir = JPath::clean( constant('JPATH_'.strtoupper($client)) . '/templates' );
	
		$rows = array();
		// Read the template dir to find templates
		$templateDirs		= JFolder::folders($templateBaseDir);
	
	
		$rowid = 0;
		// Check that the directory contains an xml file
		foreach($templateDirs as $templateDir) {
			$dirName = JPath::clean($templateBaseDir . $templateDir);
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
				$row->directory = $templateDir;
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
			<th width="5%" class="title"><?php echo JText::_( 'Num' ); ?></th>
			<th width="5%">&nbsp;</th>
			<th width="25%" class="title">
			<?php echo JText::_( 'Name' ); ?>
			</th>
			<th width="20%"  class="title">
			<?php echo JText::_( 'Author' ); ?>
			</th>
			<th width="5%" align="center">
			<?php echo JText::_( 'Version' ); ?>
			</th>
			<th width="10%" align="center">
			<?php echo JText::_( 'Date' ); ?>
			</th>
			<th width="20%"  class="title">
			<?php echo JText::_( 'Author URL' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ( $i=0, $n = count( $rows ); $i < $n; $i++ ) {
			$row = &$rows[$i];
			?>
			<tr class="<?php echo 'row'. $k; ?>">
				<td>
				<?php echo $page->rowNumber( $i ); ?>
				</td>
				<td>
				<?php
				if ( $row->checked_out && $row->checked_out != $my->id ) {
					?>
					&nbsp;
					<?php
				} else {
					?>
					<input type="radio" id="cb<?php echo $i;?>" name="eid[]" value="<?php echo $row->directory; ?>" onClick="isChecked(this.checked);" />
					<?php
				}
				?>
				</td>
				<td>
				<?php echo $row->name;?>
				</td>
				<td>
				<?php echo $row->authorEmail ? '<a href="mailto:'. $row->authorEmail .'">'. $row->author .'</a>' : $row->author; ?>
				</td>
				<td align="center">
				<?php echo $row->version; ?>
				</td>
				<td align="center">
				<?php echo $row->creationdate; ?>
				</td>
				<td>
				<a href="<?php echo substr( $row->authorUrl, 0, 7) == 'http://' ? $row->authorUrl : 'http://'.$row->authorUrl; ?>" target="_blank">
				<?php echo $row->authorUrl; ?>
				</a>
				</td>
			</tr>
			<?php
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
		<?php
		}
	}
}
?>