<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JHtml::_('behavior.core');
JHtml::_('jquery.framework');

echo JHtml::_(
	'bootstrap.renderModal',
	'ModalHelp',
	array(
		'title'      => $displayData['title'],
		'url'        => $displayData['url'],
		'height'     => '400px',
		'width'      => '800px',
		'bodyHeight' => '70',
		'modalWidth' => '80',
		'footer'     => '<a role="button" class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>',
	)
);
?>
<button href="#ModalHelp" data-toggle="modal" role="button" rel="help" class="btn btn-small">
	<span class="icon-question-sign"></span>
	<?php echo $displayData['text']; ?>
</button>
