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
$params = new JInput($displayData);

$hashtag = $params->get('hashtag');

if ($hashtag == '')
{
	JLog::add(JText::_('JLAYOUT_TWITTER_HASHTAG_REQUIRED'), JLog::WARNING);
}
else
{
	// Size of the hashtag box can either be regular (blank) or large
	$size = $params->get('data-size', '');

	if ($size != '')
	{
		$size = 'data-size="large"';
	}

	// Allow related users to be put into the mention
	$related = $params->get('data-related', '');

	if ($related != '')
	{
		$related = 'data-related="' . $related . '"';
	}

	$text = $params->get('data-text', '');

	if ($text != '')
	{
		$text = '&text=' . rawurlencode($text);
	}

	/**
	 * Auto-detect language - but let that be overridden if wanted from extensions languages
	 * Should be in the form of xx.
	**/
	$language = $params->get('language', JFactory::getLanguage()->getLocale()['4']);

	// Get Document to add in twitter script if not already included
	$document = JFactory::getDocument();

	$document->addScript("http://platform.twitter.com/widgets.js");
	?>
	<div class="TwitterButton">
		<a href="https://twitter.com/intent/tweet?button_hashtag=<?php echo $hashtag . $text; ?>"
			class="twitter-hashtag-button"
			<?php echo $size . $related; ?>
			data-lang="<?php echo $language; ?>"
		>
			<?php echo JText::sprintf(JText::_('JLAYOUT_TWITTER_TWEET_HASHTAG'), $hashtag); ?>
		</a>
	</div>
<?php
}
