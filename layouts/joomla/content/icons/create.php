<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JHtml::_('bootstrap.tooltip');

$params = $displayData['params'];
$legacy = $displayData['legacy'];

?>
<?php if ($params->get('show_icons')) : ?>
	<?php if ($legacy) : ?>
		<?php echo JHtml::_('image', 'system/new.png', JText::_('JNEW'), null, true); ?>
	<?php else : ?>
		<span class="icon-plus" aria-hidden="true"></span>
		<?php echo JText::_('JNEW'); ?>
	<?php endif; ?>
<?php else : ?>
	<?php echo JText::_('JNEW') . '&#160;'; ?>
<?php endif; ?>
