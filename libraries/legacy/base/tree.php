<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Base
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Tree Class.
 *
 * @since       1.5
 * @deprecated  3.0
 */
class JTree extends JObject
{
	/**
	 * Root node
	 *
	 * @var    JNode
	 * @since  1.5
	 * @deprecated  3.0
	 */
	protected $_root = null;

	/**
	 * Current working node
	 *
	 * @var    JNode
	 * @since  1.5
	 * @deprecated  3.0
	 */
	protected $_current = null;

	/**
	 * Constructor
	 *
	 * @since   1.5
	 * @deprecated  3.0
	 */
	public function __construct()
	{
		JLog::add('JTree::__construct() is deprecated.', JLog::WARNING, 'deprecated');

		$this->_root = new JNode('ROOT');
		$this->_current = & $this->_root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   array    &$node       The node to process
	 * @param   boolean  $setCurrent  True to set as current working node
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.0
	 */
	public function addChild(&$node, $setCurrent = false)
	{
		JLog::add('JTree::addChild() is deprecated.', JLog::WARNING, 'deprecated');

		$this->_current->addChild($node);

		if ($setCurrent)
		{
			$this->_current = &$node;
		}
	}

	/**
	 * Method to get the parent
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.0
	 */
	public function getParent()
	{
		JLog::add('JTree::getParent() is deprecated.', JLog::WARNING, 'deprecated');

		$this->_current = &$this->_current->getParent();
	}

	/**
	 * Method to get the parent
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.0
	 */
	public function reset()
	{
		JLog::add('JTree::reset() is deprecated.', JLog::WARNING, 'deprecated');

		$this->_current = &$this->_root;
	}
}
