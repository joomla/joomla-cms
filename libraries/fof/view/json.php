<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.view');

/**
 * FrameworkOnFramework JSON View class
 *
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFViewJson extends FOFViewHtml
{

	protected function onDisplay($tpl = null)
	{
		// Load the model
		$model = $this->getModel();

		$items = $model->getItemList();
		$this->assignRef('items', $items);

		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');

		if (is_null($tpl))
		{
			$tpl = 'json';
		}

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			JError::setErrorHandling(E_ALL, 'ignore');
		}
		$hasFailed = false;
		try
		{
			$result = $this->loadTemplate($tpl, true);
			if ($result instanceof Exception)
			{
				$hasFailed = true;
			}
		}
		catch (Exception $e)
		{
			$hasFailed = true;
		}

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			if ($result instanceof Exception)
			{
				$hasFailed = true;
			}
			JError::setErrorHandling(E_WARNING, 'callback');
		}

		if ($hasFailed)
		{
			// Default JSON behaviour in case the template isn't there!
			$json = json_encode($items);

			// JSONP support
			$callback = $this->input->getVar('callback', null);
			if (!empty($callback))
			{
				echo $callback . '(' . $json . ')';
			}
			else
			{
				$defaultName = $this->input->getCmd('view', 'joomla');
				$filename = $this->input->getCmd('basename', $defaultName);

				$document->setName($filename);
				echo $json;
			}

			return false;
		}
		else
		{
			echo $result;
			return false;
		}
	}

	protected function onRead($tpl = null)
	{
		$model = $this->getModel();

		$item = $model->getItem();
		$this->assign('item', $item);

		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');

		if (is_null($tpl))
		{
			$tpl = 'json';
		}

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			JError::setErrorHandling(E_ALL, 'ignore');
		}

		$hasFailed = false;
		try
		{
			$result = $this->loadTemplate($tpl, true);
		}
		catch (Exception $e)
		{
			$hasFailed = true;
		}

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			if ($result instanceof Exception)
			{
				$hasFailed = true;
			}
			JError::setErrorHandling(E_WARNING, 'callback');
		}

		if ($hasFailed)
		{
			// Default JSON behaviour in case the template isn't there!
			$json = json_encode($item);

			// JSONP support
			$callback = $this->input->get('callback', null);
			if (!empty($callback))
			{
				echo $callback . '(' . $json . ')';
			}
			else
			{
				$defaultName = $this->input->getCmd('view', 'joomla');
				$filename = $this->input->getCmd('basename', $defaultName);
				$document->setName($filename);
				echo $json;
			}

			return false;
		}
		else
		{
			echo $result;
			return false;
		}
	}

}