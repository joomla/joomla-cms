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

$user = $params->get('user', '');

if ($user == '')
{
	JLog::add(JText::sprintf('JLAYOUT_TWITTER_USER_REQUIRED', JText::_('JLAYOUT_TWITTER_MENTION')), JLog::WARNING);
}
else
{
	// Check if the user has specified a (boolean) to show the number of users who have tweeted. Set it to the default false otherwise
	$showCount = $params->get('show-count', false, 'bool');

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
		<a href="https://twitter.com/<?php echo $user; ?>"
			class="twitter-follow-button"
			data-show-count="<?php echo $showCount; ?>"
			data-lang="<?php echo $language; ?>"
		>
			<?php echo JText::sprintf(JText::_('JLAYOUT_TWITTER_FOLLOW_CAPITAL'), $user); ?>
		</a>
	</div>
<?php
}
