<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Prototype JView class.
 *
 * Hypermedia use forked from the FOF Framework by: Nicholas K. Dionysopoulos
 *
 * @package     Joomla.Libraries
 * @subpackage  View
 * @since       3.4
 */
class JViewJsonCms extends JViewCms
{
	/**
	 * When set to true we'll add hypermedia to the output, implementing the
	 * HAL specification (http://stateless.co/hal_specification.html)
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	public $useHypermedia = false;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   JModelCmsInterface  $model     The model object.
	 * @param   JDocument           $document  The document object.
	 * @param   array               $config    An array of config options. Should contain component
	 *                                         name and view name.
	 *
	 * @since   3.4
	 */
	public function __construct(JModelCmsInterface $model, JDocument $document, array $config)
	{
		parent::__construct($model, $document, $config);

		if (isset($config['use_hypermedia']))
		{
			$this->useHypermedia = (bool) $config['use_hypermedia'];
		}

		// Set the layout to be json by default
		$this->setLayout('json');
	}

	/**
	 * Retrieves the data array from the default model.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function getData()
	{
		$model = $this->getModel();

		if ($model instanceof JModelItemInterface)
		{
			return array(
				'item' => $model->getItem()
			);
		}
		elseif ($model instanceof JModelListInterface)
		{
			return array(
				'items' => $model->getItems(),
				'pagination' => $model->getPagination()
			);
		}

		// We don't know what type of model we have.
		// Just return an empty array.
		return array();
	}

	/**
	 * Method to get the layout path.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  mixed  The layout file name if found, false otherwise.
	 *
	 * @since   3.4
	 */
	public function getPath($layout)
	{
		// Get the layout file name.
		$file = JPath::clean($layout . '.php');

		$pathSpl = $this->loadPaths();
		$pathSpl->top();

		$paths = array();

		while ($pathSpl->valid())
		{
			$paths[] = $pathSpl->current();
			$pathSpl->next();
		}

		// Find the layout file path.
		$path = JPath::find($paths, $file);

		return $path;
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.4
	 */
	public function render()
	{
		$data = $this->getData();

		// Get the layout path.
		$path = $this->getPath($this->getLayout());

		// Check if the layout path was found.
		if (!$path)
		{
			// Default JSON behaviour in case the template isn't there
			if ($this->useHypermedia)
			{
				if (isset($data['item']))
				{
					$haldocument = $this->createDocumentWithHypermedia($data['item'], $this->getModel());
				}
				elseif (isset($data['items']))
				{
					$haldocument = $this->createDocumentWithHypermedia($data['items'], $this->getModel());
				}
				else
				{
					// No item or items to render. With no template we'll just play safe and abort
					throw new RuntimeException('There are no items to render');
				}

				$json = $haldocument->render('json');
			}
			else
			{
				// Formulate a nice response with JResponseJson
				$response = new JResponseJson($data);
				$json     = (string) $response;
			}

			// JSONP support
			$input    = JFactory::getApplication()->input;
			$callback = $input->get('callback', null);

			if (!empty($callback))
			{
				return $callback . '(' . $json . ')';
			}
			else
			{
				$defaultName = $input->getCmd('view', 'joomla');
				$filename    = $input->getCmd('basename', $defaultName);
				$document    = $this->document;

				$document->setName($filename);

				return $json;
			}
		}

		// Start an output buffer.
		ob_start();

		// Load the layout.
		include $path;

		// Get the layout contents.
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Creates a FOFHalDocument using the provided data
	 *
	 * @param   array      $data   The data to put in the document
	 * @param   JModelCms  $model  The model of this view
	 *
	 * @return  FOFHalDocument  A HAL-enabled document
	 *
	 * @since   3.4
	 */
	protected function createDocumentWithHypermedia($data, $model = null)
	{
		// Create a new HAL document
		if (is_array($data))
		{
			$count = count($data);
		}
		else
		{
			$count = null;
		}

		if ($count == 1)
		{
			reset($data);
			$document = new FOFHalDocument(end($data));
		}
		else
		{
			$document = new FOFHalDocument($data);
		}

		// Create a self link
		$uri = (string) (JUri::getInstance());
		$uri = $this->removeURIBase($uri);
		$uri = JRoute::_($uri);
		$document->addLink('self', new FOFHalLink($uri));

		// Create relative links in a record list context
		if (is_array($data) && ($model instanceof JModelListInterface))
		{
			$pagination = $model->getPagination();

			if ($pagination->get('pages.total') > 1)
			{
				// Try to guess URL parameters and create a prototype URL
				// NOTE: You are better off specialising this method
				$protoUri = $this->getPrototypeURIForPagination();

				// The "first" link
				$uri = clone $protoUri;
				$uri->setVar('limitstart', 0);
				$uri = JRoute::_((string) $uri);

				$document->addLink('first', new FOFHalLink($uri));

				// Do we need a "prev" link?
				if ($pagination->get('pages.current') > 1)
				{
					$prevPage   = $pagination->get('pages.current') - 1;
					$limitstart = ($prevPage - 1) * $pagination->limit;
					$uri        = clone $protoUri;
					$uri->setVar('limitstart', $limitstart);
					$uri = JRoute::_((string) $uri);

					$document->addLink('prev', new FOFHalLink($uri));
				}

				// Do we need a "next" link?
				if ($pagination->get('pages.current') < $pagination->get('pages.total'))
				{
					$nextPage   = $pagination->get('pages.current') + 1;
					$limitstart = ($nextPage - 1) * $pagination->limit;
					$uri        = clone $protoUri;
					$uri->setVar('limitstart', $limitstart);
					$uri = JRoute::_((string) $uri);

					$document->addLink('next', new FOFHalLink($uri));
				}

				// The "last" link?
				$lastPage   = $pagination->get('pages.total');
				$limitstart = ($lastPage - 1) * $pagination->limit;
				$uri        = clone $protoUri;
				$uri->setVar('limitstart', $limitstart);
				$uri = JRoute::_((string) $uri);

				$document->addLink('last', new FOFHalLink($uri));
			}
		}

		return $document;
	}

	/**
	 * Convert an absolute URI to a relative one
	 *
	 * @param   string  $uri  The URI to convert
	 *
	 * @return  string  The relative URL
	 *
	 * @since   3.4
	 */
	protected function removeURIBase($uri)
	{
		static $root = null, $rootlen = 0;

		if (is_null($root))
		{
			$root    = rtrim(JUri::base(), '/');
			$rootlen = strlen($root);
		}

		if (substr($uri, 0, $rootlen) == $root)
		{
			$uri = substr($uri, $rootlen);
		}

		return ltrim($uri, '/');
	}

	/**
	 * Returns a JUri instance with a prototype URI used as the base for the
	 * other URIs created by the JSON renderer
	 *
	 * @return  JUri  The prototype JUri instance
	 *
	 * @since   3.4
	 */
	protected function getPrototypeURIForPagination()
	{
		$protoUri = new JUri('index.php');
		$input    = new FOFInput;
		$protoUri->setQuery($input->getData());
		$protoUri->delVar('savestate');
		$protoUri->delVar('base_path');

		return $protoUri;
	}
}
