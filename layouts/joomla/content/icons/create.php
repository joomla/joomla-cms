<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$params = $displayData['params'];

?>
<?php if ($params->get('show_icons')) : ?>
	<?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'new', 'fixed' => true]); ?>
	<?php echo Text::_('JNEW'); ?>
<?php else : ?>
	<?php echo Text::_('JNEW') . '&#160;'; ?>
<?php endif; ?>
