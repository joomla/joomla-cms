<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
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

	var $_regPath = null;

	/** @var object JRegistry object */
	var $_registry = null;

	function __construct(&$app, $name, $request='wizVal')
	{
		$this->_app 	= &$app;
		$this->_step 	= JRequest::getVar('step', 0, '', 'int');
		$this->_registry =& new JParameter('');
		$this->_regPath  = 'wizard.'.$name;

		// Get the step data from the session
		$steps =& $app->getUserState($this->_regPath);

		// Get the values from the request and load them into the object
		$steps[$this->_step] = JRequest::getVar($request, array(), '', 'array');
		
		// Load all the step data into the registry
		for ($i=0;$i<=$this->_step;$i++) {
			if (isset($steps[$i]) && is_array($steps[$i])) {
				$this->_registry->loadArray($steps[$i]);
			}
		}

		// Save the step data in the session
		$app->setUserState($this->_regPath, $steps);
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
		// Get the step data from the session
		$steps =& $this->_app->getUserState($this->_regPath);
		$vals = array();

		// Load all the step data into the registry
		for ($i=1;$i<=$this->_step;$i++) {
			if (is_array($steps[$i])) {
				$vals = array_merge($vals, $steps[$i]);
			}
		}
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

	function loadDefault($ini='')
	{
		$this->_registry->loadINI($ini);
	}

	function clear()
	{
		$this->_app->setUserState($this->_regPath, array());
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

jimport( 'joomla.application.model' );

/**
 * Library to support a wizard workflow
 * 
 * @package Joomla.Framework
 * @subpackage Presentation
 * @author Louis Landry <louis.landry@joomla.org>
 */
class JWizardModel extends JModel
{

	var $_wizard = null;
	var $_helper = null;

	/** @var object JRegistry object */
	var $_item = null;

	function &getForm()
	{
		return $this->_wizard->getForm();
	}

	function getMessage()
	{
		return $this->_wizard->getMessage();
	}

	function &getConfirmation()
	{
		return $this->_wizard->getConfirmation();
	}

	function getStep()
	{
		return $this->_wizard->getStep();
	}

	function getStepName()
	{
		return $this->_wizard->getStepName();
	}

	function getSteps()
	{
		return $this->_wizard->getSteps();
	}
}

jimport( 'joomla.application.view' );

/**
 * Wizard View
 * @abstract
 */
class JWizardView extends JView
{
	function display()
	{
		mosCommonHTML::loadOverlib();
		if (!$this->isStarted()) {
			$this->doStart();
		} else {
			if ($this->isFinished()) {
				$this->doFinished();
			} else {
				$this->doNext();
			}
		}
	}

	function isStarted()
	{
		return ($this->get('step'));
	}

	function isFinished()
	{
		$steps = $this->get('steps');
		return (count($steps) <= $this->get('step') - 1);
	}
}

/**
 * Wizard Helper
 * @abstract
 */
class JWizardHelper extends JObject
{
	var $_helperContext = null;

	var $_helperName = null;
	
	var $_xmlPath = null;

	var $_parent = null;

	/**
	 * Constructor
	 */
	function __construct(&$parent)
	{
		$this->_parent =& $parent;
	}

	/**
	 * Returns the wizard name
	 * @return string
	 */
	function getWizardName()
	{
		return $this->_helperContext . '.' . $this->_helperName;
	}

	/**
	 * Initializes the helper class with the wizard object and loads the wizard xml.
	 * 
	 * @param object JWizard
	 */
	function init(&$wizard)
	{
		$this->_wizard =& $wizard;
		$this->loadXML();
	}

	function loadXML()
	{
		$path = $this->_xmlPath.DS.$this->_helperName.'.xml';
		$this->_wizard->loadXML($path, 'control');
	}


	/**
	 * Sets the wizard object for the helper class
	 * 
	 * @param object JWizard
	 */
	function setWizard(&$wizard)
	{
		$this->_wizard =& $wizard;
	}

	function setXmlPath( $path )
	{
		$this->_xmlPath = $path;
	}
}
?>