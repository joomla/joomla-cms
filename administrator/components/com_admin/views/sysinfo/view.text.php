<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="systeminfo-' . date('c') . '.txt"');
		header('Cache-Control: must-revalidate');

		$data = $this->getLayoutData();

		$lines = array();

		foreach ($data as $sectionName => $section)
		{
			$customRenderingMethod = 'render' . ucfirst($sectionName);

			if (method_exists($this, $customRenderingMethod))
			{
				$lines[] = $this->$customRenderingMethod($section['title'], $section['data']);
			}
			else
			{
				$lines[] = $this->renderSection($section['title'], $section['data']);
			}
		}

		echo str_replace(JPATH_ROOT, 'xxxxxx', implode("\n\n", $lines));

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
			'info' => array(
				'title' => JText::_('COM_ADMIN_SYSTEM_INFORMATION', true),
				'data'  => $model->getSafeData('info')
			),
			'phpSettings' => array(
				'title' => JText::_('COM_ADMIN_PHP_SETTINGS', true),
				'data'  => $model->getSafeData('phpSettings')
			),
			'config' => array(
				'title' => JText::_('COM_ADMIN_CONFIGURATION_FILE', true),
				'data'  => $model->getSafeData('config')
			),
			'directories' => array(
				'title' => JText::_('COM_ADMIN_DIRECTORY_PERMISSIONS', true),
				'data'  => $model->getSafeData('directory', true)
			),
			'phpInfo' => array(
				'title' => JText::_('COM_ADMIN_PHP_INFORMATION', true),
				'data'  => $model->getSafeData('phpInfoArray')
			),
			'extensions' => array(
				'title' => JText::_('COM_ADMIN_EXTENSIONS', true),
				'data'  => $model->getSafeData('extensions')
			)
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

		$lines[] = $margin . '=============';
		$lines[] = $margin . $sectionName;
		$lines[] = $margin . '=============';
		$level++;

		foreach ($sectionData as $name => $value)
		{
			if (is_array($value))
			{
				if ($name == 'Directive')
				{
					continue;
				}

				$lines[] = '';
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
