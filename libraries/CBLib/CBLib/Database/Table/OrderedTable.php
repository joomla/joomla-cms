<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/29/14 5:36 PM $
* @package CBLib\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Database\Table;

defined('CBLIB') or die();

/**
 * CBLib\Database\Table\OrderedTable Class implementation
 * 
 */
class OrderedTable extends Table
{
	/**
	 * Ordering keys and for each their ordering groups.
	 * E.g.; array( 'ordering' => array( 'tab' ), 'ordering_registration' => array() )
	 * @var array
	 */
	protected $_orderings	=	array( 'ordering' => array( /* here ordering groups, if any, could be e.g. 'type' */ ) );

	/**
	 * If table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 *
	 * This override handles assigning orderings of new records if unset.
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 *
	 * @throws \RuntimeException
	 */
	public function store( $updateNulls = false )
	{
		$k				=	$this->_tbl_key;

		if ( $this->$k === null )
		{
			// new record without any ordering: Give it highest ordering (below 10000 which is our ordering limit):
			foreach ( $this->_orderings as $ordering => $orderingGroups )
			{
				if ( property_exists( $this, $ordering ) && ( $this->$ordering <= 0 ) )
				{
					$where		=	array( $this->_db->NameQuote( $ordering ) . ' < 9998' );

					foreach ( $orderingGroups as $ordGrpField )
					{
						if ( $this->$ordGrpField !== null )
						{
							$where[]	=	$this->_db->NameQuote( $ordGrpField ) . ' = ' . $this->_db->Quote( $this->$ordGrpField );
						}
					}

					$sql		=	'SELECT MAX(' . $this->_db->NameQuote( $ordering ) . ')'
						.	"\n FROM "    . $this->_db->NameQuote( $this->_tbl )
						.	"\n WHERE "   . implode( ' AND ', $where );
					$this->_db->SetQuery($sql);
					$max		=	$this->_db->LoadResult();
					$this->$ordering	=	$max + 1;
				}
			}
		}
		return parent::store( $updateNulls );
	}

	/**
	 * Tells if this Table has the $feature
	 * Special Features: 'ordered'
	 *
	 * @param  string  $feature   Feature to check
	 * @param  string  $forField  [optional] Field of Table with that feature
	 * @return boolean
	 */
	public function hasFeature( $feature, $forField = null )
	{
		if ( $feature == 'ordered' && $forField ) {
			return in_array( $forField, $this->getPublicProperties(), true );
		}

		return parent::hasFeature( $feature, $forField );
	}

	/**
	 * ORDERING feature: Move the entry into the direction corresponding to a given ordering
	 *
	 * @param  int     $direction  Direction to move the entry: +1 at same ordering than next object, -1 at same as previous, 0 keep at same
	 * @param  string  $where      This is expected to be a valid (and safe!) SQL expression (e.g. to reorder within a category)
	 * @param  string  $ordering   Ordering column name
	 * @return void
	 *
	 * @throws \UnexpectedValueException
	 * @throws \Exception
	 */
	public function move( $direction, $where = '', $ordering = 'ordering' )
	{
		if ( ! $this->hasFeature( 'ordered', $ordering ) ) {
			throw new \UnexpectedValueException( 'Error: ' . __FUNCTION__ . ' called but ' . get_class( $this ) . ' does not support ordering.' );
		}

		$k		=	$this->_tbl_key;

		$sql	=	'SELECT ' . $this->_db->NameQuote( $this->_tbl_key ) . ', ' . $this->_db->NameQuote( $ordering )
				.	"\n FROM " . $this->_db->NameQuote( $this->_tbl );

		if ( $direction < 0 ) {
			$sql	.=	"\n WHERE " . $this->_db->NameQuote( $ordering ) . ' < ' . (int) $this->$ordering;
			$sql	.=	($where ? "\n	AND $where" : '');
			$sql	.=	"\n ORDER BY " . $this->_db->NameQuote( $ordering ) . ' DESC';
		} else if ( $direction > 0 ) {
			$sql	.=	"\n WHERE " . $this->_db->NameQuote( $ordering ) . ' > ' . (int) $this->$ordering;
			$sql	.=	($where ? "\n	AND $where" : '');
			$sql	.=	"\n ORDER BY " . $this->_db->NameQuote( $ordering );
		} else {
			// Nothing to move:
			return;
		}

		$this->_db->setQuery( $sql, 0, 1 );

		$row		=	null;
		if ( $this->_db->loadObject( $row ) ) {
			$query	=	'UPDATE '   . $this->_db->NameQuote( $this->_tbl )
					.	"\n SET "   . $this->_db->NameQuote( $ordering )	   . ' = ' . (int) $row->$ordering
					.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . ' = ' . $this->_db->Quote( $this->$k )
			;
			$this->_db->setQuery( $query );

			if ( ! $this->_db->query() ) {
				$err = $this->_db->getErrorMsg();
				throw new \Exception( $err );
			}

			$query	=	'UPDATE '   . $this->_db->NameQuote( $this->_tbl )
					.	"\n SET "	. $this->_db->NameQuote( $ordering )	   . ' = ' . (int) $this->$ordering
					.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . ' = ' . $this->_db->Quote( $row->$k )
			;
			$this->_db->setQuery( $query );

			if ( ! $this->_db->query() ) {
				$err = $this->_db->getErrorMsg();
				throw new \Exception( $err );
			}

			$this->$ordering	=	$row->$ordering;
		} else {
			$query	=	'UPDATE '   . $this->_db->NameQuote( $this->_tbl )
					.	"\n SET "	. $this->_db->NameQuote( $ordering ) . ' = ' . (int) $this->$ordering
					.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . ' = ' . $this->_db->Quote( $this->$k )
			;
			$this->_db->setQuery( $query );

			if ( !$this->_db->query() ) {
				$err = $this->_db->getErrorMsg();
				throw new \Exception( $err );
			}
		}
	}

