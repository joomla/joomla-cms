<?php
/**
 * @version   ${version}
 * @package   ${package}
 * @copyright Copyright (C) 2015 Mathew Lenning. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @author    Mathew Lenning - http://babel-university.com/
 */
 
 // No direct access
defined('_JEXEC') or die;

/**
 * Class ConfigControllerFix
 * Controller to remove the root_user value from the configuration.
 * Since this controller does not use the session or the inputs and
 * and only removes the root_user key before saving the configuration
 * We don't need to check the session.
 */
class ConfigControllerFix extends JControllerAdministrate
{
	public function execute()
	{
		/** @var ConfigModelApplication $model */
		$model = $this->getModel('application');

		/** @var JRegistry $config */
		$config = $model->getItem()->toArray();

		$user = JFactory::getUser();
		$isRootUser = ($user->username === $config['root_user'] || $user->id == $config['root_user']);
		if(!$isRootUser)
		{
			throw new ErrorException(JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$model->update($config, true);

		/** @var JApplicationCms $app */

		$this->setReturn('index.php?Itemid=0');
		return $this->executeController();
	}
}