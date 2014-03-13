<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

foreach ($this->items as $i => $item) :
	$status = '';

	if (isset($this->patches[$item->number])) :
		$patch  = $this->patches[$item->number];
		$status = ($patch->applied) ? ' success' : '';
	else :
		$patch = false;
	endif;
?>
<tr class="row<?php echo $i % 2; ?><?php echo $status ?>">
	<td class="center">
		<?php echo $item->number; ?>
	</td>
	<td>
		<a class="icon icon16-github hasTip" title="<?php echo JText::_('COM_PATCHTESTER_OPEN_IN_GITHUB'); ?>" href="<?php echo $item->html_url; ?>" target="_blank">
			<?php echo $item->title; ?>
		</a>
	</td>
	<td>
		<?php if ($item->body) :
			echo JHtml::_('tooltip', htmlspecialchars($item->body), 'Info');
		else :
			echo '&nbsp;';
		endif;
		?>
	</td>
	<td class="center">
		<?php if ($item->joomlacode_issue) :
			$title = ' title="Open link::' . JText::_('COM_PATCHTESTER_OPEN_IN_JOOMLACODE') . '"';

			if (is_int($item->joomlacode_issue)) :
				echo '<a href="http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=';
				echo  $item->joomlacode_issue . '"' . $title . ' class="modal hasTip" rel="{handler: \'iframe\', size: {x: 900, y: 500}}">';
				echo '[#' . $item->joomlacode_issue . ']</a>';
			else :
				echo '<a href="' . $item->joomlacode_issue . '"' . $title;
				echo ' class="modal hasTip" rel="{handler: \'iframe\', size: {x: 900, y: 500}}">';
				echo '[#joomlacode]</a>';
			endif;
		endif; ?>
	</td>
	<td class="center">
		<?php if ($patch && $patch->applied) : ?>
			<span class="label label-success">
			<?php echo JText::_('COM_PATCHTESTER_APPLIED'); ?>
			</span>
		<?php else : ?>
			<span class="label">
			<?php echo JText::_('COM_PATCHTESTER_NOT_APPLIED'); ?>
			</span>
		<?php endif; ?>
	</td>
	<td class="center">
		<?php if ($patch && $patch->applied) :
			echo '<a class="btn btn-small btn-success" href="javascript:submitpatch(\'pull.revert\', ' . (int) $patch->id . ');">' . JText::_('COM_PATCHTESTER_REVERT_PATCH') . '</a>';
		else :
			echo '<a class="btn btn-small btn-primary" href="javascript:submitpatch(\'pull.apply\', ' . (int) $item->number . ');">' . JText::_('COM_PATCHTESTER_APPLY_PATCH') . '</a>';
		endif; ?>
	</td>
</tr>
<?php endforeach;
