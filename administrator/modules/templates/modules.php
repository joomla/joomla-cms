<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * xhtml (divs and font headder tags)
 */
function modChrome_xhtml($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="module<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php if ($module->showtitle != 0) : ?>
			<h3><?php echo $module->title; ?></h3>
		<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	<?php endif;
}

/*
 * allows for rounded corners
 */
function modChrome_sliders($module, &$params, &$attribs)
{
	jimport('joomla.html.pane');
	// Initialize variables
	$user		=& JFactory::getUser();
	$sliders	=& JPane::getInstance('sliders');

	$editAllComponents 	= $user->authorize( 'administration', 'edit', 'components', 'all' );

	// special handling for components module
	if ( $module->module != 'mod_components' || ( $module->module == 'mod_components' && $editAllComponents ) ) {
		$sliders->startPanel( JText::_( $module->title ), 'module' . $module->id );
		echo $module->content;
		$sliders->endPanel();
	}
}

/*
 * allows for rounded corners
 */
function modChrome_tabs($module, &$params, &$attribs)
{
	jimport('joomla.html.pane');
	// Initialize variables
	$user	=& JFactory::getUser();
	$tabs	=& JPane::getInstance('tabs');

	$editAllComponents 	= $user->authorize( 'administration', 'edit', 'components', 'all' );

	// special handling for components module
	if ( $module->module != 'mod_components' || ( $module->module == 'mod_components' && $editAllComponents ) ) {
			$tabs->startPanel( JText::_( $module->title ), 'module' . $module->id );
			echo $module->content;
			$tabs->endPanel();
	}
}

/*
 * allows for rounded corners
 */
function modChrome_feed($module, &$params, &$attribs)
{
	// Initialize variables
	$config		= & JFactory::getConfig();
	$cacheDir	= JPATH_BASE.DS.'cache';
	$rssurl 	= $params->get( 'rssurl', '' );
	$rssitems 	= $params->get( 'rssitems', '' );
	$rssdesc 	= $params->get( 'rssdesc', '' );
	?>
	<table cellpadding="0" cellspacing="0" class="moduletable<?php echo $params->get( 'moduleclass_sfx', '' ); ?>">
	<?php if ($module->content) : ?>
		<tr>
			<td><?php echo $module->content; ?></td>
		</tr>
	<?php endif;

	// feed output
	if ( $rssurl ) {
		if (!is_writable( $cacheDir )) { ?>
			<tr>
				<td><?php echo JText::_( 'Please make cache directory writable.' ); ?></td>
			</tr>
		<?php } else {
			$LitePath = JPATH_SITE .'/includes/Cache/Lite.php';
			$rssDoc =& JFactory::getXMLParser('RSS');
			$rssDoc->useCacheLite(true, $LitePath, $cacheDir, 3600);
			$rssDoc->loadRSS( $rssurl );
			$totalChannels = $rssDoc->getChannelCount();

			for ($i = 0; $i < $totalChannels; $i++) {
				$currChannel =& $rssDoc->getChannel($i);
				echo '<tr>';
				echo '<td><strong><a href="'. $currChannel->getLink() .'" target="_child">';
				echo $currChannel->getTitle() .'</a></strong></td>';
				echo '</tr>';
				if ($rssdesc) {
					echo '<tr>';
					echo '<td>'. $currChannel->getDescription() .'</td>';
					echo '</tr>';
				}

				$actualItems = $currChannel->getItemCount();
				$setItems = $rssitems;

				if ($setItems > $actualItems) {
					$totalItems = $actualItems;
				} else {
					$totalItems = $setItems;
				}

				for ($j = 0; $j < $totalItems; $j++) {
					$currItem =& $currChannel->getItem($j);

					echo '<tr>';
					echo '<td><strong><a href="'. $currItem->getLink() .'" target="_child">';
					echo $currItem->getTitle() .'</a></strong> - '. $currItem->getDescription() .'</td>';
					echo '</tr>';
				}
			}
		}
	}
	echo '</table>';
}
?>