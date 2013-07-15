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

if(!isset($displayData['hashtag']))
{
	JLog::add(JText::_('TWITTER_HASHTAG_REQUIRED'), JLog::WARNING);
}
else
{
	if(empty($displayData['hashtag']))
	{
	   JLog::add(JText::_('TWITTER_HASHTAG_REQUIRED'), JLog::WARNING);
	}
	else
	{
		if (isset($displayData['data-size']))
		{
			 $size = 'data-size="large"';
		}
		else
		{
			$size = '';
		}
		
		if (isset($displayData['data-related']))
		{
			 $related = 'data-related="' . $displayData['data-related'] . '"';
		}
		else
		{
			$related = '';
		}
		
		if (isset($displayData['data-text']))
		{
			 $text = '&text=' . rawurlencode($displayData['data-text']);
		}
		else
		{
			$text = '';
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
			<a href="https://twitter.com/intent/tweet?button_hashtag=<?php echo $displayData['hashtag'] . $text; ?>"
				class="twitter-hashtag-button"
				<?php echo $displayData['data-size'] . $displayData['data-related']; ?>
				data-lang="<?php echo $language; ?>"
			>
				<?php echo JText::sprintf(JText::_('TWITTER_TWEET_HASHTAG'), $displayData['hashtag']); ?>
			</a>
		</div>
	<?php
	}
}
