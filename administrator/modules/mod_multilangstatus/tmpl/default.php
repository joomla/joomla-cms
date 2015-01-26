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
	JHtml::_('bootstrap.modal');

	JFactory::getDocument()->addStyleDeclaration('.navbar-fixed-bottom {z-index:1050;}');

	$link = JRoute::_('index.php?option=com_languages&view=multilangstatus&tmpl=component');
?>
<div class="btn-group multilanguage">
	<a href="#multiLangModal" role="button" class="btn btn-link" data-toggle="modal" title="<?php echo JText::_('MOD_MULTILANGSTATUS'); ?>">
		<i class="icon-comment"></i>
		<?php echo JText::_('MOD_MULTILANGSTATUS'); ?>
	</a>
</div>

<?php echo JHtmlBootstrap::renderModal('multiLangModal', array( 'url' => $link, 'title' => JText::_('MOD_MULTILANGSTATUS'),'height' => '300px', 'width' => '500px'));
