<?php
/**
 * @version		$Id: view.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Joomla! Cache view type object
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheView extends JCache
{
	/**
	 * Get the cached view data
	 *
	 * @access	public
	 * @param	object	$view	The view object to cache output for
	 * @param	string	$method	The method name of the view method to cache output for
	 * @param	string	$group	The cache data group
	 * @param	string	$id		The cache data id
	 * @return	boolean	True if the cache is hit (false else)
	 * @since	1.5
	 */
	function get(&$view, $method, $id=false)
	{
		global $mainframe;

		// Initialize variables
		$data = false;

		// If an id is not given generate it from the request
		if ($id == false) {
			$id = $this->_makeId($view, $method);
		}

		$data = parent::get($id);
		if ($data !== false) {
			$data		= unserialize($data);
			$document	= &JFactory::getDocument();

			// Get the document head out of the cache.
			$document->setHeadData((isset($data['head'])) ? $data['head'] : array());

			// If the pathway buffer is set in the cache data, get it.
			if (isset($data['pathway']) && is_array($data['pathway']))
			{
				// Push the pathway data into the pathway object.
				$pathway = &$mainframe->getPathWay();
				$pathway->setPathway($data['pathway']);
			}

			// If a module buffer is set in the cache data, get it.
			if (isset($data['module']) && is_array($data['module']))
			{
				// Iterate through the module positions and push them into the document buffer.
				foreach ($data['module'] as $name => $contents) {
					$document->setBuffer($contents, 'module', $name);
				}
			}

			// Get the document body out of the cache.
			echo (isset($data['body'])) ? $data['body'] : null;
			return true;
		}

		/*
		 * No hit so we have to execute the view
		 */
		if (method_exists($view, $method))
		{
			$document = &JFactory::getDocument();

			// Get the modules buffer before component execution.
			$buffer1 = $document->getBuffer();

			// Make sure the module buffer is an array.
			if (!isset($buffer1['module']) || !is_array($buffer1['module'])) {
				$buffer1['module'] = array();
			}

			// Capture and echo output
			ob_start();
			ob_implicit_flush(false);
			$view->$method();
			$data = ob_get_contents();
			ob_end_clean();
			echo $data;

			/*
			 * For a view we have a special case.  We need to cache not only the output from the view, but the state
			 * of the document head after the view has been rendered.  This will allow us to properly cache any attached
			 * scripts or stylesheets or links or any other modifications that the view has made to the document object
			 */
			$cached = array();

			// View body data
			$cached['body'] = $data;

			// Document head data
			$cached['head'] = $document->getHeadData();

			// Pathway data
			$pathway			= &$mainframe->getPathWay();
			$cached['pathway']	= $pathway->getPathway();

			// Get the module buffer after component execution.
			$buffer2 = $document->getBuffer();

			// Make sure the module buffer is an array.
			if (!isset($buffer2['module']) || !is_array($buffer2['module'])) {
				$buffer2['module'] = array();
			}

			// Compare the second module buffer against the first buffer.
			$cached['module'] = array_diff_assoc($buffer2['module'], $buffer1['module']);

			// Store the cache data
			$this->store(serialize($cached), $id);
		}
		return false;
	}

	/**
	 * Generate a view cache id
	 *
	 * @access	private
	 * @param	object	$view	The view object to cache output for
	 * @param	string	$method	The method name to cache for the view object
	 * @return	string	MD5 Hash : view cache id
	 * @since	1.5
	 */
	function _makeId(&$view, $method)
	{
		return md5(serialize(array(JRequest::getURI(), get_class($view), $method)));
	}
}
