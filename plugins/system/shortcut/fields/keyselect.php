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
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  4.1
	 */
	protected $app;

	protected $type = 'Keyselect';

	public function getInput()
	{
		$return = '';

		HTMLHelper::_('bootstrap.modal');

		$app = Factory::getApplication();
		Factory::getDocument()->addScriptOptions('modal_header_title', 'Set Shortcut');
		Factory::getDocument()->addScriptOptions('modal_combination_text', 'Current Combination:');
		Factory::getDocument()->addScriptOptions('modal_new_combination_text', 'New Combination:');
		Factory::getDocument()->addScriptOptions('modal_cancel_button_text', 'Cancel');
		Factory::getDocument()->addScriptOptions('modal_save_button_text', 'Save Changes');

		$wa = $app->getDocument()->getWebAssetManager();

		if (!$wa->assetExists('script', 'keyselectmodal'))
		{
			$wa->registerScript('keyselectmodal', 'media/plg_system_shortcut/js/keyselect.js', [], ['defer' => true , 'type' => 'module']);
		}

		$wa->useScript('keyselectmodal');

		$return .= '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value . '" /><button id="' . $this->id . '_btn" class="keySelectBtn btn btn-secondary ' . $this->class . '" type="button" data-class="' . $this->class . '">' . $this->value . '</button>';

		return $return;
	}
}