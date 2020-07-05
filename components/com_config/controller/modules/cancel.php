<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cancel Controller for module editing
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigControllerModulesCancel extends ConfigControllerCanceladmin
{
	/**
	 * Method to cancel module editing.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check if the user is authorized to do this.
		$user = JFactory::getUser();

		if (!$user->authorise('module.edit.frontend', 'com_modules.module.' . $this->input->get('id')))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		$this->context = 'com_config.config.global';

		// Get returnUri
		$returnUri = $this->input->post->get('return', null, 'base64');

		if (!empty($returnUri))
		{
			$this->redirect = base64_decode(urldecode($returnUri));
		}
		else
		{
			$this->redirect = JUri::base();
		}

		$id = $this->input->getInt('id');

		// Access backend com_module
		JLoader::register('ModulesControllerModule', JPATH_ADMINISTRATOR . '/components/com_modules/controllers/module.php');
		JLoader::register('ModulesViewModule', JPATH_ADMINISTRATOR . '/components/com_modules/views/module/view.json.php');
		JLoader::register('ModulesModelModule', JPATH_ADMINISTRATOR . '/components/com_modules/models/module.php');

		$cancelClass = new ModulesControllerModule;

		$cancelClass->cancel($id);

		parent::execute();
	}
}
