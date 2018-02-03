<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var array $displayData
 */

$entries    = array_key_exists('entries', $displayData) ? $displayData['entries'] : [];
$options    = array_key_exists('options', $displayData) ? $displayData['options'] : [];
$label      = array_key_exists('label', $options) ? $options['label'] : 'Submenu';

$option     = Factory::getApplication()->input->get('option');
$elementId  = 'js-submenu-'.$option;

HTMLHelper::_('jquery.framework');

Factory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function($) {
		$("#'.$elementId.'").change(function() {
		    window.location.href = $(this).val();
        });
	});
');

if (count($entries) > 0) { ?>
<div>
    <label for="<?php echo $elementId; ?>" class="sr-only" ><?php echo $label;?></label>
    <select name="submenu" id="<?php echo $elementId; ?>" class="custom-select custom-select-sm">
        <?php foreach ($entries as $entry) {
            $selected = $entry[2] ? ' selected="selected"' : '';
            ?>
            <option value="<?php echo JUri::base(). htmlentities($entry[1], ENT_COMPAT); ?>" <?php echo $selected; ?>>
                <?php echo htmlspecialchars($entry[0]); ?>
            </option>
        <?php } ?>
    </select>
</div>
<?php } ?>
