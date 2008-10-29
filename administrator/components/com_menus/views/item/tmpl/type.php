<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
	<!--
		Window.onDomReady(function(){
			//document.treemanager.expandAll('menu-item');
		});
	//-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
	<table class="admintable" width="100%">
		<tr valign="top">
			<td width="60%">
				<!-- Menu Item Type Section -->
				<fieldset>
					<legend><?php echo JText::_( 'Select Menu Item Type' ); ?></legend>
					<ul id="menu-item" class="jtree">
						<li id="internal-node"><div class="node-open"><span></span><a href="#"><?php echo JText::_('Internal Link'); ?></a></div>
							<ul>
								<?php for ($i=0,$n=count($this->components);$i<$n;$i++) : ?>
									<?php if($this->components[$i]->legacy) : ?>
										<li><div class="node-open"><span></span><a href="<?php echo JRoute::_('index.php?option=com_menus&amp;task=edit&amp;type=component&amp;url[option]=' . $this->components[$i]->option . '&amp;menutype=' . $this->item->menutype . '&amp;cid[]=' . $this->item->id); ?>" id="<?php echo str_replace('com_', '', $this->components[$i]->option); ?>"><?php echo $this->components[$i]->name; ?></a></div>
									<?php elseif ($this->expansion['option'] == str_replace('com_', '', $this->components[$i]->option)) : ?>
										<li <?php echo ($i == $n-1)? 'class="last"' : '' ?>><div class="node-open"><span></span><a id="<?php echo str_replace('com_', '', $this->components[$i]->option); ?>"><?php echo JText::_($this->components[$i]->name); ?></a></div>
										<?php echo $this->expansion['html']; ?>
									<?php else : ?>
										<li <?php echo ($i == $n-1)? 'class="last"' : '' ?>><div class="node"><span></span><a href="<?php echo JRoute::_('index.php?option=com_menus&amp;task=type&amp;menutype=' . $this->item->menutype . '&amp;cid[]=' . $this->item->id . '&amp;expand=' . str_replace('com_', '', $this->components[$i]->option)); ?>" id="<?php echo str_replace('com_', '', $this->components[$i]->option); ?>"><?php echo JText::_($this->components[$i]->name); ?></a></div>
									<?php endif; ?>
								</li>
								<?php endfor; ?>
							</ul>
						</li>
						<li id="external-node"><div class="base"><span></span><a href="<?php echo JRoute::_('index.php?option=com_menus&amp;task=edit&amp;type=url&amp;menutype=' . $this->item->menutype . '&amp;cid[]=' . $this->item->id); ?>"><?php echo JText::_('External Link'); ?></a></div></li>
						<li id="separator-node"><div class="base"><span></span><a href="<?php echo JRoute::_('index.php?option=com_menus&amp;task=edit&amp;type=separator&amp;menutype=' . $this->item->menutype . '&amp;cid[]=' . $this->item->id); ?>"><?php echo JText::_('Separator'); ?></a></div></li>
						<li id="link-node" class="last"><div class="base"><span></span><a href="<?php echo JRoute::_('index.php?option=com_menus&amp;task=edit&amp;type=menulink&amp;menutype=' . $this->item->menutype . '&amp;cid[]=' . $this->item->id); ?>"><?php echo JText::_('Alias'); ?></a></div></li>
					</ul>
				</fieldset>
			</td>
			<td width="40%">
			</td>
		</tr>
	</table>
	<input type="hidden" name="option" value="com_menus" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>