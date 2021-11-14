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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
/**
 * KeySelect in Shortcut plugin to add accessible keyboard navigation to the site and administrator templates.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldKeyselect extends FormField
{
	/**
	 * The Form Field type
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Keyselect';

	/**
	 * Method to get the input
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getInput()
	{
		HTMLHelper::_('bootstrap.modal');

		/** @var $app CMSWebApplicationInterface */
		$app = Factory::getApplication();
		$wa = $app->getDocument()->getWebAssetManager();

		if (!$wa->assetExists('script', 'keyselectmodal'))
		{
			$document = $app->getDocument();
			$document->addScriptOptions('set_shorcut_text', Text::_("Set Shortcut"));
			$document->addScriptOptions('current_combination_text', Text::_("Current Combination"));
			$document->addScriptOptions('new_combination_text', Text::_("New Combination"));
			$document->addScriptOptions('cancel_button_text', Text::_("Cancel"));
			$document->addScriptOptions('save_button_text', Text::_("Save"));
			$wa->registerScript('keyselectmodal', 'media/plg_system_shortcut/js/keyselect.js', [], ['defer' => true, 'type' => 'module']);
		}

		$wa->useScript('keyselectmodal');

		$return = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value . '" /><button id="' . $this->id . '_btn" class="keySelectBtn btn btn-secondary ' . $this->class . '" type="button" data-class="' . $this->class . '">' . $this->value . '</button>';

		return $return;
	}
}
