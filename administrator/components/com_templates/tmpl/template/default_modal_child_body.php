<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

Factory::getDocument()->getWebAssetManager()->usePreset('choicesjs');

// Generate a list of styles for the child creation modal
$options = [];

if (count($this->styles) > 0) {
    foreach ($this->styles as $style) {
        $options[] = HTMLHelper::_('select.option', $style->id, $style->title, 'value', 'text');
    }
}

$fancySelectData = [
    'autocomplete'   => 'off',
    'autofocus'      => false,
    'class'          => '',
    'description'    => '',
    'disabled'       => false,
    'group'          => false,
    'id'             => 'style_ids',
    'hidden'         => false,
    'hint'           => '',
    'label'          => '',
    'labelclass'     => '',
    'onchange'       => '',
    'onclick'        => '',
    'multiple'       => true,
    'pattern'        => '',
    'readonly'       => false,
    'repeat'         => false,
    'required'       => false,
    'size'           => 4,
    'spellcheck'     => false,
    'validate'       => '',
    'value'          => '0',
    'options'        => $options,
    'dataAttributes' => [],
    'dataAttribute'  => '',
    'name'           => 'style_ids[]',
];
?>
<div id="template-manager-copy" class="container-fluid">
    <div class="mt-2">
        <div class="col-md-12">
            <div class="control-group">
                <div class="control-label">
                    <label for="new_name">
                        <?php echo Text::_('COM_TEMPLATES_TEMPLATE_CHILD_NAME_LABEL'); ?>
                    </label>
                </div>
                <div class="controls">
                    <input class="form-control" type="text" id="new_name" name="new_name" required>
                    <small class="form-text">
                        <?php echo Text::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_DESC'); ?>
                    </small>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <label for="style_ids">
                        <?php echo Text::_('COM_TEMPLATES_TEMPLATE_CHILD_STYLE_LABEL'); ?>
                    </label>
                </div>
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.form.field.list-fancy-select', $fancySelectData); ?>
                    <small class="form-text">
                        <?php echo Text::_('COM_TEMPLATES_TEMPLATE_NEW_STYLE_DESC'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
