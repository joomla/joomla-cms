<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
	 * @return  mixed  A string if successful, otherwise a Error object.
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

		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="systeminfo-' . microtime(true) . '.txt"');
		header('Cache-Control: must-revalidate');

		$data = $this->getLayoutData();

		$lines = array();

		foreach ($data as $sectionName => $sectionData)
		{
			$customRenderingMethod = 'render' . ucfirst($sectionName);

			if (method_exists($this, $customRenderingMethod))
			{
				$lines[] = $this->$customRenderingMethod($sectionName, $sectionData);
			}
			else
			{
				$lines[] = $this->renderSection($sectionName, $sectionData);
			}
		}

		echo implode("\n\n", $lines);

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
			'phpSettings' => $model->getSafeData('phpSettings'),
			'config'      => $model->getSafeData('config'),
			'info'        => $model->getSafeData('info'),
			'phpInfo'     => $model->getSafeData('phpInfoArray'),
			'directories' => $model->getSafeData('directory')
		);
	}

	/**
	 * Render a section
	 *
	 * @param   string   $sectionName  Name of the section to render
	 * @param   array    $sectionData  Data of the section to render
	 * @param   integer  $level        Depth level for indentation
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	protected function renderSection($sectionName, $sectionData, $level = 0)
	{
		$lines = array();

		$margin = ($level > 0) ? str_repeat("\t", $level) : null;

		$lines[] = $margin . "=============";
		$lines[] = $margin . $sectionName;
		$lines[] = $margin . "=============";
		$level++;

		foreach ($sectionData as $name => $value)
		{
			if (is_array($value))
			{
				if ($name == 'Directive')
				{
					continue;
				}

				$lines[] = "";
				$lines[] = $this->renderSection($name, $value, $level);
			}
			else
			{
				if (is_bool($value))
				{
					$value = $value ? 'true' : 'false';
				}

				if (is_int($name) && ($name == 0 || $name == 1))
				{
					$name = ($name == 0 ? 'Local Value' : 'Master Value');
				}

				$lines[] = $margin . $name . ': ' . $value;
			}
		}

		return implode("\n", $lines);
	}

	/**
	 * Specific rendering for directories
	 *
	 * @param   string   $sectionName  Name of the section
	 * @param   array    $sectionData  Directories information
	 * @param   integer  $level        Starting level
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	protected function renderDirectories($sectionName, $sectionData, $level = -1)
	{
		foreach ($sectionData as $directory => $data)
		{
			$sectionData[$directory] = $data['writable'] ? ' writable' : ' NOT writable';
		}

		return $this->renderSection($sectionName, $sectionData, $level);
	}
}
