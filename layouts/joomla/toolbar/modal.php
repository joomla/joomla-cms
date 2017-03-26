<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Generic toolbar button layout to open a modal
 * -----------------------------------------------
 * @param   array   $displayData    Button parameters. Default supported parameters:
 *                                  - selector  string  Unique DOM identifier for the modal. CSS id without #
 *                                  - class     string  Button class
 *                                  - icon      string  Button icon
 *                                  - text      string  Button text
 */

$selector = $displayData['selector'];
$class    = isset($displayData['class']) ? $displayData['class'] : 'btn btn-secondary btn-sm';
$icon     = isset($displayData['icon']) ? $displayData['icon'] : 'fa fa-download';
$text     = isset($displayData['text']) ? $displayData['text'] : '';

// Render the modal
echo JHtml::_('bootstrap.renderModal',
	'modal_'. $selector,
	array(
		'url'         => $displayData['doTask'],
		'title'       => $text,
		'height'      => '100%',
		'width'       => '100%',
		'modalWidth'  => 80,
		'bodyHeight'  => 60,
		'closeButton' => true,
		'footer'      => '<a class="btn btn-secondary" data-dismiss="modal" type="button"'
						. ' onclick="window.parent.jQuery(\'#modal_downloadModal\').modal(\'hide\');">'
						. JText::_("COM_BANNERS_CANCEL") . '</a>'
						. '<button class="btn btn-success" type="button"'
						. ' onclick="jQuery(\'#modal_downloadModal iframe\').contents().find(\'#exportBtn\').click();">'
						. JText::_("COM_BANNERS_TRACKS_EXPORT") . '</button>',
	)
);
?>
<button onclick="jQuery('#modal_<?php echo $selector; ?>').modal('show')" class="<?php echo $class; ?>" data-toggle="modal" title="<?php echo $text; ?>">
	<span class="icon-<?php echo $icon; ?>"></span><?php echo $text; ?>
</button>
