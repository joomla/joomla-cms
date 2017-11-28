<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

echo JHtml::_(
	'bootstrap.renderModal',
	'versionsModal',
	array(
		'url'    => "index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;item_id="
			. (int) $displayData['itemId']. "&amp;type_id=" . $displayData['typeId'] . "&amp;type_alias=" . $displayData['typeAlias']
			. "&amp;" . JSession::getFormToken() . "=1",
		'title'  => $displayData['title'],
		'height' => '100%',
		'width'  => '100%',
		'modalWidth'  => '80',
		'bodyHeight'  => '60',
		'footer' => '<a type="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
			. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>'
	)
);

$id = isset($displayData['id']) ? $displayData['id'] : '';

?>
<button<?php echo $id; ?> onclick="jQuery('#versionsModal').modal('show')" class="btn btn-sm btn-outline-primary" data-toggle="modal" title="<?php echo $displayData['title']; ?>">
	<span class="fa fa-code-fork" aria-hidden="true"></span><?php echo $displayData['title']; ?>
</button>
