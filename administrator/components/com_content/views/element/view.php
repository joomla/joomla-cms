<?php
/**
 * @version		$Id: view.php 11625 2009-02-15 15:32:42Z kdevine $
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML Article Element View class for the Content component
 *
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewElement extends JView
{
	function display()
	{
		global $mainframe;

		// Initialize variables
		$db			= &JFactory::getDbo();
		$nullDate	= $db->getNullDate();

		$document	= & JFactory::getDocument();
		$document->setTitle(JText::_('Article Selection'));

		JHtml::_('behavior.modal');

		$template = $mainframe->getTemplate();
		$document->addStyleSheet("templates/$template/css/general.css");

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$lists = $this->_getLists();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'section_name' && $lists['order_Dir'] == 'ASC');

		$rows = &$this->get('List');
		$page = &$this->get('Pagination');
		JHtml::_('behavior.tooltip');
		?>
		<form action="index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object=id" method="post" name="adminForm">

			<table>
				<tr>
					<td width="100%">
						<?php echo JText::_('Filter'); ?>:
						<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
						<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
						<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
					</td>
					<td nowrap="nowrap">
						<?php
						echo $lists['sectionid'];
						echo $lists['catid'];
						?>
					</td>
				</tr>
			</table>

			<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th width="5">
						<?php echo JText::_('Num'); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort',   'Title', 'c.title', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="7%">
						<?php echo JHtml::_('grid.sort',   'Access', 'groupname', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="2%" class="title">
						<?php echo JHtml::_('grid.sort',   'ID', 'c.id', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th class="title" width="15%" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort',   'Section', 'section_name', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th  class="title" width="15%" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort',   'Category', 'cc.title', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th align="center" width="10">
						<?php echo JHtml::_('grid.sort',   'Date', 'c.created', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $page->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count($rows); $i < $n; $i++)
			{
				$row = &$rows[$i];

				$link 	= '';
				$date	= JHtml::_('date',  $row->created, JText::_('DATE_FORMAT_LC4'));
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $page->getRowOffset($i); ?>
					</td>
					<td>
						<a style="cursor: pointer;" onclick="window.parent.jSelectArticle('<?php echo $row->id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""),$row->title); ?>', '<?php echo JRequest::getVar('object'); ?>');">
							<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8'); ?></a>
					</td>
					<td align="center">
						<?php echo $row->groupname;?>
					</td>
					<td>
						<?php echo $row->id; ?>
					</td>
						<td>
							<?php echo $row->section_name; ?>
						</td>
					<td>
						<?php echo $row->cctitle; ?>
					</td>
					<td nowrap="nowrap">
						<?php echo $date; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			</table>

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
		</form>
		<?php
	}

	function _getLists()
	{
		global $mainframe;

		// Initialize variables
		$db		= &JFactory::getDbo();

		// Get some variables from the request
		$sectionid			= JRequest::getVar('sectionid', -1, '', 'int');
		$redirect			= $sectionid;
		$option				= JRequest::getCmd('option');
		$filter_order		= $mainframe->getUserStateFromRequest('articleelement.filter_order',		'filter_order',		'',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest('articleelement.filter_order_Dir',	'filter_order_Dir',	'',	'word');
		$filter_state		= $mainframe->getUserStateFromRequest('articleelement.filter_state',		'filter_state',		'',	'word');
		$catid				= $mainframe->getUserStateFromRequest('articleelement.catid',				'catid',			0,	'int');
		$filter_authorid	= $mainframe->getUserStateFromRequest('articleelement.filter_authorid',		'filter_authorid',	0,	'int');
		$filter_sectionid	= $mainframe->getUserStateFromRequest('articleelement.filter_sectionid',	'filter_sectionid',	-1,	'int');
		$limit				= $mainframe->getUserStateFromRequest('global.list.limit',					'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart			= $mainframe->getUserStateFromRequest('articleelement.limitstart',			'limitstart',		0,	'int');
		$search				= $mainframe->getUserStateFromRequest('articleelement.search',				'search',			'',	'string');
		$search				= JString::strtolower($search);

		// get list of categories for dropdown filter
		$filter = ($filter_sectionid >= 0) ? ' WHERE cc.section = '.$db->Quote($filter_sectionid) : '';

		// get list of categories for dropdown filter
		$query = 'SELECT cc.id AS value, cc.title AS text, section' .
				' FROM #__categories AS cc' .
				' INNER JOIN #__sections AS s ON s.id = cc.section' .
				$filter .
				' ORDER BY s.ordering, cc.ordering';

		$lists['catid'] = ContentHelper::filterCategory($query, $catid);

		// get list of sections for dropdown filter
		$javascript = 'onchange="document.adminForm.submit();"';
		$lists['sectionid'] = JHtml::_('list.section', 'filter_sectionid', $filter_sectionid, $javascript);

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search'] = $search;

		return $lists;
	}
}
