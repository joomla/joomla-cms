<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
*/
class modules_html
{
	/**
	* @param object
	* @param object
	* @param int 0=show without wrapper and title, 1=tabbed style, 2=xhtml style
	*/
	function module( &$module, &$params, $style=0, $preview=true )
	{
		switch ( $style )
		{
			case 3:
				// show a naked module - no wrapper and no title
				modules_html::modoutput_sliders( $module, $params );
				break;

			case 2:
				// show a naked module - no wrapper and no title
				modules_html::modoutput_xhtml( $module, $params );
				break;

			case 1:
				// show a tabbed module - no wrapper and no title
				modules_html::modoutput_tabs( $module, $params );
				break;

			case 0:
			default:
				// show a naked module
				modules_html::modoutput_naked( $module, $params );
				break;
		}

		if ( $params->get( 'rssurl' ) ) {
			// feed output
			modules_html::modoutput_feed( $module, $params );
		}
	}

	/*
	* standard xhtml output, module wrapped in a div
	*/
	function modoutput_xhtml( $module, $params  )
	{
		$moduleclass_sfx = $params->get( 'moduleclass_sfx' );

		?>
		<div>
		<?php echo $module->content; ?>
		</div>
		<?php
	}

	/*
	* show a tabbed module
	*/
	function modoutput_sliders( $module, $params  )
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$user	=& $mainframe->getUser();
		$tabs	=& JPane::getInstance('sliders');

		$editAllComponents 	= $user->authorize( 'administration', 'edit', 'components', 'all' );
		// special handling for components module

		if ( $module->module != 'mod_components' || ( $module->module == 'mod_components' && $editAllComponents ) ) {
				$tabs->startPanel( JText::_( $module->title ), 'module' . $module->id );
				echo $module->content;
				$tabs->endPanel();
		}
	}

	/*
	* show a tabbed module
	*/
	function modoutput_tabs( $module, $params  )
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$user	=& $mainframe->getUser();
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
	* show a naked module - no wrapper and no title
	*/
	function modoutput_naked( $module, $params  )
	{
		$moduleclass_sfx = $params->get( 'moduleclass_sfx' );
		echo $module->content;
	}

	function modoutput_feed( &$module, &$params )
	{
		global $mainframe;

		$rssurl 			= $params->get( 'rssurl', '' );
		$rssitems 			= $params->get( 'rssitems', '' );
		$rssdesc 			= $params->get( 'rssdesc', '' );
		$moduleclass_sfx 	= $params->get( 'moduleclass_sfx', '' );

		echo '<table cellpadding="0" cellspacing="0" class="moduletable' . $moduleclass_sfx . '">';

		if ($module->content) {
			echo '<tr>';
			echo '<td>' . $module->content . '</td>';
			echo '</tr>';
		}

		// feed output
		if ( $rssurl ) {
			$cacheDir = $mainframe->getCfg( 'cachepath' ) .'/';
			if (!is_writable( $cacheDir )) {
				echo '<tr>';
				echo '<td>'. JText::_( 'Please make cache directory writable.' ) .'</td>';
				echo '</tr>';
			} else {
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
}
?>