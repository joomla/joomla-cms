<?php
/**
* @author    JoomShaper http://www.joomshaper.com
* @copyright Copyright (C) 2010 - 2015 JoomShaper
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
*/
//no direct access
defined('_JEXEC') or die('Restricted Access');

if( $displayData['params']->get('fb_appID') != '' ) {

	$doc = JFactory::getDocument();

	if(!defined('HELIX_COMMENTS_FACEBOOK_COUNT')) {

		$doc->addScript( '//connect.facebook.net/en-GB/all.js#xfbml=1&appId=' . $displayData['params']->get('fb_appID') . '&version=v2.0' );

		define('HELIX_COMMENTS_FACEBOOK_COUNT', 1);

	}

	?>

	<span class="comments-anchor">
		<a href="<?php echo $displayData['url']; ?>#sp-comments"><?php echo JText::_('COMMENTS'); ?> <fb:comments-count href=<?php echo $displayData['url']; ?>></fb:comments-count></a>
	</span>

	<?php

}