	/**
	 * ORDERING feature: Compacts the ordering sequence of the selected records
	 *
	 * @param  string  $where     Additional where query to limit ordering to a particular subset of records
	 * @param  array   $cIds      Ids of table key ids which should preserve their position (in addition of the negative positions)
	 * @param  string  $ordering  Name of ordering column in table
	 * @return boolean            TRUE success, FALSE failed, with error of database updated.
	 *
	 * @throws \UnexpectedValueException
	 */
	public function updateOrder( $where = '' , $cIds = null, $ordering = 'ordering' )
	{
		if ( ! $this->hasFeature( 'ordered', $ordering ) ) {
			throw new \UnexpectedValueException( 'Error: ' . __FUNCTION__ . ' called but ' . get_class( $this ) . ' does not support ordering.' );
		}

		$k			=	$this->_tbl_key;

		$this->_db->setQuery(
			'SELECT ' . $this->_db->NameQuote( $this->_tbl_key ) . ', ' . $this->_db->NameQuote( $ordering )
			.	"\n FROM " . $this->_db->NameQuote( $this->_tbl )
			.	($where ? "\n WHERE $where" : '')
			.	"\n ORDER BY " . $this->_db->NameQuote( $ordering )
		);

		if ( ! ( $orders = $this->_db->loadObjectList() ) ) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}

		// Save existing orderings:
		$oldOrders	=	array();
		array_walk(
			$orders,
			function( $obj ) use ( &$oldOrders, $k, $ordering ) {
				$oldOrders[$obj->$k]	=	(int) $obj->$ordering;
			}
		);

		$n			=	count( $orders );
		$iOfThis	=	null;

		if ( $cIds !== null ) {
			// determine list of reserved/changed ordering numbers:
			$cidsOrderings		=	array();
			for ( $i=0; $i < $n; $i++ ) {
				if ( in_array( $orders[$i]->$k, $cIds ) ) {
					$cidsOrderings[$orders[$i]->$k]		=	$orders[$i]->$ordering;
				}
			}

			// change ordering numbers outside of reserved and negative ordering numbers list:
			$j		=	1;
			for ( $i=0; $i < $n; $i++ ) {
				if ( $orders[$i]->$k == $this->$k ) {
					// place 'this' record in the desired location at the end !
					$iOfThis	=	$i;
					if ( $orders[$i]->$ordering == $j ) {
						$j++;
					}
				} elseif ( in_array($orders[$i]->$k, $cIds ) ) {
					if ( $orders[$i]->$ordering == $j ) {
						$j++;
					}
				} else {
					if ( $orders[$i]->$ordering >= 0 ) {
						$orders[$i]->$ordering	=	$j++;
					}
					while ( in_array($orders[$i]->$ordering, $cidsOrderings ) ) {
						$orders[$i]->$ordering	=	$j++;
					}
				}
			}
		} else {
			$j		=	1;
			for ( $i=0; $i < $n; $i++ ) {
				if ( $orders[$i]->$k == $this->$k ) {
					// place 'this' record in the desired location at the end !:
					$iOfThis	=	$i;
					if ( $orders[$i]->$ordering == $j ) {
						$j++;
					}
				} elseif ( $orders[$i]->$ordering != $this->$ordering && $this->$ordering > 0 && $orders[$i]->$ordering >= 0 ) {
					$orders[$i]->$ordering	=	$j++;
				} elseif ( $orders[$i]->$ordering == $this->$ordering && $this->$ordering > 0 && $orders[$i]->$ordering >= 0 ) {
					if ( $orders[$i]->$ordering == $j ) {
						$j++;
					}
					$orders[$i]->$ordering	=	$j++;
				}
			}
		}
		if ( $iOfThis !== null ) {
			$orders[$iOfThis]->$ordering	=	min( $this->$ordering, $j );
		}

		// sort entries by ->$ordering:
		usort(
			$orders,
			function ( $a, $b ) use ( $ordering )
			{
				if ( $a->$ordering == $b->$ordering ) {
					return 0;
				}
				return ( $a->$ordering > $b->$ordering ) ? +1 : -1;
			}
		);

		// compact ordering:
		$j		=	1;
		for ( $i=0; $i < $n; $i++ ) {
			if ( $orders[$i]->$ordering >= 0 ) {
				$orders[$i]->$ordering	=	$j++;
			}
		}

		for ( $i=0; $i < $n; $i++ ) {
			if ( ( ( $orders[$i]->$ordering >= 0 ) || ( $orders[$i]->$k == $this->$k ) )
				&& ( ( (int) $orders[$i]->$ordering ) !== $oldOrders[$orders[$i]->$k] ) )
			{
				$this->_db->setQuery(
					"UPDATE $this->_tbl"
					.	"\n SET "	. $this->_db->NameQuote( $ordering )	   . ' = ' . ( (int) $orders[$i]->$ordering )
					.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . ' = ' . $this->_db->Quote( $orders[$i]->$k )
				);
				$this->_db->query();
			}
		}

		// if we didn't find to reorder the current record, make it last:
		if ( ( $iOfThis === null) && ($this->$ordering > 0 ) ) {
			$order	=	$n+1;
			$this->_db->setQuery(
				'UPDATE '	. $this->_db->NameQuote( $this->_tbl )
				.	"\n SET " . $this->_db->NameQuote( $ordering )		   . ' = ' . ( (int) $order )
				.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . ' = ' . $this->_db->Quote( $this->$k )
			);
			$this->_db->query();
		}

		return true;
	}
}
