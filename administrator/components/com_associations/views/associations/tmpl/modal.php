<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();

if ($app->isClient('site'))
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$function   = $app->input->getCmd('function', 'jSelectAssociation');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$colSpan    = 4;
$iconStates = array(
	-2 => 'icon-trash',
	0  => 'icon-unpublish',
	1  => 'icon-publish',
	2  => 'icon-archive',
);

$app->getDocument()->addScriptDeclaration(
	"jQuery(document).ready(function($) {
		// Run function on parent window.
		$('.select-link').on('click', function() {
			if (self != top)
			{
				window.parent." . $function . "(this.getAttribute('data-id'));
			}
		});
	});"
);
?>
<form action="<?php echo JRoute::_('index.php?option=com_associations&view=associations&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1');
 ?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="col-md-2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="col-md-10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped" id="associationsList">
			<thead>
				<tr>
					<?php if (!empty($this->typeSupports['state'])) : ?>
						<th width="1%" class="center nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'state', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap">
						<?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?>
					</th>
					<th width="5%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_ASSOCIATIONS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
					</th>
					<?php if (!empty($this->typeFields['menutype'])) : ?>
						<th width="10%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_ASSOCIATIONS_HEADING_MENUTYPE', 'menutype_title', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<?php if (!empty($this->typeSupports['acl'])) : ?>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $colSpan; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<?php if (!empty($this->typeSupports['state'])) : ?>
						<td class="center">
							<span class="<?php echo $iconStates[$this->escape($item->state)]; ?>"></span>
						</td>
					<?php endif; ?>
					<td class="nowrap has-context">
						<?php if (isset($item->level)) : ?>
							<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
						<?php endif; ?>
						<a class="select-link" href="javascript:void(0);" data-id="<?php echo $item->id; ?>"><?php echo $this->escape($item->title); ?></a>
						<?php if (!empty($this->typeFields['alias'])) : ?>
							<span class="small">
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
							</span>
						<?php endif; ?>
						<?php if (!empty($this->typeFields['catid'])) : ?>
							<div class="small">
								<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
							</div>
						<?php endif; ?>
					</td>
					<td class="small">
						<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
					</td>
					<td>
						<?php if (true || $item->association) : ?>
							<?php echo AssociationsHelper::getAssociationHtmlList($this->extensionName, $this->typeName, (int) $item->id, $item->language, false, false); ?>
						<?php endif; ?>
					</td>
					<?php if (!empty($this->typeFields['menutype'])) : ?>
						<td class="small">
							<?php echo $this->escape($item->menutype_title); ?>
						</td>
					<?php endif; ?>
					<?php if (!empty($this->typeSupports['acl'])) : ?>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
					<?php endif; ?>
					<td class="hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="forcedItemType" value="<?php echo $app->input->get('forcedItemType', '', 'string'); ?>">
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'cmd'); ?>">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
