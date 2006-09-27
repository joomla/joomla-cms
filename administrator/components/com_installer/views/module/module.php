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
 * Static class to handle module view logic
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

		$db			=& JFactory::getDBO();
		$filter 	= JRequest::getVar( 'filter' );
		$limit		= $mainframe->getUserStateFromRequest("$option.limit", 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		$select[] 			= mosHTML::makeOption('', JText::_('All'));
		$select[] 			= mosHTML::makeOption('0', JText::_('Site Modules'));
		$select[] 			= mosHTML::makeOption('1', JText::_('Admin Modules'));
		$lists['filter'] 	= mosHTML::selectList($select, 'filter', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $filter);

		if ($filter == NULL) {
			$and = '';
		} else {
			if (!$filter) {
				$and = "\n AND client_id = 0";
			} else {
				if ($filter) {
					$and = "\n AND client_id = 1";
				}
			}
		}

		$query = "SELECT id, module, client_id, title, iscore"
				. "\n FROM #__modules"
				. "\n WHERE module LIKE 'mod_%' "
				. $and
				. "\n GROUP BY module, client_id"
				. "\n ORDER BY iscore, client_id, module"
				;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$n = count($rows);
		for ($i = 0; $i < $n; $i ++) {
			$row = & $rows[$i];

			// path to module directory
			if ($row->client_id == "1") {
				$moduleBaseDir = JPATH_ADMINISTRATOR.DS."modules";
			} else {
				$moduleBaseDir = JPATH_SITE.DS."modules";
			}

			// xml file for module
			$xmlfile = $moduleBaseDir . DS . $row->module .DS. $row->module.".xml";

			if (file_exists($xmlfile))
			{
				if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}
			}
		}

		/*
		* Take care of the pagination
		*/
		jimport('joomla.html.pagination');
		$page = new JPagination( count( $rows ), $limitstart, $limit );
		$rows = array_slice( $rows, $page->limitstart, $page->limit );

		JInstallerScreens_module::showInstalled($rows, $lists, $page);
	}

}

/**
 * Static class to handle module view display
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.5
 */
class JInstallerScreens_module {

	function showInstalled( &$rows, &$lists, &$page ) {

		mosCommonHTML::loadOverlib();
		?>
		<form action="index.php?option=com_installer&amp;extension=module" method="post" name="adminForm">

				<table class="adminform">
				<tr>
					<td width="100%">
						<?php echo JText::_( 'DESCMODULES' ); ?>
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
						<?php echo JText::_( 'Module File' ); ?>
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
				</thead>
				<tfoot>
					<td colspan="6">
					<?php echo $page->getListFooter(); ?>
					</td>
				</tfoot>
				<tbody>
				<?php
				$rc = 0;
				for ($i = 0, $n = count( $rows ); $i < $n; $i++) {
					$row =& $rows[$i];

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
							<span class="bold"><?php echo $row->module; ?></span>
						</td>
						<td align="center">
							<?php echo $row->client_id == "0" ? JText::_( 'Site' ) : JText::_( 'Admin' ); ?>
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
				</tbody>
				</table>
				<?php
			} else {
				echo JText::_( 'No custom modules installed' );
			}
			?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_installer" />
		<input type="hidden" name="extension" value="module" />
		</form>
		<?php
	}
}
?>