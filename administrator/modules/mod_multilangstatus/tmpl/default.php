<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_multilangstatus
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include jQuery
JHtml::_('jquery.framework');

JFactory::getDocument()->addStyleDeclaration('.navbar-fixed-bottom {z-index:1050;}');

$link = JRoute::_('index.php?option=com_languages&view=multilangstatus&tmpl=component');
$footer = '<button class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JTOOLBAR_CLOSE') . '</a>';
?>
<div class="btn-group multilanguage">
	<a href="#multiLangModal" role="button" class="btn btn-link" data-toggle="modal" title="<?php echo JText::_('MOD_MULTILANGSTATUS'); ?>">
		<span class="icon-comment"></span>
		<?php echo JText::_('MOD_MULTILANGSTATUS'); ?>
	</a>
</div>

<?php echo JHtml::_(
	'bootstrap.renderModal',
	'multiLangModal',
	array(
		'title' => JText::_('MOD_MULTILANGSTATUS'),
		'backdrop' => 'static',
		'keyboard' => true,
		'closeButton' => true,
		'footer' => $footer,
		'url' => $link,
		'height' => '300px',
		'width' => '500px'
		)
	);
