<?php
/**
* @version $Id: mod_rssfeed.php 588 2005-10-23 15:20:09Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

//check if cache diretory is writable as cache files will be created for the feed
$cacheDir = $mosConfig_cachepath.DS;
if (!is_writable($cacheDir))
{
	echo '<div>';
	echo JText::_('Please make cache directory writable.');
	echo '</div>';
	return;
}

// module params
$moduleclass_sfx	= $params->get('moduleclass_sfx');
$rssurl				= $params->get('rssurl', '');
$rssitems			= $params->get('rssitems', 5);
$rssdesc			= $params->get('rssdesc', 1);
$rssimage			= $params->get('rssimage', 1);
$rssitemdesc		= $params->get('rssitemdesc', 1);
$words				= $params->def('word_count', 0);
$rsstitle			= $params->get('rsstitle', 1);
$rssrtl				= $params->get('rssrtl', 0);


if (empty ($rssurl))
{
	echo '<div>';
	echo JText::_('No feed URL specified.');
	echo '</div>';
	return;
}


//  get RSS parsed object
$options = array();
$options['rssUrl'] = $rssurl;
$options['cache_time'] = 3600;

$rssDoc = JFactory::getXMLparser('RSS', $options);

if ($rssDoc != false)
{
	// feed elements
	$currChannel	= $rssDoc->channel;
	$image			= $rssDoc->image;
	$items 			= $rssDoc->items;
	$iUrl = 0;

	//image handling
	$iUrl = isset($image['url']) ? $image['url'] : null;
	$iTitle = isset($image['title']) ? $image['title'] : null;

	// feed title
	?>
	<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?>">
	<table cellpadding="0" cellspacing="0" class="moduletable<?php echo $moduleclass_sfx; ?>">
	<?php

	// feed description
	if (!is_null( $currChannel['title'] ) && $rsstitle)
	{
	?>
		<tr>
			<td>
				<strong>
				<a href="<?php echo ampReplace( $currChannel['link'] ); ?>" target="_blank">
					<?php echo $currChannel['title']; ?></a>
				</strong>
			</td>
		</tr>
	<?php
	}

	// feed description
	if ($rssdesc)
	{
	?>
		<tr>
			<td>
				<?php echo $currChannel['description']; ?>
			</td>
		</tr>
	<?php
	}

	// feed image
	if ($rssimage && $iUrl)
	{
	?>
		<tr>
			<td align="center">
				<image src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/>
			</td>
		</tr>
	<?php
	}

	$actualItems = count( $items );
	$setItems = $rssitems;

	if ($setItems > $actualItems)
	{
		$totalItems = $actualItems;
	}
	else
	{
		$totalItems = $setItems;
	}
	?>
	<tr>
		<td>
			<ul class="newsfeed<?php echo $moduleclass_sfx; ?>"  >
	<?php
	for ($j = 0; $j < $totalItems; $j ++)
	{
		$currItem = & $items[$j];
		// item title
		?>
			<li >
		<?php

		if ( !is_null( $currItem['link'] ) )
		{
		?>
					<a href="<?php echo $currItem['link']; ?>" target="_child">
					<?php echo $currItem['title']; ?>
					</a>
				<?php
		}

		// item description
		if ($rssitemdesc)
		{
			// item description
			$text = html_entity_decode($currItem['description']);
			$text = str_replace('&apos;', "'", $text);

			// word limit check
			if ($words)
			{
				$texts = explode(' ', $text);
				$count = count($texts);
				if ($count > $words)
				{
					$text = '';
					for ($i = 0; $i < $words; $i ++)
					{
						$text .= ' '.$texts[$i];
					}
					$text .= '...';
				}
			}
			?>
					<div style="text-align: <?php echo $rssrtl ? 'right': 'left'; ?> ! important">
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
	</table>
	</div>
		<?php
}
?>