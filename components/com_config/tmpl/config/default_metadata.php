<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


?>
<fieldset>

    <legend><?php echo $this->text('COM_CONFIG_METADATA_SETTINGS'); ?></legend>

    <?php foreach ($this->form->getFieldset('metadata') as $field) : ?>
        <div class="mb-3">
            <?php echo $field->label; ?>
            <?php echo $field->input; ?>
            <?php if ($field->description) : ?>
                <div class="form-text hide-aware-inline-help d-none" id="<?php echo $field->id ?>-desc">
                    <?php echo $this->text($field->description) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</fieldset>
