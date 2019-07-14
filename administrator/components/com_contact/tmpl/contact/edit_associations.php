<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$hasAssoc = !($this->form->getValue('language', null, '*') === '*');

?>
<?php if ($hasAssoc) : ?>
<fieldset id="fieldset-associations" class="options-fieldset option-fieldset-full">
	<legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
<?php endif; ?>
	<?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
<?php if ($hasAssoc) : ?>
</fieldset>
<?php endif; ?>
