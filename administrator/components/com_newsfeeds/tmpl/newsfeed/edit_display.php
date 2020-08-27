<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$this->fieldset = 'jbasic';
?>

<fieldset id="fieldset-display" class="options-form">
	<legend><?php echo Text::_('JGLOBAL_FIELDSET_DISPLAY_OPTIONS'); ?></legend>
	<div>
	<?php echo LayoutHelper::render('joomla.edit.fieldset', $this); ?>
	</div>
</fieldset>
