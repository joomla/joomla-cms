<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$form = $displayData->getForm();

// JLayout for standard handling of metadata fields in the administrator content edit screens.
$fieldSets = $form->getFieldsets('metadata');
?>

<?php foreach ($fieldSets as $name => $fieldSet) : ?>
    <?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo $this->escape(Text::_($fieldSet->description)); ?>
        </div>
    <?php endif; ?>

    <?php
    // Include the real fields in this panel.
    if ($name === 'jmetadata') {
        echo $form->renderField('metadesc');
        echo $form->renderField('metakey');
    }

    foreach ($form->getFieldset($name) as $field) {
        if ($field->name !== 'jform[metadata][tags][]') {
            echo $field->renderField();
        }
    } ?>
<?php endforeach; ?>
