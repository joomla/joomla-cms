<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.form.formfield');

class JFormFieldRestoredefaults extends JFormField
{
	protected $type = 'Restoredefaults';

	public function getInput()
	{
		$return = '';
		$app = Factory::getApplication();
		$wa = $app->getDocument()->getWebAssetManager();

		if (!$wa->assetExists('script', 'restoredefaults'))
		{
			$wa->registerScript('restoredefaults', 'media/plg_system_shortcut/js/restoredefaults.js', [], ['defer' => true , 'type' => 'module']);
		}

		$wa->useScript('restoredefaults');

		$return .= '<button class="restoreDefaultsBtn btn btn-secondary" type="button" data-class="' . $this->class . '">' . JText::_('PLG_SYSTEM_SHORTCUT_BUTTON_RESET_LBL') . '</button>';

		return $return;
	}
}
