<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();

if ($app->isSite())
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

require_once JPATH_ROOT . '/components/com_content/helpers/route.php';

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$extension = $this->escape($this->state->get('filter.extension'));
$function  = $app->input->getCmd('function', 'jSelectCategory');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_categories&view=categories&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="filter clearfix">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<hr class="hr-condensed" />
	</fieldset>

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped" id="categoryList">
			<thead>
				<tr>
					<th>
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php
						echo JHtml::_(
							'grid.sort',
							'JGRID_HEADING_LANGUAGE',
							'language',
							$this->state->get('list.direction'),
							$this->state->get('list.ordering')
						);
						?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="15">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<?php if ($item->language && JLanguageMultilang::isEnabled())
					{
						$tag = strlen($item->language);
						if ($tag == 5)
						{
							$lang = substr($item->language, 0, 2);
						}
						elseif ($tag == 6)
						{
							$lang = substr($item->language, 0, 3);
						}
						else
						{
							$lang = "";
						}
					}
					elseif (!JLanguageMultilang::isEnabled())
					{
						$lang = "";
					}
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php echo str_repeat('<span class="gi">&mdash;</span>', $item->level - 1) ?>
							<a href="javascript:void()" onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>', null, '<?php echo $this->escape(ContentHelperRoute::getCategoryRoute($item->id, $item->language)); ?>', '<?php echo $this->escape($lang); ?>', null);">
								<?php echo $this->escape($item->title); ?>
							</a>
						</td>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="center nowrap">
							<?php if ($item->language == '*'): ?>
								<?php echo JText::alt('JALL', 'language'); ?>
							<?php else: ?>
								<?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
							<?php endif; ?>
						</td>
						<td class="center hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<input type="hidden" name="extension" value="<?php echo $extension; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>

</form>
