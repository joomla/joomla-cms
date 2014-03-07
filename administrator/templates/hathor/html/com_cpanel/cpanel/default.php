<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

echo JHtml::_('sliders.start', 'panel-sliders', array('useCookie' => '1'));
if (JFactory::getUser()->authorise('core.manage', 'com_postinstall')) :
	if ($this->postinstall_message_count):
		echo JHtml::_('sliders.panel', JText::_('COM_CPANEL_MESSAGES_TITLE'), 'cpanel-panel-com-postinstall');
	?>
		<div class="modal-body">
			<p>
				<?php echo JText::_('COM_CPANEL_MESSAGES_BODY_NOCLOSE'); ?>
			</p>
			<p>
				<?php echo JText::_('COM_CPANEL_MESSAGES_BODYMORE_NOCLOSE'); ?>
			</p>
		</div>
		<div class="modal-footer">
			<button onclick="window.location='index.php?option=com_postinstall&eid=700'; return false" class="btn btn-primary btn-large" >
				<?php echo JText::_('COM_CPANEL_MESSAGES_REVIEW'); ?>
			</button>
		</div>
	<?php endif; ?>
<?php endif;

foreach ($this->modules as $module)
{
	$output = JModuleHelper::renderModule($module);
	$params = new JRegistry;
	$params->loadString($module->params);
	if ($params->get('automatic_title', '0') == '0')
	{
		echo JHtml::_('sliders.panel', $module->title, 'cpanel-panel-' . $module->name);
	}
	elseif (method_exists('mod'.$module->name.'Helper', 'getTitle'))
	{
		echo JHtml::_('sliders.panel', call_user_func_array(array('mod' . $module->name . 'Helper', 'getTitle'), array($params)), 'cpanel-panel-' . $module->name);
	}
	else
	{
		echo JHtml::_('sliders.panel', JText::_('MOD_' . $module->name . '_TITLE'), 'cpanel-panel-' . $module->name);
	}
	echo $output;
}

echo JHtml::_('sliders.end');
