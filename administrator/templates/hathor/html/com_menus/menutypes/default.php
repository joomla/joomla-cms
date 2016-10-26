<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
// Checking if loaded via index.php or component.php
$tmpl = ($input->getCmd('tmpl') != '') ? '1' : '';

JFactory::getDocument()->addScriptDeclaration(
		'
		setmenutype = function(type) {
			var tmpl = ' . json_encode($tmpl) . ';
			if (tmpl)
			{
				window.parent.Joomla.submitbutton("item.setType", type);
				window.parent.jQuery("#menuTypeModal").modal("hide");
			}
			else
			{
				window.location="index.php?option=com_menus&view=item&task=item.setType&layout=edit&type=" + type;
			}
		};
	'
);
?>

<h2 class="modal-title"><?php echo JText::_('COM_MENUS_TYPE_CHOOSE'); ?></h2>
<ul class="menu_types">
	<?php foreach ($this->types as $name => $list): ?>
	<li><dl class="menu_type">
			<dt><?php echo JText::_($name); ?></dt>
			<dd><ul>
					<?php foreach ($list as $item): ?>
					<li><a class="choose_type" href="#" title="<?php echo JText::_($item->description); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => (isset($item->type) ? $item->type : $item->title), 'request' => $item->request))); ?>')">
							<?php echo JText::_($item->title);?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</dd>
		</dl>
	</li>
	<?php endforeach; ?>

</ul>
