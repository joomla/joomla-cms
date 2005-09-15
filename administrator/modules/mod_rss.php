<?php
/**
* @version $Id: mod_rss.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla_4.5.3
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $_LANG;

$cacheDir = $mosConfig_absolute_path .'/cache/';
//check if cache diretory is writable as cache files will be created for the feed
if ( !is_writable( $cacheDir ) ) {
	echo '<div>';
	echo $_LANG->_( 'Please make cache directory writable.' );
	echo '</div>';
	exit;
}

// module params
$rssurl 			= $params->get( 'rssurl' );
$rssitems 			= $params->get( 'rssitems', 5 );
$rssdesc 			= $params->get( 'rssdesc', 1 );
$rssimage 			= $params->get( 'rssimage', 1 );
$rssitemdesc		= $params->get( 'rssitemdesc', 1 );
$moduleclass_sfx 	= $params->get( 'moduleclass_sfx' );
$words 				= $params->def( 'word_count', 0 );

$LitePath 			= $mosConfig_absolute_path .'/includes/Cache/Lite.php';
require_once( $mosConfig_absolute_path .'/includes/domit/xml_domit_rss.php' );

$rssDoc = new xml_domit_rss_document();
$rssDoc->useCacheLite(true, $LitePath, $cacheDir, 3600);
$rssDoc->loadRSS( $rssurl );

$totalChannels 	= $rssDoc->getChannelCount();
?>
<table cellpadding="0" cellspacing="0" class="moduletable<?php echo $moduleclass_sfx; ?>">
<?php
for ( $i = 0; $i < $totalChannels; $i++ ) {
	$currChannel =& $rssDoc->getChannel($i);
	$elements 	= $currChannel->getElementList();
	$iUrl		= 0;
	$iTitle		= $_LANG->_( 'Feed Image' );
	foreach ( $elements as $element ) {
		//image handling
		if ( $element == 'image' ) {
			$image =& $currChannel->getElement( DOMIT_RSS_ELEMENT_IMAGE );
			$iUrl	= $image->getUrl();
			$iTitle	= $image->getTitle();
		}
	}

	// feed title
	?>
	<tr>
		<td>
		<strong>
		<a href="<?php echo $currChannel->getLink(); ?>" target="_blank">
		<?php echo $currChannel->getTitle(); ?>
		</a>
		</strong>
		</td>
	</tr>
	<?php
	// feed description
	if ( $rssdesc ) {
		?>
		<tr>
			<td>
			<?php echo $currChannel->getDescription(); ?>
			</td>
		</tr>
		<?php
	}
	// feed image
	if ( $rssimage && $iUrl ) {
		?>
		<tr>
			<td align="center">
			<img src="<?php echo $iUrl; ?>" alt="<?php echo $iTitle; ?>"/>
			</td>
		</tr>
		<?php
	}

	$actualItems = $currChannel->getItemCount();
	$setItems = $rssitems;

	if ($setItems > $actualItems) {
		$totalItems = $actualItems;
	} else {
		$totalItems = $setItems;
	}

	?>
	<tr>
		<td>
		<ul class="newsfeed<?php echo $moduleclass_sfx; ?>">
		<?php
		for ($j = 0; $j < $totalItems; $j++) {
			$currItem =& $currChannel->getItem($j);
			// item title
			?>
			<li class="newsfeed<?php echo $moduleclass_sfx; ?>">
			<strong>
			<a href="<?php echo $currItem->getLink(); ?>" target="_blank">
			<?php echo $currItem->getTitle(); ?>
			</a>
			</strong>
			<?php
			// item description
			if ( $rssitemdesc ) {
				// item description
				$text 	= html_entity_decode( $currItem->getDescription() );

				// word limit check
				if ( $words ) {
					$texts = explode( ' ', $text );
					$count = count( $texts );
					if ( $count > $words ) {
						$text = '';
						for( $i=0; $i < $words; $i++ ) {
							$text .= ' '. $texts[$i];
						}
						$text .= '...';
					}
				}
				?>
				<div>
				<?php echo $text; ?>
				</div>
				<?php
			}
			?>
			</li>
			<?php
		}
		?>
		</ul>
		</td>
	</tr>
	<?php
}
?>
</table>