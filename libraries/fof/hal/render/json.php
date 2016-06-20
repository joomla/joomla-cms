<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  hal
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('FOF_INCLUDED') or die;

/**
 * Implements the HAL over JSON renderer
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFHalRenderJson implements FOFHalRenderInterface
{
	/**
	 * When data is an array we'll output the list of data under this key
	 *
	 * @var   string
	 */
	private $_dataKey = '_list';

	/**
	 * The document to render
	 *
	 * @var   FOFHalDocument
	 */
	protected $_document;

	/**
	 * Public constructor
	 *
	 * @param   FOFHalDocument  &$document  The document to render
	 */
	public function __construct(&$document)
	{
		$this->_document = $document;
	}

	/**
	 * Render a HAL document in JSON format
	 *
	 * @param   array  $options  Rendering options. You can currently only set json_options (json_encode options)
	 *
	 * @return  string  The JSON representation of the HAL document
	 */
	public function render($options = array())
	{
		if (isset($options['data_key']))
		{
			$this->_dataKey = $options['data_key'];
		}

		if (isset($options['json_options']))
		{
			$jsonOptions = $options['json_options'];
		}
		else
		{
			$jsonOptions = 0;
		}

		$serialiseThis = new stdClass;

		// Add links
		$collection = $this->_document->getLinks();
		$serialiseThis->_links = new stdClass;

		foreach ($collection as $rel => $links)
		{
			if (!is_array($links))
			{
				$serialiseThis->_links->$rel = $this->_getLink($links);
			}
			else
			{
				$serialiseThis->_links->$rel = array();

				foreach ($links as $link)
				{
					array_push($serialiseThis->_links->$rel, $this->_getLink($link));
				}
			}
		}

		// Add embedded documents

		$collection = $this->_document->getEmbedded();

		if (!empty($collection))
		{
			$serialiseThis->_embedded->$rel = new stdClass;

			foreach ($collection as $rel => $embeddeddocs)
			{
				if (!is_array($embeddeddocs))
				{
					$embeddeddocs = array($embeddeddocs);
				}

				foreach ($embeddeddocs as $embedded)
				{
					$renderer = new FOFHalRenderJson($embedded);
					array_push($serialiseThis->_embedded->$rel, $renderer->render($options));
				}
			}
		}

		// Add data
		$data = $this->_document->getData();

		if (is_object($data))
		{
			if ($data instanceof FOFTable)
			{
				$data = $data->getData();
			}
			else
			{
				$data = (array) $data;
			}

			if (!empty($data))
			{
				foreach ($data as $k => $v)
				{
					$serialiseThis->$k = $v;
				}
			}
		}
		elseif (is_array($data))
		{
			$serialiseThis->{$this->_dataKey} = $data;
		}

		return json_encode($serialiseThis, $jsonOptions);
	}

	/**
	 * Converts a FOFHalLink object into a stdClass object which will be used
	 * for JSON serialisation
	 *
	 * @param   FOFHalLink  $link  The link you want converted
	 *
	 * @return  stdClass  The converted link object
	 */
	protected function _getLink(FOFHalLink $link)
	{
		$ret = array(
			'href'	=> $link->href
		);

		if ($link->templated)
		{
			$ret['templated'] = 'true';
		}

		if (!empty($link->name))
		{
			$ret['name'] = $link->name;
		}

		if (!empty($link->hreflang))
		{
			$ret['hreflang'] = $link->hreflang;
		}

		if (!empty($link->title))
		{
			$ret['title'] = $link->title;
		}

		return (object) $ret;
	}
}
