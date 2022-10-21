<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$fieldSets = $this->form->getFieldsets('params');
?>

<legend><?php echo Text::_('COM_CONFIG_TEMPLATE_SETTINGS'); ?></legend>

<?php

// Search for com_config field set
if (!empty($fieldSets['com_config'])) {
    echo $this->form->renderFieldset('com_config');
} else {
    // Fall-back to display all in params
    foreach ($fieldSets as $name => $fieldSet) {
        $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CONFIG_' . $name . '_FIELDSET_LABEL';

        if (isset($fieldSet->description) && trim($fieldSet->description)) {
            echo '<p class="tip">' . $this->escape(Text::_($fieldSet->description)) . '</p>';
        }

        echo $this->form->renderFieldset($name);
    }
}
