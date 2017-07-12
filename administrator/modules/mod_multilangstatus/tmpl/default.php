<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_multilangstatus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include jQuery
JHtml::_('jquery.framework');

JHtml::_('script', 'mod_multilangstatus/admin-multilangstatus.min.js', array('version' => 'auto', 'relative' => true));
?>

<li class="nav-item multilanguage">
	<a class="nav-link" href="#multiLangModal" title="<?php echo JText::_('MOD_MULTILANGSTATUS'); ?>" data-toggle="modal" role="button">
		<span class="fa fa-language" aria-hidden="true"></span>
		<span class="sr-only"><?php echo JText::_('MOD_MULTILANGSTATUS'); ?></span>
	</a>
</li>

<?php echo JHtml::_(
	'bootstrap.renderModal',
	'multiLangModal',
	array(
		'title'      => JText::_('MOD_MULTILANGSTATUS'),
		'url'        => JRoute::_('index.php?option=com_languages&view=multilangstatus&tmpl=component'),
		'height'     => '400px',
		'width'      => '800px',
		'bodyHeight' => 70,
		'modalWidth' => 80,
		'footer'     => '<a class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">' . JText::_('JTOOLBAR_CLOSE') . '</a>',
	)
);
