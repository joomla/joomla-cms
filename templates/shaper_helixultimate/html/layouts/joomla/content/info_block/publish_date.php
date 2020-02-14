<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
$articleView = $displayData['articleView'];
?>
<span class="published" title="<?php echo Text::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', HTMLHelper::_('date', $displayData['item']->publish_up, Text::_('DATE_FORMAT_LC3'))); ?>">
	<time datetime="<?php echo HTMLHelper::_('date', $displayData['item']->publish_up, 'c'); ?>"<?php echo ($articleView == 'details') ? ' itemprop="datePublished"' : ''; ?>>
		<?php echo HTMLHelper::_('date', $displayData['item']->publish_up, Text::_('DATE_FORMAT_LC3')); ?>
	</time>
</span>
