<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<br />
<table class="pollstableborder" cellspacing="0" cellpadding="0" border="0">
<thead>
	<tr>
		<th colspan="3" class="sectiontableheader">
			<img src="components/com_poll/assets/poll.png" align="middle" border="0" width="12" height="14" alt="" />
			<?php echo $this->poll->title; ?>
		</th>
	</tr>
</thead>
<tbody>
<?php foreach($this->votes as $vote) : ?>
	<tr class="sectiontableentry<?php echo $vote->odd; ?>">
		<td width="100%" colspan="3">
			<?php echo $vote->text; ?>
		</td>
	</tr>
	<tr class="sectiontableentry<?php echo $vote->odd; ?>">
		<td align="right" width="25">
			<strong><?php echo $vote->hits; ?></strong>&nbsp;
		</td>
		<td width="30" >
			<?php echo $vote->percent; ?>%
		</td>
		<td width="300" >
			<div class="<?php echo $vote->class; ?>" style="height:<?php echo $vote->barheight; ?>px;width:<?php echo $vote->percent; ?>%"></div>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
<br />
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
	<tr>
		<td class="smalldark">
			<?php echo JText::_( 'Number of Voters' ); ?>
		</td>
		<td class="smalldark">
			&nbsp;:&nbsp;
			<?php echo $this->votes[0]->voters; ?>
		</td>
	</tr>
	<tr>
		<td class="smalldark">
			<?php echo JText::_( 'First Vote' ); ?>
		</td>
		<td class="smalldark">
			&nbsp;:&nbsp;
			<?php echo $this->first_vote; ?>
		</td>
	</tr>
	<tr>
		<td class="smalldark">
			<?php echo JText::_( 'Last Vote' ); ?>
		</td>
		<td class="smalldark">
			&nbsp;:&nbsp;
			<?php echo $this->last_vote; ?>
		</td>
	</tr>
</tbody>
</table>