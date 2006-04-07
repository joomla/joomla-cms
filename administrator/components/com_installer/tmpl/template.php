<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

//load folder filesystem class
jimport('joomla.filesystem.folder');

/**
 * Static class to handle template view logic
 * 
 * @author Louis Landry <louis.landry@joomla.org>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category Controller
 * @since 1.5
 */
class JInstallerExtensionTasks {

	/**
	* @param string The URL option
	*/
	function showInstalled() {
		global $mainframe;
		
		$db			= & $mainframe->getDBO();
		$option		= JRequest::getVar( 'option' );
		$filter 	= $mainframe->getUserStateFromRequest( "$option.template.filter", 'filter', -1 );
		$limit 		= $mainframe->getUserStateFromRequest( 'limit', 'limit', $mainframe->getCfg('list_limit') );
		$limitstart = $mainframe->getUserStateFromRequest( "$option.limitstart", 'limitstart', 0 );

		$select[] 			= mosHTML::makeOption('-1', JText::_('All'));
		$select[] 			= mosHTML::makeOption('0', JText::_('Site Templates'));
		$select[] 			= mosHTML::makeOption('1', JText::_('Admin Templates'));
		$lists['filter'] 	= mosHTML::selectList($select, 'filter', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $filter);

		if ($filter == '-1') {
			$client = 'all';
			// Get the site templates
			$templateDirs = JFolder::folders(JPATH_SITE.DS.'templates');
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 0;
				$template->baseDir = JPATH_SITE.DS.'templates';
				
				$templates[] = $template;				
			}			
			// Get the admin templates
			$templateDirs = JFolder::folders(JPATH_ADMINISTRATOR.DS.'templates');
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 1;
				$template->baseDir = JPATH_ADMINISTRATOR.DS.'templates';
				
				$templates[] = $template;				
			}			
		} elseif ($filter == '0') {
			$client = 'site';
			$templateDirs = JFolder::folders(JPATH_SITE.DS.'templates');
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 0;
				$template->baseDir = JPATH_SITE.DS.'templates';
				
				$templates[] = $template;				
			}			
		} elseif ($filter == '1') {
			$client = 'administrator';
			$templateDirs = JFolder::folders(JPATH_ADMINISTRATOR.DS.'templates');
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 1;
				$template->baseDir = JPATH_ADMINISTRATOR.DS.'templates';
				
				$templates[] = $template;				
			}			
		}
		
		/*
		 * Get a list of currently used templates
		 */
		$query = "SELECT template " .
				"\nFROM #__templates_menu " .
				"\nWHERE 1";
		$db->setQuery($query);
		$currentList = $db->loadResultArray();

		$rows = array();
		$rowid = 0;
		// Check that the directory contains an xml file
		foreach($templates as $template) 
		{
			$dirName = $template->baseDir .DS. $template->folder;
			$xmlFilesInDir = JFolder::files($dirName,'.xml$');
	
			foreach($xmlFilesInDir as $xmlfile) 
			{
				$data = JApplicationHelper::parseXMLInstallFile($dirName . DS. $xmlfile);
				
				$row = new StdClass();
				$row->id 		= $rowid;
				$row->client_id	= $template->client;
				$row->directory = $template->folder;
				$row->baseDir   = $template->baseDir;
				
				foreach($data as $key => $value) {
					$row->$key = $value;
				}
				
				$row->checked_out = 0;
				$row->jname = JString::strtolower( str_replace( ' ', '_', $row->name ) );
	
				$rows[] = $row;
				$rowid++;
			}
		}
	
		/*
		 * Take care of the pagination
		 */	
		jimport('joomla.presentation.pagination');
		$page = new JPagination( count( $rows ), $limitstart, $limit );
		$rows = array_slice( $rows, $page->limitstart, $page->limit );
	
		JInstallerScreens_template::showInstalled($rows, $page, $client, $lists, $currentList);
	}

}

/**
 * Static class to handle template view display
 * 
 * @author Louis Landry <louis.landry@joomla.org>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.5
 */
class JInstallerScreens_template 
{
	function showInstalled( &$rows, &$page, $client, $lists, &$currentList ) 
	{	
		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">
				
		<div id="pane-navigation">
			<?php require_once(dirname(__FILE__).DS.'navigation.html'); ?>
		</div>
		
		<div id="pane-document">
			<fieldset title="<?php echo JText::_('Installed Templates'); ?>">
				<legend>
					<?php echo JText::_('Installed Templates'); ?>
				</legend>
				
				<table class="adminform">
				<tr>
					<td width="100%">
						<?php echo JText::_( 'DESCTEMPLATES' ); ?>
					</td>
					<td align="right">
						<?php echo $lists['filter'];?>
					</td>
				</tr>
				</table>
		
			<?php
			if (count($rows)) {
				?>
				<table class="adminlist">					
				<tr>
					<th class="title" width="10">
						<?php echo JText::_( 'Num' ); ?>
					</th>
					<th class="title">
						<?php echo JText::_( 'Template' ); ?>
					</th>
					<th width="7%" align="center">
						<?php echo JText::_( 'Client' ); ?>
					</th>
					<th width="10%" align="center">
						<?php echo JText::_( 'Version' ); ?>
					</th>
					<th width="15%">
						<?php echo JText::_( 'Date' ); ?>
					</th>
						<th width="25%"  class="title">
						<?php echo JText::_( 'Author' ); ?>
					</th>
				</tr>
				<?php
				$rc = 0;
				for ($i = 0, $n = count( $rows ); $i < $n; $i++) {
					$row =& $rows[$i];
					
					/*
					 * Handle currently used templates
					 */
					if (in_array($row->directory, $currentList)) {
						$cbd 	= 'disabled';
						$style 	= 'style="color:#999999;"';
					} else {
						$cbd 	= '';
						$style 	= '';
					}
					
					$author_info = @$row->authorEmail .'<br />'. @$row->authorUrl;
					?>
					<tr class="<?php echo "row$rc"; ?>" <?php echo $style; ?>>
						<td>
							<?php echo $page->rowNumber( $i ); ?>
						</td>
						<td>
							<input type="checkbox" id="cb<?php echo $i;?>" name="eid[]" value="<?php echo $row->baseDir.DS.$row->directory; ?>" onclick="isChecked(this.checked);" <?php echo $cbd; ?> />
							<input type="hidden" name="eclient[]" value="<?php echo $row->client_id; ?>" />
							<span class="bold"><?php echo $row->name; ?></span>
						</td>
						<td align="center">
							<?php echo $row->client_id == 0 ? JText::_( 'Site' ) : JText::_( 'Admin' ); ?>
						</td>
						<td align="center">
							<?php echo @$row->version != '' ? $row->version : '&nbsp;'; ?>
						</td>
						<td>
							<?php echo @$row->creationdate != '' ? $row->creationdate : '&nbsp;'; ?>
						</td>
						<td>
							<span onmouseover="return overlib('<?php echo $author_info; ?>', CAPTION, '<?php echo JText::_( 'Author Information' ); ?>', BELOW, LEFT);" onmouseout="return nd();">
								<?php echo @$row->author != '' ? $row->author : '&nbsp;'; ?>										
							</span>
						</td>
					</tr>
					<?php
					$rc = $rc == 0 ? 1 : 0;
				}
				?>
				</table>
				<?php echo $page->getListFooter(); ?>			
				<?php
			} else {
				echo JText::_('No Installed Templates');
			}
			?>
			</fieldset>
		</div>
		
		<input type="hidden" name="extension" value="template" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_installer" />
		<input type="hidden" name="client" value="<?php echo $client;?>" />
		</form>
		<?php
	}
}
?>