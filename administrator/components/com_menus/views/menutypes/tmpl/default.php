<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
// Checking if loaded via index.php or component.php
$tmpl = $input->getCmd('tmpl', '');
$document = JFactory::getDocument();
?>

<script type="text/javascript">
	setmenutype = function(type)
	{
		<?php if ($tmpl) : ?>
			window.parent.Joomla.submitbutton('item.setType', type);
			window.parent.SqueezeBox.close();
		<?php else : ?>
			window.location="index.php?option=com_menus&view=item&task=item.setType&layout=edit&type="+('item.setType', type);
		<?php endif; ?>
	}
</script>

<?php echo JHtml::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'slide1')); ?>
	<?php
		$i = 0;
		foreach ($this->types as $name => $list) : ?>
		<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', JText::_($name), 'collapse' . $i++); ?>
			<ul class="nav nav-tabs nav-stacked">
				<?php foreach ($list as $item) : ?>
					<li>
						<a class="choose_type" href="#" title="<?php echo JText::_($item->description); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => $item->title, 'request' => $item->request))); ?>')">
							<?php echo JText::_($item->title);?> <small class="muted"><?php echo JText::_($item->description); ?></small>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', JText::_('COM_MENUS_TYPE_SYSTEM'), 'collapse-system'); ?>
		<ul class="nav nav-tabs nav-stacked">
			<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_EXTERNAL_URL_DESC'); ?>"
					onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => 'url'))); ?>')">
					<?php echo JText::_('COM_MENUS_TYPE_EXTERNAL_URL'); ?> <small class="muted"><?php echo JText::_('COM_MENUS_TYPE_EXTERNAL_URL_DESC'); ?></small>
				</a>
			</li>
			<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_ALIAS_DESC'); ?>"
					onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => 'alias'))); ?>')">
					<?php echo JText::_('COM_MENUS_TYPE_ALIAS'); ?> <small class="muted"><?php echo JText::_('COM_MENUS_TYPE_ALIAS_DESC'); ?></small>
				</a>
			</li>
			<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_SEPARATOR_DESC'); ?>"
					onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => 'separator'))); ?>')">
					<?php echo JText::_('COM_MENUS_TYPE_SEPARATOR'); ?> <small class="muted"><?php echo JText::_('COM_MENUS_TYPE_SEPARATOR_DESC'); ?></small>
				</a>
			</li>
			<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_HEADING_DESC'); ?>"
					onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => 'heading'))); ?>')">
					<?php echo JText::_('COM_MENUS_TYPE_HEADING'); ?> <small class="muted"><?php echo JText::_('COM_MENUS_TYPE_HEADING_DESC'); ?></small>
				</a>
			</li>
		</ul>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>
<?php echo JHtml::_('bootstrap.endAccordion'); ?>
