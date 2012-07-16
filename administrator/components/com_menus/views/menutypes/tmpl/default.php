<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Checking if loaded via index.php or component.php
$tmpl = JRequest::getCmd('tmpl', '');
?>

<script type="text/javascript">
	setmenutype = function(type)
	{
		<?php if($tmpl) : ?>
			window.parent.Joomla.submitbutton('item.setType', type);
			window.parent.SqueezeBox.close();
		<?php else : ?>
			window.location="index.php?option=com_menus&view=item&task=item.setType&layout=edit&type="+('item.setType', type);
		<?php endif; ?>
	}
</script>

<h4 class="modal-title"><?php echo JText::_('COM_MENUS_TYPE_CHOOSE'); ?></h2>

<!-- Begin Accordions -->
<div class="accordion" id="collapseTypes">
	<?php 
		$i = 0;
		foreach ($this->types as $name => $list): ?>
		<!-- Begin Accordion Group -->
		<div class="accordion-group">
			<div class="accordion-heading">
				<h4>
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#collapseTypes" href="#collapse<?php echo $i++; ?>">
						<?php echo JText::_($name); ?>
					</a>
				</h4>
			</div>
			<div id="collapse<?php echo $i+-1; ?>" class="accordion-body collapse">
				<div class="accordion-inner">
					<ul class="nav nav-list">
						<?php foreach ($list as $item): ?>
							<li>
								<a class="choose_type" href="#" rel="tooltip" title="<?php echo JText::_($item->description); ?>"
									onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => $item->title, 'request' => $item->request))); ?>')">
									<?php echo JText::_($item->title);?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
		<!-- End Accordion Group -->
	<?php endforeach; ?>
	
	<!-- Begin Accordion Group -->
	<div class="accordion-group">
		<div class="accordion-heading">
			<h4>
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#collapseTypes" href="#collapseSystem">
					<?php echo JText::_('COM_MENUS_TYPE_SYSTEM'); ?>
				</a>
			</h4>
		</div>
		<div id="collapseSystem" class="accordion-body collapse">
			<div class="accordion-inner">
				<ul class="nav nav-list">
					<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_EXTERNAL_URL_DESC'); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title'=>'url'))); ?>')">
							<?php echo JText::_('COM_MENUS_TYPE_EXTERNAL_URL'); ?>
						</a>
					</li>
					<li><a class="choose_type" href="#" title="<?php echo JText::_('COM_MENUS_TYPE_ALIAS_DESC'); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title'=>'alias'))); ?>')">
							<?php echo JText::_('COM_MENUS_TYPE_ALIAS'); ?>
						</a>
					</li>
					<li><a class="choose_type" href="#"  title="<?php echo JText::_('COM_MENUS_TYPE_SEPARATOR_DESC'); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title'=>'separator'))); ?>')">
							<?php echo JText::_('COM_MENUS_TYPE_SEPARATOR'); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<!-- End Accordion Group -->
</div>
<!-- End Accordions -->
