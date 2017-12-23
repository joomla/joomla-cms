<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Admin\Administrator\View\Sysinfo;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\MVC\View\AbstractView;

/**
 * Sysinfo View class for the Admin component
 *
 * @since  3.5
 */
class JsonView extends AbstractView
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
		if (!\JFactory::getUser()->authorise('core.admin'))
		{
			throw new Notallowed(\JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		header('MIME-Version: 1.0');
		header('Content-Disposition: attachment; filename="systeminfo-' . date('c') . '.json"');
		header('Content-Transfer-Encoding: binary');

		$data = $this->getLayoutData();

		echo json_encode($data);

		\JFactory::getApplication()->close();
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
		/* @var \Joomla\Component\Admin\Administrator\Model\SysInfoModel $model */
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
