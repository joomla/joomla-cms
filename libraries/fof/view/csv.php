<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  view
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework CSV View class. Automatically renders the data in CSV
 * format.
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
class FOFViewCsv extends FOFViewHtml
{
	/**
	 *  Should I produce a CSV header row.
	 *
	 * @var  boolean
	 */
	protected $csvHeader = true;

	/**
	 * The filename of the downloaded CSV file.
	 *
	 * @var  string
	 */
	protected $csvFilename = null;

	/**
	 * The columns to include in the CSV output. If it's empty it will be ignored.
	 *
	 * @var  array
	 */
	protected $csvFields = array();

	/**
	* Public constructor. Instantiates a FOFViewCsv object.
	*
	* @param   array  $config  The configuration data array
	*/
	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		parent::__construct($config);

		if (array_key_exists('csv_header', $config))
		{
			$this->csvHeader = $config['csv_header'];
		}
		else
		{
			$this->csvHeader = $this->input->getBool('csv_header', true);
		}

		if (array_key_exists('csv_filename', $config))
		{
			$this->csvFilename = $config['csv_filename'];
		}
		else
		{
			$this->csvFilename = $this->input->getString('csv_filename', '');
		}

		if (empty($this->csvFilename))
		{
			$view              = $this->input->getCmd('view', 'cpanel');
			$view              = FOFInflector::pluralize($view);
			$this->csvFilename = strtolower($view);
		}

		if (array_key_exists('csv_fields', $config))
		{
			$this->csvFields = $config['csv_fields'];
		}
	}

	/**
	* Executes before rendering a generic page, default to actions necessary for the Browse task.
	*
	* @param   string  $tpl  Subtemplate to use
	*
	* @return  boolean  Return true to allow rendering of the page
	*/
	protected function onDisplay($tpl = null)
	{
		// Load the model
		$model = $this->getModel();

		$items = $model->getItemList();
		$this->items = $items;

        $platform = FOFPlatform::getInstance();
		$document = $platform->getDocument();

		if ($document instanceof JDocument)
		{
			$document->setMimeEncoding('text/csv');
		}

		$platform->setHeader('Pragma', 'public');
        $platform->setHeader('Expires', '0');
        $platform->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $platform->setHeader('Cache-Control', 'public', false);
        $platform->setHeader('Content-Description', 'File Transfer');
        $platform->setHeader('Content-Disposition', 'attachment; filename="' . $this->csvFilename . '"');

		if (is_null($tpl))
		{
			$tpl = 'csv';
		}

		FOFPlatform::getInstance()->setErrorHandling(E_ALL, 'ignore');

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

		if (!$hasFailed)
		{
			echo $result;
		}
		else
		{
			// Default CSV behaviour in case the template isn't there!

			if (empty($items))
			{
				return;
			}

			$item    = array_pop($items);
			$keys    = get_object_vars($item);
			$keys    = array_keys($keys);
			$items[] = $item;
			reset($items);

			if (!empty($this->csvFields))
			{
				$temp = array();

				foreach ($this->csvFields as $f)
				{
					if (in_array($f, $keys))
					{
						$temp[] = $f;
					}
				}

				$keys = $temp;
			}

			if ($this->csvHeader)
			{
				$csv = array();

				foreach ($keys as $k)
				{
					$k = str_replace('"', '""', $k);
					$k = str_replace("\r", '\\r', $k);
					$k = str_replace("\n", '\\n', $k);
					$k = '"' . $k . '"';

					$csv[] = $k;
				}

				echo implode(",", $csv) . "\r\n";
			}

			foreach ($items as $item)
			{
				$csv  = array();
				$item = (array) $item;

				foreach ($keys as $k)
				{
					if (!isset($item[$k]))
					{
						$v = '';
					}
					else
					{
						$v = $item[$k];
					}

					if (is_array($v))
					{
						$v = 'Array';
					}
					elseif (is_object($v))
					{
						$v = 'Object';
					}

					$v = str_replace('"', '""', $v);
					$v = str_replace("\r", '\\r', $v);
					$v = str_replace("\n", '\\n', $v);
					$v = '"' . $v . '"';

					$csv[] = $v;
				}

				echo implode(",", $csv) . "\r\n";
			}
		}

		return false;
	}
}
