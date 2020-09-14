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

?>
<div class="toggle-editor btn-toolbar float-right clearfix mt-3">
	<div class="btn-group">
		<button type="button" disabled class="btn btn-secondary js-tiny-toggler-button">
			<?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'eye-open']); ?>
			<?php echo Text::_('PLG_TINY_BUTTON_TOGGLE_EDITOR'); ?>
		</button>
	</div>
</div>
