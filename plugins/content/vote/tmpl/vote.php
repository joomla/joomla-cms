<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Layout variables
 * -----------------
 * @var   string   $context  The context of the content being passed to the plugin
 * @var   object   &$row     The article object
 * @var   object   &$params  The article params
 * @var   integer  $page     The 'page' number
 * @var   array    $parts    The context segments
 * @var   string   $path     Path to this file
 */

$uri = clone Uri::getInstance();
$uri->setVar('hitcount', '0');

// Create option list for voting select box
$options = array();

for ($i = 1; $i < 6; $i++)
{
	$options[] = HTMLHelper::_('select.option', $i, Text::sprintf('PLG_VOTE_VOTE', $i));
}

?>
<form method="post" action="<?php echo htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8'); ?>" class="form-inline mb-2">
	<span class="content_vote">
		<label class="visually-hidden" for="content_vote_<?php echo (int) $row->id; ?>"><?php echo Text::_('PLG_VOTE_LABEL'); ?></label>
		<?php echo HTMLHelper::_('select.genericlist', $options, 'user_rating', 'class="form-select form-select-sm w-auto"', 'value', 'text', '5', 'content_vote_' . (int) $row->id); ?>
		<input class="btn btn-sm btn-primary" type="submit" name="submit_vote" value="<?php echo Text::_('PLG_VOTE_RATE'); ?>">
		<input type="hidden" name="task" value="article.vote">
		<input type="hidden" name="hitcount" value="0">
		<input type="hidden" name="url" value="<?php echo htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</span>
</form>
