<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params = new JInput($displayData);

// Allow hashtags to be put into the share
$hashtags = $params->get('data-hashtags', '');

if ($hashtags != '')
{
	$hashtags = 'data-hashtags="' . $hashtags . '"';
}

// Allow a user to be put into the share
$via = $params->get('data-via', '');

if ($via != '')
{
	$via = 'data-via="' . $via . '"';
}

// Size of the share box can either be regular (blank) or large
$size = $params->get('data-size', '');

if ($size != '')
{
	$size = 'data-size="large"';
}

// Allow a URL to be put into the share (defaults to current URL)
$url = $params->get('data-href', JUri::current());

// Allow text to be put into the share
$text = $params->get('data-text', '');

if ($text != '')
{
	$text = 'data-text="' . $text . '"';
}

// Allow related users to be put into the share
$related = $params->get('data-related', '');

if ($related != '')
{
	$related = 'data-related="' . $related . '"';
}

// Get Document to add in twitter script if not already included
$document = JFactory::getDocument();

/**
 * Auto-detect language - but let that be overridden if wanted from extensions languages
 * Should be in the form of xx.
**/
$language = $params->get('language', JFactory::getLanguage()->getLocale()['4']);

$document->addScript("http://platform.twitter.com/widgets.js");
?>
<div class="TwitterButton">
	<a href="https://twitter.com/share" class="twitter-share-button" <?php echo 'data-lang="' . $language . '"' . $hashtags . $via . $size . 'data-url="' . $url . '"' . $text . $related; ?>><?php echo JText::_('TWITTER_TWEET'); ?></a>
</div>
