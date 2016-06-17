<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Sysinfo View class for the Admin component
 *
 * @since  3.5
 */
class AdminViewSysinfo extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   3.5
	 */
	public function display($tpl = null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		header('MIME-Version: 1.0');
		header('Content-Disposition: attachment; filename="systeminfo-' . date("c") . '.json"');
		header('Content-Transfer-Encoding: binary');

		$data = $this->getLayoutData();

		echo json_encode($data);

		JFactory::getApplication()->close();
	}

	/**
	 * Get the data for the view
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutData()
	{
		$model = $this->getModel();

		return array(
			'info'        => $model->getSafeData('info'),
			'phpSettings' => $model->getSafeData('phpSettings'),
			'config'      => $model->getSafeData('config'),
			'directories' => $model->getSafeData('directory', true),
			'phpInfo'     => $model->getSafeData('phpInfoArray'),
			'extensions'  => $model->getSafeData('extensions')
		);
	}
}
