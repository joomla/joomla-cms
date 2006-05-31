<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * Library to support a wizard workflow
 * 
 * @package Joomla.Framework
 * @subpackage Presentation
 * @author Louis Landry <louis.landry@joomla.org>
 */
class JWizard extends JObject
{

	var $_step = null;

	var $_steps = array();

	var $_xml = null;

	var $_xpath = null;

	/** @var object JRegistry object */
	var $_registry = null;

	function __construct(&$app, $name, $request='wizVal')
	{
		$this->_step = JRequest::getVar('step', 0, '', 'int');

		// Build registry path
		$regPath = 'wizard.'.$name;

		// Create the object if it does not exist
		$this->_registry =& $app->getUserState($regPath);
		if (!is_a($this->_registry, 'JParameter')) {
			$this->_registry =& new JParameter('');
		}

		// Get the values from the request and load them into the object
		$items = JRequest::getVar($request, array(), '', 'array');
		$this->_registry->loadArray($items);

		/*
		 * Create the JParameter object to sit in the registry.  We have to create a new one
		 * because if we don't, then the element objects will throw a php error because the 
		 * class definitions will not be loaded when the object is unserialized...  Thus no
		 * xml definition is allowed.
		 */
		$registry =& new JParameter('');
		$registry->loadArray($this->_registry->toArray());
		$app->setUserState($regPath, $registry);
	}

	function loadXML($path, $xpath='')
	{
		// load the xml metadata
		if (file_exists( $path )) {
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile($path)) {
				$this->_xml = &$xml;
				$this->_xpath = $xpath;
			}
		}
	}

	function &getForm()
	{
		$steps = $this->getSteps();
		$step = $steps[$this->_step - 1];
		if (isset($step) && $params = $step->getElementByPath('params')) {
			$this->_registry->setXML( $params );
		}
		return $this->_registry;
	}

	function getMessage()
	{
		$msg	= null;
		$steps	= $this->getSteps();
		$step	= $steps[$this->_step - 1];
		if (isset($step)) {
			$e		= $step->getElementByPath('message');
			$msg	= $e->data();
		}
		return $msg;
	}

	function &getConfirmation()
	{
		$vals =& $this->_registry->toArray();
		return $vals;
	}

	function getStep()
	{
		return $this->_step;
	}

	function getStepName()
	{
		$name	= null;
		$steps	= $this->getSteps();
		$step	= $steps[$this->_step - 1];
		if (isset($step)) {
			$name	= $step->attributes('name');
		}
		return $name;
	}

	function getSteps()
	{
		if (empty($this->_steps)) {
			if ($xmlDoc =& $this->_getWizElement()) {
				if ($steps = $xmlDoc->getElementByPath( 'steps' )) {
					foreach($steps->children() as $step) {
						
						/*
						 * For each child we need to see if it is an include and if so we
						 * need to get those children and process them as well (break out into
						 * another method).  Then we need to create the objects in the _steps 
						 * array for each child of type step.  For now we aren't going to handle
						 * nested includes.
						 */
						if ($step->name() == 'include') {
							// Handle include
							$this->_getIncludedSteps($step);
						} elseif ($step->name() == 'step') {
							// Include step to array
							$this->_steps[] = $step;
						} else {
							// Do nothing
							continue;
						}
					}
				}
			}
		}
		return $this->_steps;
	}

	function isStarted()
	{
		return ($this->_step);
	}

	function isFinished()
	{
		$steps = $this->getSteps();
		return (count($steps) <= $this->_step - 1);
	}

	function _getIncludedSteps($include)
	{
		$tags	= array();
		$source	= $include->attributes('source');
		$path	= $include->attributes('path');

		preg_match_all( "/{([A-Za-z\-_]+)}/", $source, $tags);
		if (isset($tags[1])) {
			for ($i=0;$i<count($tags[1]);$i++) {
				$source = str_replace($tags[0][$i], $this->_registry->get($tags[1][$i]), $source);
			}
		}

		// load the source xml file
		if (file_exists( JPATH_ROOT.$source )) {
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile(JPATH_ROOT.$source)) {
				$document = &$xml->document;

				$steps = $document->getElementByPath($path);

				foreach($steps->children() as $step) {
					if ($step->name() == 'include') {
						// Handle include
					} elseif ($step->name() == 'step') {
						// Include step to array
						$this->_steps[] = $step;
					} else {
						// Do nothing
						continue;
					}
				}
			}
		}
	}

	/**
	 * @access private
	 */
	function &_getWizElement()
	{
		$result = null;
		if (isset( $this->_xml->document )) {
			$result = &$this->_xml->document->getElementByPath($this->_xpath);
		}
		return $result;
	}
}
?>