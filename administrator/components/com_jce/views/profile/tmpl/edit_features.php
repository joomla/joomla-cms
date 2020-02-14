<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 */
defined('_JEXEC') or die;

$this->name = JText::_('WF_PROFILES_FEATURES_LAYOUT');
$this->fieldsname = 'features';
echo JLayoutHelper::render('joomla.content.options_default', $this);
?>

<div class="form-horizontal">
    <?php echo JLayoutHelper::render('edit.layout', $this);?>
    <?php echo JLayoutHelper::render('edit.additional', $this);?>
</div>
<input type="hidden" name="jform[plugins]" value="" />
<input type="hidden" name="jform[rows]" value="" />