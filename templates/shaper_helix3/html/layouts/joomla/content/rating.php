<?php
/**
 * @package     Helix3
 * @subpackage  Layout
 * @author 		JoomShaper
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

if($displayData['params']->get('show_vote')) {

$rating = (int) $displayData['item']->rating;
$layout = JRequest::getCmd('view', 'article');

$rating_count = $displayData['item']->rating_count;
if($rating_count == '') {
	$rating_count = 0;
}

$class_name = '';

if ($layout == 'article') {
	$class_name = ' sp-rating';
}
?>
	<dd class="post_rating" id="post_vote_<?php echo $displayData['item']->id; ?>">
		<?php echo JText::_('HELIX3_ARTICLE_RATING'); ?>: <div class="voting-symbol<?php echo $class_name; ?>">
			<?php
			$j = 0;
			for($i = $rating; $i < 5; $i++){
				echo '<span class="star" data-number="'.(5-$j).'"></span>';
				$j = $j+1;
			}
			for ($i = 0; $i < $rating; $i++)
			{
				echo '<span class="star active" data-number="'.($rating - $i).'"></span>';
			}
			?>
		</div>
		<span class="ajax-loader fa fa-spinner fa-spin"></span>
		<span class="voting-result">( <?php echo ($rating_count>1) ? $rating_count . ' ' . JText::_('HELIX3_COUNT_RATINGS') : $rating_count . ' ' . JText::_('HELIX3_COUNT_RATING'); ?> )</span>
</dd>
<?php }