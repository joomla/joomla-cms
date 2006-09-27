<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Installer
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
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
	function showInstalled()
	{
		global $mainframe, $option;

		$filter 	= JRequest::getVar( 'filter', '' );
		$limit		= $mainframe->getUserStateFromRequest("$option.limit", 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		if ($filter == NULL) {
			$and = '';
		} else {
			$and = "\n WHERE folder = '$filter'";
		}

		// Get a database connector
		$db = & JFactory::getDBO();

		$query = 	"SELECT id, name, folder, element, client_id, iscore"
					. "\n FROM #__plugins"
					. $and
					. "\n ORDER BY iscore, folder, name"
					;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		/*
		 * Get the plugin base directory
		 */
		$baseDir = JPATH_SITE.DS.'plugins';

		$numRows = count($rows);
		for ($i = 0; $i < $numRows; $i ++) {
			$row = & $rows[$i];

			/*
			 * Get the plugin xml file
			 */
			$xmlfile = $baseDir.DS.$row->folder.DS.$row->element.".xml";

			if (file_exists($xmlfile))
			{
				if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}
			}
		}

		// get list of Positions for dropdown filter
		$query = "SELECT folder AS value, folder AS text"
		. "\n FROM #__plugins"
		. "\n GROUP BY folder"
		. "\n ORDER BY folder"
		;
		$types[] = mosHTML::makeOption( '', JText::_( 'All' ) );
		$db->setQuery( $query );
		$types = array_merge( $types, $db->loadObjectList() );
		$lists['filter'] = mosHTML::selectList( $types, 'filter', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter );

		/*
		* Take care of the pagination
		*/
		jimport('joomla.presentation.pagination');
		$page = new JPagination( count( $rows ), $limitstart, $limit );
		$rows = array_slice( $rows, $page->limitstart, $page->limit );

		JInstallerScreens_plugin::showInstalled($rows, $lists, $page);

	}
}

/**
 * Static class to handle plugin view display
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.5
 */
class JInstallerScreens_plugin {

	/**
	 * Displays the installed non-core Plugins
	 *
	 * @param array An array of plugin objects
	 * @return void
	 */
	function showInstalled(&$rows, &$lists, &$page) {

		mosCommonHTML::loadOverlib();
		?>
		<form action="index.php?option=com_installer&amp;extension=plugin" method="post" name="adminForm">

				<table class="adminform">
				<tr>
					<td width="100%">
						<?php echo JText::_( 'DESCPLUGINS' ); ?>
					</td>
					<td align="right">
						<?php echo $lists['filter'];?>
					</td>
				</tr>
				</table>

			<?php
			if (count($rows)) {
				?>
				<table class="adminlist" cellspacing="1">
				<thead>
				<tr>
					<th class="title" width="2">
						<?php echo JText::_( 'Num' ); ?>
					</th>
					<th class="title">
						<?php echo JText::_( 'Plugin' ); ?>
					</th>
					<th width="10%" class="title">
						<?php echo JText::_( 'Type' ); ?>
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
				</thead>
				<tfoot>
					<td colspan="6">
					<?php echo $page->getListFooter(); ?>
					</td>
				</tfoot>
				<tbody>
				<?php
				$rc = 0;
				$n = count($rows);
				for ($i = 0; $i < $n; $i ++) {
					$row = & $rows[$i];

					/*
					 * Handle currently used templates
					 */
					if ($row->iscore)	{
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
							<?php echo $page->getRowOffset( $i ); ?>
						</td>
						<td>
							<input type="checkbox" id="cb<?php echo $i;?>" name="eid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" <?php echo $cbd; ?> />
							<span class="bold">
								<?php echo $row->name; ?>
							</span>
						</td>
						<td>
							<?php echo $row->folder; ?>
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
					$rc = 1 - $rc;
				}
				?>
				</tbody>
				</table>
				<?php
			} else {
				echo JText::_('WARNNONONCORE');
			}
			?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_installer" />
		<input type="hidden" name="extension" value="plugin" />
		</form>
		<?php
	}
}
?>