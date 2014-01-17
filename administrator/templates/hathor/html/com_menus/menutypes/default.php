<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<script type="text/javascript">
	setmenutype = function(type)
	{
		window.parent.Joomla.submitbutton('item.setType', type);
		window.parent.SqueezeBox.close();
	}
</script>

<h2 class="modal-title"><?php echo JText::_('COM_MENUS_TYPE_CHOOSE'); ?></h2>
<ul class="menu_types">
	<?php foreach ($this->types as $name => $list): ?>
	<li><dl class="menu_type">
			<dt><?php echo JText::_($name); ?></dt>
			<dd><ul>
					<?php foreach ($list as $item): ?>
					<li><a class="choose_type" href="#" title="<?php echo JText::_($item->description); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => $item->title, 'request' => $item->request))); ?>')">
							<?php echo JText::_($item->title);?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</dd>
		</dl>
	</li>
	<?php endforeach; ?>

	<li><dl class="menu_type">
			<dt><?php echo JText::_('COM_MENUS_TYPE_SYSTEM'); ?></dt>
			<dd>
				<ul>
					<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_EXTERNAL_URL_DESC'); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => 'url'))); ?>')">
							<?php echo JText::_('COM_MENUS_TYPE_EXTERNAL_URL'); ?>
						</a>
					</li>
					<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_ALIAS_DESC'); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => 'alias'))); ?>')">
							<?php echo JText::_('COM_MENUS_TYPE_ALIAS'); ?>
						</a>
					</li>
					<li><a class="choose_type" href="#"  title="<?php echo JText::_('COM_MENUS_TYPE_SEPARATOR_DESC'); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => 'separator'))); ?>')">
							<?php echo JText::_('COM_MENUS_TYPE_SEPARATOR'); ?>
						</a>
					</li>
					<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_HEADING_DESC'); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => 'heading'))); ?>')">
							<?php echo JText::_('COM_MENUS_TYPE_HEADING'); ?>
						</a>
					</li>
				</ul>
			</dd>
		</dl>
	</li>
</ul>
