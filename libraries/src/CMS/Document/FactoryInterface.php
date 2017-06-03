<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('_JEXEC') or die;

/**
 * Interface defining a factory which can create JDocument objects
 *
 * @since  __DEPLOY_VERSION__
 */
interface FactoryInterface
{
	/**
	 * Creates a new JDocument object for the requested format.
	 *
	 * @param   string  $type        The document type to instantiate
	 * @param   array   $attributes  Array of attributes
	 *
	 * @return  \JDocument
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createDocument($type = 'html', array $attributes = array());

	/**
	 * Creates a new renderer object.
	 *
	 * @param   \JDocument  $document  The JDocument instance to attach to the renderer
	 * @param   string      $type      The renderer type to instantiate
	 *
	 * @return  RendererInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createRenderer(\JDocument $document, $type);
}
