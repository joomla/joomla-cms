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
/**
 * Restore Defaults in Shortcut plugin to add accessible keyboard navigation to the site and administrator templates.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldRestoredefaults extends FormField
{
	/**
	 * The Form Field type
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Restoredefaults';

	/**
	 * Method to get the input
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getInput()
	{
		/** @var $app CMSWebApplicationInterface */
		$app    = Factory::getApplication();
		$wa     = $app->getDocument()->getWebAssetManager();
		$return = '';

		if (!$wa->assetExists('script', 'restoredefaults'))
		{
			$wa->registerScript('restoredefaults', 'media/plg_system_shortcut/js/restoredefaults.js', [], ['defer' => true , 'type' => 'module']);
		}

		$wa->useScript('restoredefaults');

		$return .= '<button class="restoreDefaultsBtn btn btn-primary" type="button" data-class="' . $this->class . '">' . Text::_('Reset') . '</button>';

		return $return;
	}
}
