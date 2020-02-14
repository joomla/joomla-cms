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

?>
<span class="modified">
	<time datetime="<?php echo HTMLHelper::_('date', $displayData['item']->modified, 'c'); ?>" itemprop="dateModified">
		<?php echo Text::sprintf('COM_CONTENT_LAST_UPDATED', HTMLHelper::_('date', $displayData['item']->modified, Text::_('DATE_FORMAT_LC3'))); ?>
	</time>
</span>
