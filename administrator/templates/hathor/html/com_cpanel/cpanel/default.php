<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

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
	echo JHtml::_('sliders.panel', $module->title, 'cpanel-panel-' . $module->name);
	echo $output;
}

echo JHtml::_('sliders.end');
