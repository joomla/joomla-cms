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
$id         = array_key_exists('id', $displayData) ? $displayData['id'] : 'js-btn-subnavigation';
$targetId   = array_key_exists('target_id', $displayData) ? $displayData['target_id'] : '';
$label      = array_key_exists('label', $displayData) ? $displayData['label'] : \JText::_('JGLOBAL_TOGGLE_NAVIGATION');
$text       = array_key_exists('text', $displayData) ? $displayData['text'] : '&#8597;';

HTMLHelper::_('jquery.framework');

Factory::getDocument()->addScriptDeclaration('
jQuery(document).ready(function($) {
    $("#' . $id . '").on("click", function(){
        $("#' . $targetId . '").toggle();
    });
});'
);

?>
<button id="<?php echo $id; ?>" class="btn btn-outline-info btn-sm" type="button" data-target="#<?php echo $targetId; ?>" aria-controls="<?php echo $targetId; ?>" aria-expanded="false" aria-label="<?php echo $label; ?>">
<span class="fa fa-list-alt"></span>
<?php echo $text; ?>
</button>
