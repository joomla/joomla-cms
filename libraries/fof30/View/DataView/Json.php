<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View\DataView;

defined('_JEXEC') || die;

use Exception;
use FOF30\Model\DataModel;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\JsonDocument as JDocumentJSON;

class Json extends Raw implements DataViewInterface
{
	/**
	 * Set to true if your onBefore* methods have already populated the item, items, limitstart etc properties used to
	 * render a JSON document.
	 *
	 * @var bool
	 */
	public $alreadyLoaded = false;

	/**
	 * Record listing offset (how many records to skip before starting showing some)
	 *
	 * @var   int
	 */
	protected $limitStart = 0;

	/**
	 * Record listing limit (how many records to show)
	 *
	 * @var   int
	 */
	protected $limit = 10;

	/**
	 * Total number of records in the result set
	 *
	 * @var   int
	 */
	protected $total = 0;

	/**
	 * The record being displayed
	 *
	 * @var   DataModel
	 */
	protected $item = null;

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

		$eventName = 'onAfter' . ucfirst($this->doTask);
		$this->triggerEvent($eventName, [$tpl]);

		return true;
	}

	/**
	 * The event which runs when we are displaying the record list JSON view
	 *
	 * @param   string  $tpl  The sub-template to use
	 */
	public function onBeforeBrowse($tpl = null)
	{
		// Load the model
		/** @var DataModel $model */
		$model = $this->getModel();

		$result = '';

		if (!$this->alreadyLoaded)
		{
			$this->limitStart = $model->getState('limitstart', 0);
			$this->limit      = $model->getState('limit', 0);
			$this->items      = $model->get(true, $this->limitStart, $this->limit);
			$this->total      = $model->count();
		}

		$document = $this->container->platform->getDocument();

		/** @var JDocumentJSON $document */
		if ($document instanceof Document)
		{
			$document->setMimeEncoding('application/json');
		}

		if (is_null($tpl))
		{
			$tpl = 'json';
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

		if ($hasFailed)
		{
			// Default JSON behaviour in case the template isn't there!
			$result = [];

			foreach ($this->items as $item)
			{
				if (is_object($item) && method_exists($item, 'toArray'))
				{
					$result[] = $item->toArray();
				}
				else
				{
					$result[] = $item;
				}
			}

			$json = json_encode($result, JSON_PRETTY_PRINT);

			// JSONP support
			$callback = $this->input->get('callback', null, 'raw');

			if (!empty($callback))
			{
				echo $callback . '(' . $json . ')';
			}
			else
			{
				$defaultName = $this->input->get('view', 'main', 'cmd');
				$filename    = $this->input->get('basename', $defaultName, 'cmd');

				$document->setName($filename);
				echo $json;
			}
		}
		else
		{
			echo $result;
		}
	}

	/**
	 * The event which runs when we are displaying a single item JSON view
	 *
	 * @param   string  $tpl  The view sub-template to use
	 */
	protected function onBeforeRead($tpl = null)
	{
		self::renderSingleItem($tpl);
	}

	/**
	 * The event which runs when we are displaying a single item JSON view
	 *
	 * @param   string  $tpl  The view sub-template to use
	 */
	protected function onAfterSave($tpl = null)
	{
		self::renderSingleItem($tpl);
	}

	/**
	 * Renders a single item JSON view
	 *
	 * @param   string  $tpl  The view sub-template to use
	 */
	protected function renderSingleItem($tpl)
	{
		// Load the model
		/** @var DataModel $model */
		$model = $this->getModel();

		$result = '';

		if (!$this->alreadyLoaded)
		{
			$this->item = $model->find();
		}


		$document = $this->container->platform->getDocument();

		/** @var JDocumentJSON $document */
		if ($document instanceof Document)
		{
			$document->setMimeEncoding('application/json');
		}

		if (is_null($tpl))
		{
			$tpl = 'json';
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

		if ($hasFailed)
		{
			// Default JSON behaviour in case the template isn't there!

			if (is_object($this->item) && method_exists($this->item, 'toArray'))
			{
				$data = $this->item->toArray();
			}
			else
			{
				$data = $this->item;
			}

			$json = json_encode($data, JSON_PRETTY_PRINT);

			// JSONP support
			$callback = $this->input->get('callback', null);

			if (!empty($callback))
			{
				echo $callback . '(' . $json . ')';
			}
			else
			{
				$defaultName = $this->input->get('view', 'main', 'cmd');
				$filename    = $this->input->get('basename', $defaultName, 'cmd');
				$document->setName($filename);

				echo $json;
			}
		}
		else
		{
			echo $result;
		}
	}
}
