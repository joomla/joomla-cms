<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;



class JFormFieldKeyselect extends JFormField
{
	protected $type = 'Keyselect';

	public function getInput()
	{
		HTMLHelper::_('bootstrap.modal');

		$app = Factory::getApplication();
		$wa = $app->getDocument()->getWebAssetManager();

		if (!$wa->assetExists('script', 'keyselectmodal'))
		{
			$document = Factory::getDocument();
			$document->addScriptOptions('set_shorcut_text', JText::_("PLG_SYSTEM_SHORTCUT_SET_SHORTCUT"));
			$document->addScriptOptions('current_combination_text', JText::_("PLG_SYSTEM_SHORTCUT_CURRENT_COMBINATION"));
			$document->addScriptOptions('new_combination_text', JText::_("PLG_SYSTEM_SHORTCUT_NEW_COMBINATION"));
			$document->addScriptOptions('cancel_button_text', JText::_("PLG_SYSTEM_SHORTCUT_CANCEL"));
			$document->addScriptOptions('save_button_text', JText::_("PLG_SYSTEM_SHORTCUT_SAVE_CHANGES"));
			$wa->registerScript('keyselectmodal', 'media/plg_system_shortcut/js/keyselect.js', [], ['defer' => true , 'type' => 'module']);
		}

		$wa->useScript('keyselectmodal');

		$return = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value . '" /><button id="' . $this->id . '_btn" class="keySelectBtn btn btn-secondary ' . $this->class . '" type="button" data-class="' . $this->class . '">' . $this->value . '</button>';

		return $return;
	}
}