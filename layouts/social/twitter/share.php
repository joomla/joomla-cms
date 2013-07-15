<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Allow hashtags to be put into the share
if (isset($displayData['data-hashtags']))
{
	$hashtags = 'data-hashtags="' . $displayData['data-hashtags'] . '"';
}
else
{
	$hashtags = '';
}

// Allow a user to be put into the share
if (isset($displayData['data-via']))
{
	$via = 'data-via="' . $displayData['data-via'] . '"';
}
else
{
	$via = '';
}

if (isset($displayData['data-size']))
{
	$size = 'data-size="large"';
}
else
{
	$size = '';
}

// Allow a URL to be put into the share (defaults to current URL)
if (isset($displayData['data-href']))
{
	$url = $displayData['data-href'];
}
else
{
	$url = JUri::current();
}

// Allow text to be put into the share
if (isset($displayData['data-text']))
{
	$text = 'data-text="' . $displayData['data-text'] . '"';
}
else
{
	$text = '';
}

// Allow related users to be put into the share
if (isset($displayData['data-related']))
{
	$related = 'data-related="' . $displayData['data-related'] . '"';
}
else
{
	$related = '';
}

// Get Document to add in twitter script if not already included
$document = JFactory::getDocument();

/**
 * Auto-detect language - but let that be overridden if wanted from extensions languages
 * Should be in the form of xx.
**/
$language = JFactory::getLanguage()->getLocale()['4'];
if (isset($displayData['language']))
{
	$language = $displayData['language'];
}

if (!in_array('<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>', $document->_custom))
{
	$document->addCustomTag('<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>');
}
?>
<div class="TwitterButton">
	<a href="https://twitter.com/share" class="twitter-share-button" <?php echo 'data-lang="' . $language . '"' . $hashtags . $via . $size . 'data-url="' . $url . '"' . $text . $related; ?>><?php echo JText::_('TWITTER_TWEET'); ?></a>
</div>
