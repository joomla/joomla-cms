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

$data = $displayData;
?>
<div class="alert alert-info">
	<?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'info']); ?>
	<span class="sr-only"><?php echo Text::_('INFO'); ?></span>
	<?php echo $data['options']['noResultsText']; ?>
</div>
