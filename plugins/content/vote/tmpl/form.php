<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<form method="post" action="<?php echo htmlspecialchars($uri->toString()); ?>" class="form-inline">
    <span class="content_vote">
        <label class="unseen element-invisible" for="content_vote_<?php echo $row->id; ?>"><?php echo JText::_('PLG_VOTE_LABEL'); ?></label>
        <?php echo JHtml::_('select.genericlist', $options, 'user_rating', null, 'value', 'text', '5', 'content_vote_' . $row->id); ?>
        &#160;<input class="btn btn-mini" type="submit" name="submit_vote" value="' . JText::_('PLG_VOTE_RATE') . '" />
        <input type="hidden" name="task" value="article.vote" />
		<input type="hidden" name="hitcount" value="0" />
        <input type="hidden" name="url" value="<?php echo htmlspecialchars($uri->toString()); ?>" />
        <?php echo JHtml::_('form.token'); ?>
	</span>
</form>