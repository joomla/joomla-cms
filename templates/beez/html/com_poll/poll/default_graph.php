<?php // @version $Id: default_graph.php 10381 2008-06-01 03:35:53Z pasamio $
defined('_JEXEC') or die;
?>

<dl class="poll">
	<dt><?php echo JText::_('Number of Voters'); ?></dt>
	<dd><?php echo $this->votes[0]->voters; ?></dd>
	<dt><?php echo JText::_('First Vote'); ?></dt>
	<dd><?php echo $this->first_vote; ?></dd>
	<dt><?php echo JText::_('Last Vote'); ?></dt>
	<dd><?php echo $this->last_vote; ?></dd>
</dl>

<h3>
	<?php echo $this->poll->title; ?>
</h3>

<table class="pollstableborder">
	<tr>
		<th id="itema" class="td_1"><?php echo JText::_('Hits'); ?></th>
		<th id="itemb" class="td_2"><?php echo JText::_('Percent'); ?></th>
		<th id="itemc" class="td_3"><?php echo JText::_('Graph'); ?></th>
	</tr>
	<?php for ($row = 0; $row < count($this->votes); $row++) :
		$vote = $this->votes[$row];
	?>
	<tr>
		<td colspan="3" id="question<?php echo $row; ?>" class="question">
			<?php echo $vote->text; ?>
		</td>
	</tr>
	<tr class="sectiontableentry<?php echo $vote->odd; ?>">
		<td headers="itema question<?php echo $row; ?>" class="td_1">
			<?php echo $vote->hits; ?>
		</td>
		<td headers="itemb question<?php echo $row; ?>" class="td_2">
			<?php echo $vote->percent.'%' ?>
		</td>
		<td headers="itemc question<?php echo $row; ?>" class="td_3">
			<div class="<?php echo $vote->class; ?>" style="height:<?php echo $vote->barheight; ?>px;width:<?php echo $vote->percent; ?>% !important"></div>
		</td>
	</tr>
	<?php endfor; ?>
</table>
