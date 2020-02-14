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

$params = $displayData['params'];
$legacy = $displayData['legacy'];

?>
<?php if ($params->get('show_icons')) : ?>
	<?php if ($legacy) : ?>
		<?php echo HTMLHelper::_('image', 'system/new.png', Text::_('JNEW'), null, true); ?>
	<?php else : ?>
		<span class="fa fa-plus" aria-hidden="true"></span>
		<?php echo Text::_('JNEW'); ?>
	<?php endif; ?>
<?php else : ?>
	<?php echo Text::_('JNEW') . '&#160;'; ?>
<?php endif; ?>
