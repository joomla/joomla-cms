<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater\Adapter;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Updater\UpdateAdapter;
use Joomla\CMS\Version;

/**
 * Collection Update Adapter Class
 *
 * @since  11.1
 */
class CollectionAdapter extends UpdateAdapter
{
	/**
	 * Root of the tree
	 *
	 * @var    object
	 * @since  11.1
	 */
	protected $base;

	/**
	 * Tree of objects
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $parent = array(0);

	/**
	 * Used to control if an item has a child or not
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $pop_parent = 0;

	/**
	 * @var array A list of discovered update sites
	 */
	protected $update_sites;

	/**
	 * A list of discovered updates
	 *
	 * @var array
	 */
	protected $updates;

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return  object
	 *
	 * @since   11.1
	 */
	protected function _getStackLocation()
	{
		return implode('->', $this->stack);
	}

	/**
	 * Get the parent tag
	 *
	 * @return  string   parent
	 *
	 * @since   11.1
	 */
	protected function _getParent()
	{
		return end($this->parent);
	}

	/**
	 * Opening an XML element
	 *
	 * @param   object  $parser  Parser object
	 * @param   string  $name    Name of element that is opened
	 * @param   array   $attrs   Array of attributes for the element
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function _startElement($parser, $name, $attrs = array())
	{
		$this->stack[] = $name;
		$tag           = $this->_getStackLocation();

		// Reset the data
		if (isset($this->$tag))
		{
			$this->$tag->_data = '';
		}

		switch ($name)
		{
			case 'CATEGORY':
				if (isset($attrs['REF']))
				{
					$this->update_sites[] = array('type' => 'collection', 'location' => $attrs['REF'], 'update_site_id' => $this->updateSiteId);
				}
				else
				{
					// This item will have children, so prepare to attach them
					$this->pop_parent = 1;
				}
				break;
			case 'EXTENSION':
				$update = Table::getInstance('update');
				$update->set('update_site_id', $this->updateSiteId);

				foreach ($this->updatecols as $col)
				{
					// Reset the values if it doesn't exist
					if (!array_key_exists($col, $attrs))
					{
						$attrs[$col] = '';

						if ($col == 'CLIENT')
						{
							$attrs[$col] = 'site';
						}
					}
				}

				$client = ApplicationHelper::getClientInfo($attrs['CLIENT'], 1);

				if (isset($client->id))
				{
					$attrs['CLIENT_ID'] = $client->id;
				}

				// Lower case all of the fields
				foreach ($attrs as $key => $attr)
				{
					$values[strtolower($key)] = $attr;
				}

				// Only add the update if it is on the same platform and release as we are
				$ver = new Version;

				// Lower case and remove the exclamation mark
				$product = strtolower(InputFilter::getInstance()->clean($ver::PRODUCT, 'cmd'));

				/*
				 * Set defaults, the extension file should clarify in case but it may be only available in one version
				 * This allows an update site to specify a targetplatform
				 * targetplatformversion can be a regexp, so 1.[56] would be valid for an extension that supports 1.5 and 1.6
				 * Note: Whilst the version is a regexp here, the targetplatform is not (new extension per platform)
				 * Additionally, the version is a regexp here and it may also be in an extension file if the extension is
				 * compatible against multiple versions of the same platform (e.g. a library)
				 */
				if (!isset($values['targetplatform']))
				{
					$values['targetplatform'] = $product;
				}

				// Set this to ourself as a default
				if (!isset($values['targetplatformversion']))
				{
					$values['targetplatformversion'] = $ver::RELEASE;
				}

				// Set this to ourselves as a default
				// validate that we can install the extension
				if ($product == $values['targetplatform'] && preg_match('/^' . $values['targetplatformversion'] . '/', JVERSION))
				{
					$update->bind($values);
					$this->updates[] = $update;
				}
				break;
		}
	}

	/**
	 * Closing an XML element
	 * Note: This is a protected function though has to be exposed externally as a callback
	 *
	 * @param   object  $parser  Parser object
	 * @param   string  $name    Name of the element closing
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function _endElement($parser, $name)
	{
		array_pop($this->stack);

		switch ($name)
		{
			case 'CATEGORY':
				if ($this->pop_parent)
				{
					$this->pop_parent = 0;
					array_pop($this->parent);
				}
				break;
		}
	}

	// Note: we don't care about char data in collection because there should be none

	/**
	 * Finds an update
	 *
	 * @param   array  $options  Options to use: update_site_id: the unique ID of the update site to look at
	 *
	 * @return  array  Update_sites and updates discovered
	 *
	 * @since   11.1
	 */
	public function findUpdate($options)
	{
		$response = $this->getUpdateSiteResponse($options);

		if ($response === false)
		{
			return false;
		}

		$this->xmlParser = xml_parser_create('');
		xml_set_object($this->xmlParser, $this);
		xml_set_element_handler($this->xmlParser, '_startElement', '_endElement');

		if (!xml_parse($this->xmlParser, $response->body))
		{
			// If the URL is missing the .xml extension, try appending it and retry loading the update
			if (!$this->appendExtension && (substr($this->_url, -4) != '.xml'))
			{
				$options['append_extension'] = true;

				return $this->findUpdate($options);
			}

			Log::add('Error parsing url: ' . $this->_url, Log::WARNING, 'updater');

			$app = Factory::getApplication();
			$app->enqueueMessage(\JText::sprintf('JLIB_UPDATER_ERROR_COLLECTION_PARSE_URL', $this->_url), 'warning');

			return false;
		}

		// TODO: Decrement the bad counter if non-zero
		return array('update_sites' => $this->update_sites, 'updates' => $this->updates);
	}
}
