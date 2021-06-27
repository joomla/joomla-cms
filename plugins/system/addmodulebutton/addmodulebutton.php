<?php
/**
 * Place Module Button
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * @since  __DEPLOY_VERSION__
 */

defined('_JEXEC') or die;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\WebAsset\WebAssetManager;
/**
 * Displays the Add Module button for Frontend Placement.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemAddModuleButton extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Listener for the `onBeforeRender` event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onBeforeRender()
	{

		if ($this->app->isClient('administrator'))
		{
			return;
		}

		// Frontend Module Placement Variables
		$canCreateModules 	= ContentHelper::getActions('com_modules')->get('core.create');
		$canEditModules 	= ContentHelper::getActions('com_modules')->get('core.edit');
		$placeModules		= $this->app->input->getBool('pm');
		$editPosition		= $this->app->input->getBool('edit');
		$showAddModuleBtn 	= $canCreateModules && !$this->app->input->getBool('tp') && !$placeModules;

		// Display Warning message when user is not logged in or does not have permissions
		if ($placeModules)
		{
			if ($editPosition && !$canEditModules)
			{
				$this->app->enqueueMessage(Text::sprintf('PLG_SYSTEM_ADD_MODULE_BUTTON_EDIT_MODULE_PERMISSIONS_WARNING'), 'warning');

				return;
			}
			elseif (!$canCreateModules)
			{
				$this->app->enqueueMessage(Text::sprintf('PLG_SYSTEM_ADD_MODULE_BUTTON_CREATE_MODULE_PERMISSIONS_WARNING'), 'warning');

				return;
			}
		}

		// Display the Add Module Button
		if ($showAddModuleBtn)
		{
			// Add Script Options to pass the Button label Language Constant
			$this->app->getDocument()->addScriptOptions('js-addModuleBtn', Text::_('PLG_SYSTEM_ADD_MODULE_BUTTON_LABEL'));

			// Script for appending the Add Module Button
			$this->app->getDocument()->getWebAssetManager()
				->registerAndUseScript('plg_system_addmodulebutton_js', 'media/plg_system_addmodulebutton/js/addmodulebutton.js', [], ['defer' => true]);
		}
	}
}
