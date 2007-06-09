<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form name="form2" method="post" action="index.php">

<table width="95%" border="0" cellspacing="0" cellpadding="1" align="center" class="poll<?php echo $params->get('moduleclass_sfx'); ?>">
<thead>
	<tr>
		<td style="font-weight: bold;">
			<?php echo $item->title; ?>
		</td>
	</tr>
</thead>
	<tr>
		<td align="center">
			<table class="pollstableborder<?php echo $params->get('moduleclass_sfx'); ?>" cellspacing="0" cellpadding="0" border="0">
			<?php for ($i = 0, $n = count($options); $i < $n; $i ++) : ?>
				<tr>
					<td class="<?php echo $tabclass_arr[$tabcnt]; ?><?php echo $params->get('moduleclass_sfx'); ?>" valign="top">
						<input type="radio" name="voteid" id="voteid<?php echo $options[$i]->id;?>" value="<?php echo $options[$i]->id;?>" alt="<?php echo $options[$i]->id;?>" />
					</td>
					<td class="<?php echo $tabclass_arr[$tabcnt]; ?><?php echo $params->get('moduleclass_sfx'); ?>" valign="top">
						<label for="voteid<?php echo $options[$i]->id;?>">
							<?php echo $options[$i]->text; ?>
						</label>
					</td>
				</tr>
				<?php
					$tabcnt = 1 - $tabcnt;
				?>
			<?php endfor; ?>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<div align="center">
				<input type="submit" name="task_button" class="button" value="<?php echo JText::_('Vote'); ?>" />
				&nbsp;
				<input type="button" name="option" class="button" value="<?php echo JText::_('Results'); ?>" onclick="document.location.href='<?php echo JRoute::_("index.php?option=com_poll&id=$item->slug"); ?>'" />
			</div>
		</td>
	</tr>
</table>

<input type="hidden" name="option" value="com_poll" />
<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />
<input type="hidden" name="id" value="<?php echo $item->id;?>" />
<input type="hidden" name="task" value="vote" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>