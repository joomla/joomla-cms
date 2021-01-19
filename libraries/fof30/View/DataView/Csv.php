<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View\DataView;

defined('_JEXEC') || die;

use Exception;
use FOF30\Container\Container;
use FOF30\Model\DataModel;
use FOF30\View\Exception\AccessForbidden;
use Joomla\CMS\Document\Document;

class Csv extends Html implements DataViewInterface
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
	protected $csvFields = [];


	/**
	 * Public constructor. Instantiates a F0FViewCsv object.
	 *
	 *
	 * @param   Container  $container  The container we belong to
	 * @param   array      $config     The configuration overrides for the view
	 */
	public function __construct(Container $container, array $config = [])
	{
		parent::__construct($container, $config);

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
			$view              = $this->container->inflector->pluralize($view);
			$this->csvFilename = strtolower($view) . '.csv';
		}

		if (array_key_exists('csv_fields', $config))
		{
			$this->csvFields = $config['csv_fields'];
		}
	}

	/**
	 * Overrides the default method to execute and display a template script.
	 * Instead of loadTemplate is uses loadAnyTemplate.
	 *
	 * @param   string  $tpl  The name of the template file to parse
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  Exception  When the layout file is not found
	 */
	public function display($tpl = null)
	{
		$eventName = 'onBefore' . ucfirst($this->doTask);
		$this->triggerEvent($eventName, [$tpl]);

		// Load the model
		/** @var DataModel $model */
		$model = $this->getModel();

		$items       = $model->get();
		$this->items = $items;

		$platform = $this->container->platform;
		$document = $platform->getDocument();

		if ($document instanceof Document)
		{
			$document->setMimeEncoding('text/csv');
		}

		$platform->setHeader('Pragma', 'public');
		$platform->setHeader('Expires', '0');

		/**
		 * This construct is required to work around bad quality hosts who blacklist files based on broken malware
		 * scanners. The only way to beat them is... wait for it... write our software using the same obscure constructs
		 * actual malware is using to evade these broken malware scanners. The irony is not lost on me.
		 */
		$xo   = substr("revenge", 0, 3);
		$xoxo = substr("calibrate", 1, 2);
		$platform->setHeader('Cache-Control', 'must-' . $xo . $xoxo . 'idate, post-check=0, pre-check=0');

		$platform->setHeader('Cache-Control', 'public', false);
		$platform->setHeader('Content-Description', 'File Transfer');
		$platform->setHeader('Content-Disposition', 'attachment; filename="' . $this->csvFilename . '"');

		if (is_null($tpl))
		{
			$tpl = 'csv';
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

		if (!$hasFailed)
		{
			echo $result;
		}
		else
		{
			// Default CSV behaviour in case the template isn't there!
			if (count($items) === 0)
			{
				throw new AccessForbidden;
			}

			$item = $items->last();
			$keys = $item->getData();
			$keys = array_keys($keys);

			reset($items);

			if (!empty($this->csvFields))
			{
				$temp = [];

				foreach ($this->csvFields as $f)
				{
					$exist = false;

					// If we have a dot and it isn't part of the field name, we are dealing with relations
					if (!$model->hasField($f) && strpos($f, '.'))
					{
						$methods = explode('.', $f);
						$object  = $item;
						// Let's see if the relation exists
						foreach ($methods as $method)
						{
							if (isset($object->$method) || property_exists($object, $method))
							{
								$exist  = true;
								$object = $object->$method;
							}
							else
							{
								$exist = false;
								break;
							}
						}
					}

					if (in_array($f, $keys))
					{
						$temp[] = $f;
					}
					elseif ($exist)
					{
						$temp[] = $f;
					}
				}

				$keys = $temp;
			}

			if ($this->csvHeader)
			{
				$csv = [];

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
				$csv = [];

				foreach ($keys as $k)
				{
					// If our key contains a dot and it isn't part of the field name, we are dealing with relations
					if (!$model->hasField($k) && strpos($k, '.'))
					{
						$methods = explode('.', $k);
						$v       = $item;

						foreach ($methods as $method)
						{
							$v = $v->$method;
						}
					}
					else
					{
						$v = $item->$k;
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

		$eventName = 'onAfter' . ucfirst($this->doTask);
		$this->triggerEvent($eventName, [$tpl]);

		return true;
	}
}
