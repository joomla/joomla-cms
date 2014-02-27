<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 * 	$context         : (string) The context of the content being passed to the plugin
 * 	$row             : (object) The article object
 * 	$params          : (JRegistry)  The article params
 * 	  - showVoteForm : (boolean) Do we have to show the vote form?
 * 	$page            : (int) The 'page' number
 */

$rating = (int) @$row->rating;

$img = '';

// Look for images in template if available
$starImageOn = JHtml::_('image', 'system/rating_star.png', JText::_('PLG_VOTE_STAR_ACTIVE'), null, true);
$starImageOff = JHtml::_('image', 'system/rating_star_blank.png', JText::_('PLG_VOTE_STAR_INACTIVE'), null, true);

for ($i = 0; $i < $rating; $i++)
{
	$img .= $starImageOn;
}

for ($i = $rating; $i < 5; $i++)
{
	$img .= $starImageOff;
}

if ($params->get('showVoteForm', false))
{
	$uri = JUri::getInstance();
	$uri->setQuery($uri->getQuery() . '&hitcount=0');

	// Create option list for voting select box
	$options = array();

	for ($i = 1; $i < 6; $i++)
	{
		$options[] = JHtml::_('select.option', $i, JText::sprintf('PLG_VOTE_VOTE', $i));
	}
}
?>
<div class="content_rating">
	<p class="unseen element-invisible"><?php JText::sprintf('PLG_VOTE_USER_RATING', $rating, '5'); ?></p>
	<?php echo $img; ?>
</div>
<?php if ($params->get('showVoteForm', false)) : ?>
	<form method="post" action="<?php echo htmlspecialchars($uri->toString()); ?>" class="form-inline">
		<span class="content_vote">
			<label class="unseen element-invisible" for="content_vote_<?php echo $row->id; ?>">
				<?php echo JText::_('PLG_VOTE_LABEL'); ?>
			</label>
			<?php echo JHtml::_('select.genericlist', $options, 'user_rating', null, 'value', 'text', '5', 'content_vote_' . $row->id); ?>
			&#160;<input class="btn btn-mini" type="submit" name="submit_vote" value="<?php echo JText::_('PLG_VOTE_RATE'); ?>" />
			<input type="hidden" name="task" value="article.vote" />
			<input type="hidden" name="hitcount" value="0" />
			<input type="hidden" name="url" value="<?php echo htmlspecialchars($uri->toString()); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</span>
	</form>
<?php endif;
