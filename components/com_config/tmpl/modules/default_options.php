<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Config\Site\View\Modules\HtmlView $this */
$fieldSets = $this->form->getFieldsets('params');

echo HTMLHelper::_('bootstrap.startAccordion', 'collapseTypes');
$i = 0;

foreach ($fieldSets as $name => $fieldSet) :
    $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MODULES_' . strtoupper($name) . '_FIELDSET_LABEL';
    $class = isset($fieldSet->class) && !empty($fieldSet->class) ? $fieldSet->class : '';


    if (isset($fieldSet->description) && trim($fieldSet->description)) :
        echo '<p class="tip">' . $this->escape(Text::_($fieldSet->description)) . '</p>';
    endif;
    ?>
    <?php echo HTMLHelper::_('bootstrap.addSlide', 'collapseTypes', Text::_($label), 'collapse' . ($i++)); ?>

<ul class="nav flex-column">
    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
    <li>
        <?php // If multi-language site, make menu-type selection read-only ?>
        <?php if (Multilanguage::isEnabled() && $this->item['module'] === 'mod_menu' && $field->getAttribute('name') === 'menutype') : ?>
            <?php $field->readonly = true; ?>
        <?php endif; ?>
        <?php echo $field->renderField(); ?>
    </li>

    <?php endforeach; ?>
</ul>

    <?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
<?php endforeach; ?>
<?php echo HTMLHelper::_('bootstrap.endAccordion'); ?>
