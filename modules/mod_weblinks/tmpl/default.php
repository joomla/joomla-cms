<?php
/**
 * @version		$Id: default.php 11952 2009-06-01 03:21:19Z robs $
 * @package		Joomla.Site
 * @subpackage	mod_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="weblinks<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) :	?>
<li>
		<?php
					$link	= JRoute::_('index.php?task=weblink.go&catid='.$item->catslug.'&id='. $item->slug);
					switch ($params->get('target', 3))
					{
						case 1:
							// open in a new window
							echo '<a href="'. $link .'" target="_blank" rel="'.$params->get('follow', 'no follow').'">'.
								htmlspecialchars($item->title) .'</a>';
							break;
	
						case 2:
							// open in a popup window
							echo "<a href=\"#\" onclick=\"javascript: window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\">".
								htmlspecialchars($item->title) .'</a>';

							break;
	
						default:
							// open in parent window
							echo '<a href="'. $link .'" rel="'.$params->get('follow', 'no follow').'">'.
								htmlspecialchars($item->title) .'</a>';
							break;
					}
			?>
			<?php if ($params->get('description', 0)) : ?>
				<?php echo nl2br($item->description); ?>
			<?php endif; ?>

			<?php if ($params->get('hits', 0)) : ?>
				<?php echo '(' . $item->hits . ' ' . JText::_('Hits') . ')'; ?>
			<?php endif; ?>
			
		</li>	
</li>
<?php endforeach; ?>
</ul>
