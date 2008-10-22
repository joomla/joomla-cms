<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );
jimport( 'joomla.database.query' );

/**
 * @package		Users
 * @subpackage	com_users
 */
class UserModelPrototype extends JModel
{
	// @todo Upgrade format as is done in com_acl
	protected $_total = 0;

	/**
	 * @param	boolean	True to resolve foreign keys
	 * @return	array	List of items
	 */
	function &getItems( $resolveFKs = true )
	{
		static $instances;

		$state	= $this->getState();
		$hash	= md5( intval( $resolveFKs ).serialize( $state->getProperties( 1 ) ) );

		if (!isset( $instances[$hash] ))
		{
				$query				= $this->_getListQuery( $state, $resolveFKs );
				$sql				= $query->toString();
				$this->_total		= $this->_getListCount( $sql );
				if ($this->_total < $state->get( 'limitstart' )) {
					$state->set( 'limitstart', 0 );
				}
			
				$result				= $this->_getList( $sql, $state->get( 'limitstart' ), $state->get( 'limit' ));
				$instances[$hash]	= $result;
			
		}
		else {
			// TODO: Ideal for true caching
			$result = $instances[$hash];
		}

		return $result;
	}

	/**
	 * @return	object	A pagination object
	 */
	function &getPagination()
	{
		static $instance;

		if (!$instance) {
			jimport( 'joomla.html.pagination' );
			$state = &$this->getState();
			$instance = new JPagination( $this->_total, $state->get( 'limitstart'), $state->get( 'limit' ) );
		}
		return $instance;
	}

	/**
	 * @param	boolean	True to resolve foreign data relationship
	 *
	 * @return	JStdClass
	 */
	function &getItem( $resolveFKs = true  )
	{
		static $instances;

		$state = $this->getState();
		$key = md5( intval( $resolveFKs ).serialize( $state->getProperties( 1 ) ) );

		if (!isset( $instances[$key] ))
		{
			$session = &JFactory::getSession();
			$id = (int) $session->get( 'users.'.$this->getName().'.id', $this->getState('id') );

			$state->set( 'where', 'a.id='.(int) $id );
			$query	= $this->_getListQuery( $state, $resolveFKs );
			$sql	= $query->toString();
			$temp	= $this->_getList( $sql );
			if (isset( $temp[0] )) {
				$instances[$key] = JArrayHelper::toObject( JArrayHelper::fromObject( $temp[0] ), 'JStdClass' );
			}
			else {
				$temp = $this->getTable();
				$instances[$key] = JArrayHelper::toObject( $temp->getProperties( 1 ), 'JStdClass' );
			}
		}
		return $instances[$key];
	}

	/**
	 * Method to checkin a table row
	 *
	 * @access	public
	 * @param	integer	$id	The ID of the row
	 * @return	mixed	True on success or JExeception object on failure
	 * @since	1.0
	 */
	function checkout($id = null)
	{
		$result	= true;

		$id = (int) (empty($id)) ? $this->getState('id') : $id;
		if ($id) {
			$table	= &$this->getTable();
			$user = &JFactory::getUser();
			if (!$table->checkout($user->get('id'), $id)) {
				$result = new JException( $table->getError() );
			}
		}
	}

	/**
	 * Method to checkin a table row
	 *
	 * @access	public
	 * @param	integer	$id	The ID of the row
	 * @return	mixed	True on success or JExeception object on failure
	 * @since	1.0
	 */
	function checkin($id = null)
	{
		$result	= true;

		$id = (int) (empty($id)) ? $this->getState('id') : $id;
		if ($id) {
			$table	= &$this->getTable();
			if (!$table->checkin($id)) {
				$result = new JException( $table->getError() );
			}
		}
	}

	/**
	 * Method to save a taxonomy entry.
	 *
	 * @access	public
	 * @param	array	values...
	 * @return	mixed	True on success or JExeception object on failure
	 * @since	1.0
	 */
	function save($input = array())
	{
		$result	= true;
		$user	= &JFactory::getUser();
		$table	= &$this->getTable();

		if (!$table->save( $input )) {
			$result	= JError::raiseWarning( 500, $table->getError() );
		}
		// Set the new id (if new)
		$this->setState( 'id', $table->id );

		return $result;
	}

	/**
	 * Method to delete a list of credit packs.
	 *
	 * @access	public
	 * @param	array	values...
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function delete( $ids )
	{
		$table	= &$this->getTable();
		if (is_array($ids)) {
			foreach ($ids as $id) {
				$table->delete((int) $id);
			}
			return true;
		}
		else {
			return $table->delete((int) $ids);
		}
	}

	/**
	 * Method to change the publish state of an item
	 *
	 * @access	public
	 * @param	array	$ids	The IDs of the taxonomy rows to publish.
	 * @param	int		$value	The value to set
	 * @return	mixed	True on success or JExeception object on failure
	 * @since	1.0
	 */
	function publish( $ids, $value = 1 )
	{
		$result	= true;
		$user	= &JFactory::getUser();
		$table	= &$this->getTable();

		if (!$table->publish( $ids, $value, $user->get('id'))) {
			$result = JError::raiseWarning(500, $table->getError());
		}
		return $result;
	}

	/**
	 * Set access level
	 *
	 * @param	mixed	An integer ID, or an array of ID's
	 * @param	int		Option - the access level
	 */
	function access( $ids, $level = null )
	{
		$table = $this->getTable();
		$qt	= 'SELECT g.id' .
				' FROM #__core_acl_axo_groups AS g' .
				' LEFT JOIN '.$table->getTableName().' AS a ON a.id = %d' .
				' WHERE g.level > a.access' .
				' ORDER BY g.id ASC';

		if (!is_array( $ids )) {
			$ids = array( $ids );
		}
		JArrayHelper::toInteger( $ids );
		$db	= &$this->getDBO();

		foreach ($ids as $id) {
			if ($level == null) {
				$db->setQuery( sprintf( $qt, $id ), 0, 1 );
				$newlevel = $db->loadResult();
			}
			else {
				$newlevel = $level;
			}
			$db->setQuery(
				'UPDATE '.$table->getTableName() .
				' SET access = '.(int) $newlevel .
				' WHERE id = '.$id
			);
			if (!$db->query()) {
				return new JException( $db->getErrorMsg() );
			}
		}
		return count( $ids );
	}

	/**
	 * @param array		An array of primary keys
	 * @param int		Increment, usually +1 or -1
	 * @return boolean
	 */
	function ordering( $input, $inc=0 )
	{
		$app = &JFactory::getApplication();

		$db		= &$this->getDBO();
		$user	= &JFactory::getUser();
		$table	= &$this->getTable();

		JArrayHelper::toInteger( $input );

		if (!empty( $input )) {
			$cids = 'id=' . implode( ' OR id=', $input );
			$hasCO = property_exists( $table, 'checked_out' );

			$query = 'UPDATE ' . $table->getTableName()
			. ' SET ordering = ordering + ' . (int) $inc
			. ' WHERE (' . $cids . ')'
			. ($hasCO ? ' AND (checked_out = 0 OR checked_out = ' . (int) $user->get( 'id' ) . ')' : '' )
			;
			$db->setQuery( $query );
			if (!$db->query()) {
				$this->setError( $db->getErrorMsg() );
			}
			else {
				return true;
			}
		}
	}
}
