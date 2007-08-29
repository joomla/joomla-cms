<?php
/**
 * @version $Id$
 */
defined('_JEXEC') or die('Restricted access');

/*
 *
 * Get the template parameters
 *
 */
$filename = JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'params.ini';
if ($content = @ file_get_contents($filename)) {
	$templateParams = new JParameter($content);
} else {
	$templateParams = null;
}
/*
 * hope to get a better solution very soon
 */

$hLevel = $templateParams->get( 'headerLevelComponent', '2' );
$ptLevel = $templateParams->get( 'pageTitleHeaderLevel', '1' );
$pollTitleOpen = '<h' . ($hLevel + 1) . '>';
$pollTitleClose = '</h' . ($hLevel + 1) . '>';
?>

<dl class="poll">
	<dt><?php echo JText::_( 'Number of Voters' ); ?></dt>
	<dd><?php echo $this->votes[0]->voters; ?></dd>
	<dt><?php echo JText::_( 'First Vote' ); ?></dt>
	<dd><?php echo $this->first_vote; ?></dd>
	<dt><?php echo JText::_( 'Last Vote' ); ?></dt>
	<dd><?php echo $this->last_vote; ?></dd>
</dl>

<?php echo $pollTitleOpen; ?>
	<?php echo $this->poll->title; ?>
<?php echo $pollTitleClose; ?>

<table class="pollstableborder">
	<tr>
		<th id="itema" class="td_1"><?php echo JText::_( 'Hits' ); ?></th>
		<th id="itemb" class="td_2"><?php echo JText::_( 'Percent' ); ?></th>
		<th id="itemc" class="td_3"><?php echo JText::_( 'Graph' ); ?></th>
	</tr>
	<?php for ( $row = 0; $row < count( $this->votes ); $row++ ) :
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
