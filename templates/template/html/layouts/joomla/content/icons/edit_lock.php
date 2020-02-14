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

$tooltip = $displayData['tooltip'];
$legacy  = $displayData['legacy'];

?>
<?php if ($legacy) : ?>
	<span class="hasTooltip" title="<?php echo HTMLHelper::tooltipText($tooltip . '', 0); ?>">
		<?php echo HTMLHelper::_('image', 'system/checked_out.png', null, null, true); ?>
	</span>
	<?php echo Text::_('JLIB_HTML_CHECKED_OUT'); ?>
<?php else : ?>
	<span class="hasTooltip fa fa-lock" title="<?php echo HTMLHelper::tooltipText($tooltip . '', 0); ?>"></span>
	<?php echo Text::_('JLIB_HTML_CHECKED_OUT'); ?>
<?php endif; ?>
