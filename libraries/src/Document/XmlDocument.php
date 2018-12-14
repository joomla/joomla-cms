<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory as CmsFactory;

/**
 * XmlDocument class, provides an easy interface to parse and display XML output
 *
 * @since  1.7.0
 */
class XmlDocument extends Document
{
	/**
	 * Document name
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	protected $name = 'joomla';

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since   1.7.0
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set mime type
		$this->_mime = 'application/xml';

		// Set document type
		$this->_type = 'xml';
	}

	/**
	 * Render the document.
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return  string  The rendered data
	 *
	 * @since  1.7.0
	 */
	public function render($cache = false, $params = array())
	{
		parent::render();

		CmsFactory::getApplication()->setHeader('Content-disposition', 'inline; filename="' . $this->getName() . '.xml"', true);

		return $this->getBuffer();
	}

	/**
	 * Returns the document name
	 *
	 * @return  string
	 *
	 * @since  1.7.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets the document name
	 *
	 * @param   string  $name  Document name
	 *
	 * @return  XmlDocument instance of $this to allow chaining
	 *
	 * @since   1.7.0
	 */
	public function setName($name = 'joomla')
	{
		$this->name = $name;

		return $this;
	}
}
