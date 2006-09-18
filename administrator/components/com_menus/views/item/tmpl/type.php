<script language="javascript" type="text/javascript">
	<!-- 
		document.addLoadEvent(function() {
		 	document.treemanager.expandAll('tree2');
		});
	//-->
</script>
<table class="admintable" width="100%">
	<tr valign="top">
		<td width="60%">
			<!-- Menu Item Type Section -->
			<fieldset>
				<legend>
					<?php echo JText::_( 'Menu Item Type' ); ?>
				</legend>
				<h2><?php echo JText::_( 'Select Type' ); ?></h2>
				<div>
					<!-- xajax_tree(this.parentNode.id,this.id) //-->
					<ul id="tree2" class="jtree">
						<li><a href="#" id="node_com">Component</a>
							<ul>
								<?php foreach ($this->components as $component) : ?>
								<li><a href="index.php?option=com_menus&amp;task=type&amp;menutype=<?php echo $this->item->menutype; ?>&amp;cid[]=<?php echo $this->item->id; ?>&amp;expand=<?php echo str_replace('com_', '', $component->option); ?>" id="<?php echo str_replace('com_', '', $component->option); ?>" onclick="xajax_tree(this.parentNode.id,this.id);"><?php echo $component->name; ?></a>
								<?php if ($this->expansion['option'] == str_replace('com_', '', $component->option)) : ?>
								<?php echo $this->expansion['html']; ?>
								<?php endif; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						</li>
						<li><a href="#" id="node_url">URL</a></li>
						<li><a href="#" id="node_sep">Separator</a></li>
						<li><a href="#" id="node_link">Menulink</a></li>	
					</ul>
				</div>
			</fieldset>
		</td>
	</tr>
</table>
