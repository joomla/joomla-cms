<script language="javascript" type="text/javascript">
	<!-- 
		document.addLoadEvent(function() {
		 	document.treemanager.expandAll('tree2');
		});
	//-->
</script>
<form action="index.php" method="post" name="adminForm">
	<table class="admintable" width="100%">
		<tr valign="top">
			<td width="70%">
				<!-- Menu Item Type Section -->
				<fieldset>
					<legend>
						<?php echo JText::_( 'Select Menu Item Type' ); ?>
					</legend>
					<div>
						<div id="internal-node"><a href="#"><?php echo JText::_('Internal Link'); ?></a>
							<ul id="tree2" class="jtree">
								<?php foreach ($this->components as $component) : ?>
								<li><a href="index.php?option=com_menus&amp;task=type&amp;menutype=<?php echo $this->item->menutype; ?>&amp;cid[]=<?php echo $this->item->id; ?>&amp;expand=<?php echo str_replace('com_', '', $component->option); ?>" id="<?php echo str_replace('com_', '', $component->option); ?>"><?php echo $component->name; ?></a>
								<?php if ($this->expansion['option'] == str_replace('com_', '', $component->option)) : ?>
								<?php echo $this->expansion['html']; ?>
								<?php endif; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div id="external-node"><a href="index.php?option=com_menus&amp;task=edit&amp;type=url&amp;menutype=<?php echo $this->item->menutype; ?>&amp;cid[]=<?php echo $this->item->id; ?>"><?php echo JText::_('External Link'); ?></a>
						</div>
						<div id="separator-node"><a href="index.php?option=com_menus&amp;task=edit&amp;type=separator&amp;menutype=<?php echo $this->item->menutype; ?>&amp;cid[]=<?php echo $this->item->id; ?>"><?php echo JText::_('Separator'); ?></a>
						</div>
						<div id="link-node"><a href="index.php?option=com_menus&amp;task=edit&amp;type=menulink&amp;menutype=<?php echo $this->item->menutype; ?>&amp;cid[]=<?php echo $this->item->id; ?>"><?php echo JText::_('Alias'); ?></a>
						</div>
					</div>
				</fieldset>
			</td>
			<td width="30%">
				<!-- Menu Item Type Description -->
				<fieldset>
					<legend>
						<?php echo JText::_( 'Description' ); ?>
					</legend>
					<div id="jdescription"></div>
				</fieldset>
			</td>
		</tr>
	</table>
	<input type="hidden" name="option" value="com_menus" />
	<input type="hidden" name="task" value="" />
</form>
