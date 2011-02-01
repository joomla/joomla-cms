<?php
/**
 * Inspector classes for the JRegistry package.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage	Registry
 */
class JRegistryInspector extends JRegistry
{
	public function bindData(& $parent, $data)
	{
		return parent::bindData($parent, $data);
	}
}