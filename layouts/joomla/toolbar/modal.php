<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
$class    = isset($displayData['class']) ? $displayData['class'] : 'btn btn-sm';
$icon     = isset($displayData['icon']) ? $displayData['icon'] : 'out-3';
$text     = isset($displayData['text']) ? $displayData['text'] : '';

// Render the modal
echo JHtml::_('bootstrap.renderModal',
	'modal_'. $selector,
	array(
		'url'         => $displayData['doTask'],
		'title'       => $text,
		'height' => '100%',
		'width'  => '100%',
		'modalWidth'  => '80',
		'bodyHeight'  => '60',
		'closeButton' => true,
		'footer'      => '<button class="btn btn-secondary" data-dismiss="modal">' . JText::_('JCANCEL') . '</button>'
	)
);
?>
<button onclick="jQuery('#modal_'<?php echo $selector; ?>).modal('show')" class="btn" data-toggle="modal" title="<?php echo $text; ?>">
	<span class="icon-<?php echo $icon; ?>"></span><?php echo $text; ?>
</button>
