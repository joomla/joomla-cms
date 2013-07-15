<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
$extension = 'layout_social';
$base_dir = JPATH_SITE;
$lang->load($extension, $base_dir);

if (!isset($displayData['user']))
{
	JLog::add(JText::sprintf('TWITTER_USER_REQUIRED', JText::_('TWITTER_FOLLOW')), JLog::WARNING);
}
else
{
	if (empty($displayData['user']))
	{
		JLog::add(JText::sprintf('TWITTER_USER_REQUIRED', JText::_('TWITTER_FOLLOW')), JLog::WARNING);
	}
	else
	{
		// Check if the user has specified a (boolean) to show the number of users who have tweeted. Set it to the default false otherwise
		$showCount = 'false';

		if (isset($displayData['show-count']) && (!is_bool($displayData['show-count'])))
		{
			$showCount = $displayData['show-count'];
		}
		
		/**
		 * Auto-detect language - but let that be overridden if wanted from extensions languages
		 * Should be in the form of xx.
		**/
		$language = $lang->getLocale()['4'];
		if (isset($displayData['language']))
		{
			$language = $displayData['language'];
		}

		// Get Document to add in twitter script if not already included
		$document = JFactory::getDocument();

		if (!in_array('<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>', $document->_custom))
		{
			$document->addCustomTag('<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>');
		}
		?>
		<div class="TwitterButton">
			<a href="https://twitter.com/<?php echo $displayData['user']; ?>"
				class="twitter-follow-button"
				data-show-count="<?php echo $showCount; ?>"
				data-lang="<?php echo $language; ?>"
			>
				<?php echo JText::sprintf(JText::_('TWITTER_FOLLOW_CAPITAL'), $displayData['user']); ?>
			</a>
		</div>
	<?php
	}
}
