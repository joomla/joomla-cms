<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/29/14 5:45 PM $
* @package CBLib\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Database\Table;

defined('CBLIB') or die();

/**
 * CBLib\Database\Table\CheckedOrderedTable Class implementation
 * 
 */
class CheckedOrderedTable extends OrderedTable
{
	/**
	 * Tells if this Table has the $feature
	 * Special Features: 'ordered', 'checkout'
	 * @since 2.0
	 *
	 * @param  string  $feature   Feature to check
	 * @param  string  $forField  [optional] Field of Table with that feature
	 * @return boolean
	 */
	public function hasFeature( $feature, $forField = null )
	{
		if ( $feature == 'checkout' ) {
			return array_key_exists( 'checked_out', get_class_vars( strtolower( get_class( $this ) ) ) );
		}
		return parent::hasFeature( $feature, $forField );
	}

	/**
	 * CHECKOUT feature: Tests if item is checked out
	 *
	 * @param  int $userId User-id
	 * @return boolean
	 *
	 * @throws \UnexpectedValueException
	 */
	public function isCheckedOut( $userId = 0 )
	{
		if ( ! $this->hasFeature( 'checkout' ) ) {
			throw new \UnexpectedValueException( 'Error: ' . __FUNCTION__ . ' called but ' . get_class( $this ) . ' does not support checkouts.' );
		}

		if ( $userId ) {
			/** @noinspection PhpUndefinedFieldInspection */
			return ( $this->checked_out && ( $this->checked_out != $userId ) );
		} else {
			/** @noinspection PhpUndefinedFieldInspection */
			return $this->checked_out;
		}
	}

	/**
	 * CHECKOUT feature: Checkout object from database
	 *
	 * @param  int      $who
	 * @param  int      $oid
	 * @return boolean
	 *
	 * @throws \UnexpectedValueException
	 */
	public function checkout( $who, $oid = null )
	{
		if ( ! $this->hasFeature( 'checkout' ) ) {
			throw new \UnexpectedValueException( 'Error: ' . __FUNCTION__ . ' called but ' . get_class( $this ) . ' does not support checkouts.' );
		}

		$k				=	$this->_tbl_key;
		if ( $oid !== null ) {
			$this->$k	=	$oid;
		}

		$time			=	$this->_db->getUtcDateTime();

		$query			=	"UPDATE " . $this->_db->NameQuote( $this->_tbl )
			.	"\n SET checked_out = " . ( (int) $who ) . ', checked_out_time = ' . $this->_db->Quote( $time )
			.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . " = " . $this->_db->Quote( $this->$k )
		;
		$this->_db->setQuery( $query );
		return $this->_db->query();
	}

	/**
	 * CHECKOUT feature: Check-in object to database
	 *
	 * @param  int      $oid
	 * @return boolean
	 *
	 * @throws \UnexpectedValueException
	 */
	public function checkin( $oid = null )
	{
		if ( ! $this->hasFeature( 'checkout' ) ) {
			throw new \UnexpectedValueException( 'Error: ' . __FUNCTION__ . ' called but ' . get_class( $this ) . ' does not support checkouts.' );
		}
		$k				=	$this->_tbl_key;
		if ( $oid !== null ) {
			$this->$k	=	$oid;
		}
		$query			=	"UPDATE " . $this->_db->NameQuote( $this->_tbl )
			.	"\n SET checked_out = 0, checked_out_time = " . $this->_db->Quote( $this->_db->getNullDate() )
			.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . " = " . $this->_db->Quote( $this->$k )
		;
		$this->_db->setQuery( $query );
		return $this->_db->query();
	}
}
