<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Object to work generically with referenced data
 *
 * @package		Joomla.Framework
 * @subpackage	Acl
 * @version		1.6
 */
class JAclReferences
{
	/**
	 * The references ACLs
	 * @var array
	 * @protected
	 */
	protected $_acls = array();
	/**
	 * The references ACOs
	 * @var array
	 * @protected
	 */
	protected $_acos = array();
	/**
	 * The references AROs
	 * @var array
	 * @protected
	 */
	protected $_aros = array();
	/**
	 * The references AXOs
	 * @var array
	 * @protected
	 */
	protected $_axos = array();
	/**
	 * The references ARO Groups
	 * @var array
	 * @protected
	 */
	protected $_aro_groups = array('__default' => array());
	/**
	 * The references AXO Groups
	 * @var array
	 * @protected
	 */
	protected $_axo_groups = array('__default' => array());

	/**
	 * Generic add method
	 *
	 * @param	string $field		The field to operate on
	 * @param	string $section		The section
	 * @param	mixed $value		An array or a string
	 */
	protected function _add($field, $section, $value)
	{
		if (!isset($this->$field[$section])) {
			$this->$field[$section] = array();
		}
		if (is_array($value)) {
			$this->$field[$section] = array_merge($this->$field[$section], $value);
		}
		else {
			$this->$field[$section][] = $value;
		}
	}

	/**
	 * Add the an ACL
	 *
	 * @param	string $section		The section
	 * @param	mixed $value		An array or a string
	 */
	public function addAcl($section, $value)
	{
		$this->_add('_acls', $section, $value);
	}

	/**
	 * Add the an ACO
	 *
	 * @param	string $section		The section
	 * @param	mixed $value		An array or a string
	 */
	public function addAco($section, $value)
	{
		$this->_add('_acos', $section, $value);
	}

	/**
	 * Add the an ARO
	 *
	 * @param	string $section		The section
	 * @param	mixed $value		An array or a string
	 */
	public function addAro($section, $value)
	{
		$this->_add('_aros', $section, $value);
	}

	/**
	 * Add the an AXO
	 *
	 * @param	string $section		The section
	 * @param	mixed $value		An array or a string
	 */
	public function addAxo($section, $value)
	{
		$this->_add('_axos', $section, $value);
	}

	/**
	 * Add the an ARO Group
	 *
	 * @param	string $section		The section
	 * @param	mixed $value		An array or a string
	 */
	public function addAroGroup($value)
	{
		$this->_add('_aros', '__default', $value);
	}

	/**
	 * Add the an AXO Group
	 *
	 * @param	string $section		The section
	 * @param	mixed $value		An array or a string
	 */
	public function addAxoGroup($value)
	{
		$this->_add('_axos', '__default', $value);
	}

	/**
	 * Bind an input array to this class
	 *
	 * array(
	 * 	'acls' => array( 'section' => array( 'value' ) ),
	 * 	'acos' => array( 'section' => array( 'value' ) ),
	 * 	'aros' => array( 'section' => array( 'value' ) ),
	 * 	'axos' => array( 'section' => array( 'value' ) ),
	 * 	'aro_groups' => array( 'value' ),
	 * 	'axo_groups' => array( 'value' ),
	 * )
	 *
	 * This is mainly used by the Save ACL methods
	 *
	 * @param	array $input
	 */
	public function bind($input)
	{
		if (isset($input['acls']) && is_array($input['acls']))
		{
			foreach ($input['acls'] as $section => $value) {
				$this->addAco($section, $value);
			}
		}

		if (isset($input['acos']) && is_array($input['acos']))
		{
			foreach ($input['acos'] as $section => $value) {
				$this->addAco($section, $value);
			}
		}

		if (isset($input['aros']) && is_array($input['aros']))
		{
			foreach ($input['aros'] as $section => $value) {
				$this->addAro($section, $value);
			}
		}

		if (isset($input['axos']) && is_array($input['axos']))
		{
			foreach ($input['axos'] as $section => $value) {
				$this->addAxo($section, $value);
			}
		}

		if (isset($input['aro_groups']) && is_array($input['aro_groups']))
		{
			$this->addAxoGroup($input['aro_groups']);
		}

		if (isset($input['axo_groups']) && is_array($input['axo_groups']))
		{
			$this->addAxoGroup($input['axo_groups']);
		}
	}

	/**
	 * Get the referenced ACLs
	 *
	 * @param	string $field		The field to operate on
	 * @param	mixed $section		True to return array with sections, string for a particular section, otherwise a concatenated array if returned
	 *
	 * @return	array
	 */
	protected function _get($field, $section = null)
	{
		$result = array();

		if (isset($this->$field[$section]))
		{
			if (!empty($section)) {
				return $this->$field[$section];
			}
			else if ($section === true) {
				return $this->$field;
			}
			else {
				foreach ($this->$field[$section] as $value) {
					$result = array_merge($result, $value);
				}
			}
		}
		return $result;
	}

	/**
	 * Get the referenced ACLs
	 *
	 * @param	mixed $section		True to return array with sections, string for a particular section, otherwise a concatenated array if returned
	 *
	 * @return	array
	 */
	public function getAcls($section = null)
	{
		return $this->_get('_acls', $section);
	}

	/**
	 * Get the referenced ACOs
	 *
	 * @param	mixed $section		True to return array with sections, string for a particular section, otherwise a concatenated array if returned
	 *
	 * @return	array
	 * @access	public
	 */
	function getAcos($section = null)
	{
		return $this->_get('_acos', $section);
	}

	/**
	 * Get the referenced AROs
	 *
	 * @param	mixed $section		True to return array with sections, string for a particular section, otherwise a concatenated array if returned
	 *
	 * @return	array
	 */
	public function getAros($section = null)
	{
		return $this->_get('_aros', $section);
	}

	/**
	 * Get the referenced AXOs
	 *
	 * @param	mixed $section		True to return array with sections, string for a particular section, otherwise a concatenated array if returned
	 *
	 * @return	array
	 */
	public function getAxos($section = null)
	{
		return $this->_get('_axos', $section);
	}

	/**
	 * Get the referenced ARO Groups
	 *
	 * @return	array
	 */
	public function getAroGroups($section = null)
	{
		return $this->_get('_aro_groups', '__default');
	}

	/**
	 * Get the referenced AXO Groups
	 *
	 * @return	array
	 */
	public function getAxoGroups()
	{
		return $this->_get('_axo_groups', '__default');
	}

	/**
	 * Gets a signature so that changes can be detected
	 *
	 * @return	string
	 */
	public function getSignature()
	{
		return md5(
			array(
				$this->_acls,
				$this->_acos,
				$this->_aros,
				$this->_axos,
				$this->_aro_groups,
				$this->_axo_groups
			)
		);
	}

	/**
	 * A utility method to quickly check if there are no references
	 *
	 * @return	booelan
	 */
	public function isEmpty()
	{
		return empty($this->_acls)
			& empty($this->_acos)
			& empty($this->_aros)
			& empty($this->_axos)
			& empty($this->_aro_groups['__default'])
			& empty($this->_axo_groups['__default']);
	}
}
