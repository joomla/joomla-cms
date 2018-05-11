<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$params = $displayData['params'];
$legacy = $displayData['legacy'];

?>
<?php if ($params->get('show_icons')) : ?>
	<?php if ($legacy) : ?>
		<?php // Checks template image directory for image, if none found default are loaded ?>
		<?php echo HTMLHelper::_('image', 'system/printButton.png', Text::_('JGLOBAL_PRINT'), null, true); ?>
	<?php else : ?>
		<span class="fa fa-print" aria-hidden="true"></span>
		<?php echo Text::_('JGLOBAL_PRINT'); ?>
	<?php endif; ?>
<?php else : ?>
	<?php echo Text::_('JGLOBAL_PRINT'); ?>
<?php endif; ?>
