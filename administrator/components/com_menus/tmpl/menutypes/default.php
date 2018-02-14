<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;

// Checking if loaded via index.php or component.php
$tmpl = ($input->getCmd('tmpl') != '') ? '1' : '';
$tmpl = json_encode($tmpl, JSON_NUMERIC_CHECK);

JHtml::_('script', 'com_menus/admin-item-modal.js', ['version' => 'auto', 'relative' => true]);

?>
<?php echo JHtml::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'slide1')); ?>
	<?php $i = 0; ?>
	<?php foreach ($this->types as $name => $list) : ?>
		<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', $name, 'collapse' . ($i++)); ?>
			<div class="list-group">
				<?php foreach ($list as $title => $item) : ?>
					<?php $menutype = array('id' => $this->recordId, 'title' => $item->type ?? $item->title, 'request' => $item->request); ?>
					<?php $menutype = base64_encode(json_encode($menutype)); ?>
					<a class="choose_type list-group-item list-group-item-action" href="#" title="<?php echo JText::_($item->description); ?>"
						onclick="Joomla.setMenuType('<?php echo $menutype; ?>', '<?php echo $tmpl; ?>')">
						<div class="pr-2">
							<?php echo $title; ?>
						</div>
						<small class="text-muted">
							<?php echo JText::_($item->description); ?>
						</small>
					</a>
				<?php endforeach; ?>
			</div>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php endforeach; ?>
<?php echo JHtml::_('bootstrap.endSlide'); ?>
<?php echo JHtml::_('bootstrap.endAccordion');
