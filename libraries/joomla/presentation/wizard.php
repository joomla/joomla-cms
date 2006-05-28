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

	function __construct(&$app, $name)
	{
		$this->_step = JRequest::getVar('step', 0, '', 'int');
		$type = $app->getUserStateFromRequest('wizard.'.$name.'.type', 'type');

//		if ($this->_step && $type) {
//			require_once(COM_MENUS.'helpers'.DS.$type.'.php');
//			$class = 'JMenuHelper'.ucfirst($type);
//			$this->_helper = new $class($app);
//		}
		
		// Build registry path
		$regPath = 'wizard.'.$name;
		if ($type) {
			$regPath .= '.'.$type;
		}

		$this->_registry = $app->getUserState($regPath);

		// Create the object if it does not exist
		if (!is_a($this->_registry, 'JParameter')) {
			$this->_registry = new JParameter('');
		}
		
		$items = JRequest::getVar('wizVal', array(), '', 'array');
		$this->_registry->loadArray($items);
		$app->setUserState($regPath, $this->_registry);
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
		if ($xmlDoc =& $this->_getWizElement()) {
			if ($params = $xmlDoc->getElementByPath( 'step'.$this->_step.'/params' )) {
				$this->_registry->setXML( $params );
			}
		}
		return $this->_registry;
	}

	function &getConfirmation()
	{
		$vals = $this->_registry->toArray();
		$final = new stdClass();
		$final->values =& $vals;
		$final->message = null;
		$final->menutype = 'component';
		$final->type = null;
		$final->componentid = null;
		$final->params =& $vals;
		$final->mvcrt = 0;

		return $final;
	}

	function getStep()
	{
		return $this->_step;
	}

	function getSteps()
	{
		if (empty($this->_steps)) {
			if ($xmlDoc =& $this->_getWizElement()) {
				if ($steps = $xmlDoc->getElementByPath( 'steps' )) {
					foreach($steps->children() as $step) {
						$this->_steps[$step->attributes('id')] = $step->data();
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