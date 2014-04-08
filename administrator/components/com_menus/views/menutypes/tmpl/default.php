<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
		<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', $name, 'collapse' . $i++); ?>
			<ul class="nav nav-tabs nav-stacked">
				<?php foreach ($list as $title => $item) : ?>
					<li>
						<a class="choose_type" href="#" title="<?php echo JText::_($item->description); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => (isset($item->type) ? $item->type : $item->title), 'request' => $item->request))); ?>')">
							<?php echo $title;?> <small class="muted"><?php echo JText::_($item->description); ?></small>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>
<?php echo JHtml::_('bootstrap.endAccordion'); ?>
