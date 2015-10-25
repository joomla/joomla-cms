<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/2/14 6:18 PM $
* @package CB\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database\Table;

use CBLib\Database\Table\OrderedTable;
use CBLib\Database\Table\TableInterface;
use CBLib\Language\CBTxt;
use CBLib\Registry\RegistryInterface;
// Temporary:
use cbFieldHandler;
use cbFieldParamsHandler;
use cbTabs;
use moscomprofilerHTML;

defined('CBLIB') or die();

/**
 * CB\Database\Table\FieldTable Class implementation
 * 
 */
class FieldTable extends OrderedTable
{
	/** @var int */
	public $fieldid			=	null;
	/** @var string */
	public $name			=	null;
	/** @var string */
	public $tablecolumns	=	null;
	/** @var string */
	public $table			=	null;
	/** @var string */
	public $title			=	null;
	/** @var string */
	public $description		=	null;
	/** @var string */
	public $type			=	null;
	/** @var int */
	public $maxlength		=	null;
	/** @var int */
	public $size			=	null;
	/** @var int */
	public $required		=	null;
	/** @var int */
	public $tabid			=	null;
	/** @var int */
	public $ordering		=	null;
	/** @var int */
	public $cols			=	null;
	/** @var int */
	public $rows			=	null;
	/** @var string */
	public $value			=	null;
	/** @var string */
	public $default			=	null;
	/** @var int */
	public $published		=	null;
	/** @var int */
	public $registration	=	null;
	/** @var int */
	public $edit			=	null;
	/** @var int */
	public $profile			=	null;
	/** @var int */
	public $readonly		=	null;
	/** @var int */
	public $searchable		=	null;
	/** @var int */
	public $calculated		=	null;
	/** @var int */
	public $sys				=	null;
	/** @var int */
	public $pluginid		=	null;
	/** @var string */
	public $cssclass		=	null;
	/**
	 * Field's params: once loaded properly contains:
	 * @var RegistryInterface|string
	 */
	public $params			=	null;

	/** @var array */
	protected $_fieldvalues	=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl			=	'#__comprofiler_fields';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key		=	'fieldid';

	/**
	 * Ordering keys and for each their ordering groups.
	 * E.g.; array( 'ordering' => array( 'tab' ), 'ordering_registration' => array() )
	 * @var array
	 */
	protected $_orderings	=	array( 'ordering' => array( 'tabid' ) );

	/**
	 * Copy the named array or object content into this object as vars
	 * only existing vars of object are filled.
	 * When undefined in array, object variables are kept.
	 *
	 * WARNING: DOES addslashes / escape BY DEFAULT
	 *
	 * Can be overridden or overloaded.
	 *
	 * @param  array|object  $array         The input array or object
	 * @param  string        $ignore        Fields to ignore
	 * @param  string        $prefix        Prefix for the array keys
	 * @return boolean                      TRUE: ok, FALSE: error on array binding
	 */
	public function bind( $array, $ignore = '', $prefix = null ) {
		$bind					=	parent::bind( $array, $ignore, $prefix );

		if ( $bind ) {
			// Set the ignore variable up like bind does encase this bind call was told to ignore field values:
			$ignore				=	' ' . $ignore . ' ';

			// Bind was successful; lets try and bind our private variable containing field values:
			$k					=	'_fieldvalues';

			// Use the same behavior as a normal bind excluding the _ ignore check for consistency:
			if ( strpos( $ignore, ' ' . $k . ' ') === false ) {
				$ak				=	$prefix . $k;

				if ( is_array( $array ) && isset( $array[$ak] ) ) {
					$this->$k	=	$array[$ak];
				} elseif ( isset( $array->$ak ) ) {
					$this->$k	=	$array->$ak;
				}
			}
		}

		return $bind;
	}

	/**
	 *	Check values before store method
	 *
	 *	@return boolean  TRUE if the object is safe for saving
	 */
	public function check() {
		global $_PLUGINS;

		if ( ! $this->name ) {
			$this->_error			=	CBTxt::T( 'Name missing!' );

			return false;
		} elseif ( in_array( $this->type, array( 'password', 'userparams' ) ) && ( $this->searchable == 1 ) ) {
			$this->_error			=	CBTxt::T( 'Private fields cannot be searchable!' );

			return false;
		} elseif ( $this->fieldid ) {
			$field					=	new FieldTable( $this->_db );

			$field->load( $this->fieldid  );

			if ( ( $this->sys == 1 ) && ( $this->published != $field->published ) ) {
				$this->_error		=	CBTxt::T( 'System fields publish state cannot be changed!' );

				return false;
			} elseif ( ( $this->sys == 1 ) && ( $this->type != $field->type ) ) {
				$this->_error		=	CBTxt::T( 'System fields type cannot be changed!' );

				return false;
			} elseif ( ( $this->sys == 1 ) && ( $this->name != $field->name ) ) {
				$this->_error		=	CBTxt::T( 'System fields name cannot be changed!' );

				return false;
			} elseif ( ( $this->calculated == 1 ) && ( $this->type != $field->type ) ) {
				$this->_error		=	CBTxt::T( 'Calculated fields type cannot be changed!' );

				return false;
			} elseif ( ( $this->calculated == 1 ) && ( $this->name != $field->name ) ) {
				$this->_error		=	CBTxt::T( 'Calculated fields name cannot be changed!' );

				return false;
			} elseif ( ( $this->tablecolumns == '' ) && ( $this->searchable == 1 ) ) {
				$this->_error		=	CBTxt::T( 'Calculated fields cannot be searchable!' );

				return false;
			} else {
				$_PLUGINS->loadPluginGroup( 'user' );

				$fieldHandler		=	new cbFieldHandler();
				$fieldXML			=	$fieldHandler->_loadFieldXML( $field );

				if ( $fieldXML && ( $fieldXML->attributes( 'unique' ) == 'true' ) ) {
					if ( $this->type != $field->type ) {
						$this->_error	=	CBTxt::T( 'Unique fields type cannot be changed!' );

						return false;
					} elseif ( $this->name != $field->name ) {
						$this->_error	=	CBTxt::T( 'Unique fields name cannot be changed!' );

						return false;
					}
				}
			}
		}

		return true;
	}

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
	public function store( $updateNulls = false ) {
		global $_PLUGINS;

		$k						=	$this->_tbl_key;

		$_PLUGINS->loadPluginGroup( 'user' );

		$fieldHandler			=	new cbFieldHandler();
		$fieldXML				=	$fieldHandler->_loadFieldXML( $this );

		// Rename non-system, non-calcualted, non-unique fields to ensure proper DB name structure:
		if ( ( ! $this->sys ) && ( ! $this->calculated ) && ( ! ( $fieldXML && ( $fieldXML->attributes( 'unique' ) == 'true' ) ) ) ) {
			// Always use lowercase names:
			$name				=	strtolower( $this->name );
			// Replace cb prefix to be added later:
			$name				=	preg_replace( '/^cb_/', '', $name );
			// Replace all invalid characters:
			$name				=	preg_replace( '/[^a-zA-Z0-9_]+/', '', $name );
			// Replace duplicate underscores:
			$name				=	preg_replace( '/(_{2,})+/', '', $name );
			// Replace leading underscores:
			$name				=	preg_replace( '/^_/', '', $name );
			// Set the new name for this field; always:
			$this->name			=	'cb_' . $name;

			if ( $this->fieldid ) {
				$field			=	new FieldTable( $this->_db );

				$field->load( $this->fieldid  );

				// Check if existing fields name has changed:
				if ( $this->name != $field->name ) {
					$columns				=	$this->getTableColumns();

					// Rename the database columns for this field as the name changed (we need to loop them encase it has more than 1 column like image fields):
					foreach ( $columns as $column ) {
						$this->_db->renameColumn( $this->table, $column, str_replace( $field->name, $this->name, $column ) );
					}

					// Rebuild the tablecolumns so the field row knows about its new column names:
					$this->tablecolumns		=	implode( ',', $fieldHandler->getMainTableColumns( $this ) );
				}
			}
		}

		// Fix HTML-editor messy addition of <p> or <div> in description which messes with translation keys:
		$this->description		=	$this->cleanEditorsTranslationJunk( $this->description );

		if ( $this->$k ) {
			// Existing Field: Store and adapt comprofiler table using Xml description of field:
			$return				=	parent::store( $updateNulls );

			if ( $return ) {
				$return			=	$fieldHandler->adaptSQL( $this, true, false, true );
			}
		} else {
		 	// New Field: Check that there is no clash on the unique name:
			$query				=	'SELECT COUNT(*)'
								.	"\n FROM " . $this->_db->NameQuote( $this->_tbl )
								.	"\n WHERE " . $this->_db->NameQuote( 'name' ) . " = " . $this->_db->Quote( $this->name );
			$this->_db->setQuery( $query );
			if ( $this->_db->LoadResult() > 0 ) {
				$this->_error	=	CBTxt::T( 'THIS_FIELD_NAME_NAME_IS_ALREADY_IN_USE', 'The field name [name] is already in use!', array( '[name]' => $this->name ) );

				return false;
			}

			$this->table		=	$fieldHandler->getMainTable( $this );
			$this->tablecolumns	=	implode( ',', $fieldHandler->getMainTableColumns( $this ) );

			if ( ( $this->tablecolumns == '' ) && ( $this->searchable == 1 ) ) {
				// Fields with no columns can't be searched; lets make sure it's not toggled to be searchable:
				$this->searchable	=	0;
			}

			// This handles ordering field setting too:
			$return				=	parent::store( $updateNulls );

			if ( $return ) {
				$return			=	$fieldHandler->adaptSQL( $this );
			}
		}

		if ( $return && $this->$k && ( $this->_fieldvalues !== null ) ) {
			$fieldValues		=	( is_string( $this->_fieldvalues ) ? json_decode( $this->_fieldvalues, true ) : $this->_fieldvalues );

			// Delete all current field values and Insert new field values:
			$fieldValuesTable	=	new FieldValueTable( $this->_db );
			$fieldValuesTable->updateFieldValues( $this->$k, $fieldValues );
		}

		if ( ! $return ) {
			$this->_error		=	CBTxt::T( 'CLASS_STORE_FAILED_ERROR', '[class]::store failed: [error]', array( '[class]' => get_class( $this ), '[error]' => addslashes( str_replace( "\n", '\n', $this->_error . ' ' . $this->_db->getErrorMsg() ) ) ) );

			return false;
		} else {
			return true;
		}
	}

	/**
	 * Returns the name of the table of the fields values (e.g. for multiple-valued fields)
	 * E.g. '#__comprofiler_field_values' for '#__comprofiler_fields'
	 *
	 * @return string
	 */
	protected function fieldValuesTableName( )
	{
		// '#__comprofiler_field_values' = substr( '#__comprofiler_fields', 0, -1 ) . '_values':
		return substr( $this->_tbl, 0, -1 ) . '_values';
	}
	/**
	 * Check for whether dependencies exist for this object in the db schema
	 *
	 * @param  null|TableInterface  $object  The object being copied to otherwise $this
	 * @return boolean                       True: Can Copy, False: Cannot Copy
	 */
	public function canCopy( $object = null ) {
		global $_PLUGINS;

		if ( $object === null ) {
			$object				=	$this;
		}

		if ( ! $object->fieldid ) {
			$object->_error		=	CBTxt::T( 'Select a field to copy.' );

			return false;
		} elseif ( $object->sys == 1 ) {
			$object->_error		=	CBTxt::T( 'System fields cannot be copied!' );

			return false;
		} elseif ( $object->calculated == 1 ) {
			$object->_error		=	CBTxt::T( 'Calculated fields cannot be copied!' );

			return false;
		} else {
			$_PLUGINS->loadPluginGroup( 'user' );

			$fieldHandler		=	new cbFieldHandler();
			$fieldXML			=	$fieldHandler->_loadFieldXML( $object );

			if ( ( $fieldXML && ( $fieldXML->attributes( 'unique' ) == 'true' ) ) ) {
				$object->_error	=	CBTxt::T( 'Unique fields cannot be copied!' );

				return false;
			}
		}

		return true;
	}

	/**
	 * Copies this record (no checks) (field and its fieldvalues)
	 *
	 * @param  null|TableInterface|self  $object  The object being copied otherwise create new object and add $this
	 * @return self|boolean                       OBJECT: The new object copied successfully, FALSE: Failed to copy
	 */
	public function copy( $object = null ) {

		if ( $object === null ) {
			$object					=	clone $this;
		}

		//TODO: This algorithm below to determine the new name could be factored out as reusable:

		// Grab index of field from fields with same name
		$query					=	'SELECT ' . $this->_db->NameQuote( 'name' )
			.	"\n FROM "	   . $this->_db->NameQuote( $this->_tbl )
			.	"\n WHERE "    . $this->_db->NameQuote( 'name' ) . " REGEXP " . $this->_db->Quote( '^' . preg_quote( $object->name ) . '[0-9]*$' )
			.	"\n ORDER BY " . $this->_db->NameQuote( 'name' );
		$this->_db->setQuery( $query );
		$names						=	$this->_db->loadResultArray();
		$count						=	count( $names );

		// Only increment if there's something to increment as the name could be changed before copy is called, which would be a 0 count:
		if ( $count ) {
			// Increment index by 1 based off similar field name count:
			$index					=	( $count + 1 );

			// Loop through and make sure the index is unique; if not keep incrementing until it is:
			do {
				$changed			=	false;

				foreach ( $names as $v ) {
					if ( $v == ( $object->name . $index ) ) {
						$index++;

						$changed	=	true;
					}
				}
			} while ( $changed );

			$object->name			=	$object->name . $index;
			$object->title			=	$object->title . ' (' . $index . ')';
		}

		// Grab existing field values so they can be pushed to the copied field:
		$fieldValuesTable			=	new FieldValueTable( $this->_db );
		$k							=	$this->_tbl_key;
		$object->_fieldvalues		=	$fieldValuesTable->getFieldValuesOfField( $object->$k );

		return parent::copy( $object );
	}

	/**
	 * Generic check for whether dependancies exist for this object in the db schema
	 *
	 * @param  int  $oid  key index
	 * @return boolean
	 */
	public function canDelete( $oid = null ) {
		if ( $oid === null ) {
			$k					=	$this->_tbl_key;
			$oid				=	$this->$k;
		}

		if ( $this->sys ) {
			$this->_error		=	CBTxt::T( 'System fields cannot be deleted!' );

			return false;
		}

		return true;
	}

	/**
	 * Deletes this record (no checks)
	 * Delete method for fields deleting also fieldvalues, and the data column(s) in the comprofiler table.
	 *
	 * @param  int      $oid   Key id of row to delete (otherwise it's the one of $this)
	 * @return boolean         TRUE if OK, FALSE if error
	 */
	public function delete( $oid = null ) {
		$k							=	$this->_tbl_key;

		if ( $oid ) {
			$this->$k				=	(int) $oid;
		}

		$fieldHandler				=	new cbFieldHandler();

		$result						=	$fieldHandler->adaptSQL( $this, 'drop' );

		if ( $result ) {
			//delete each field value related to a field
			$rowFieldValues			=	new FieldValueTable( $this->_db );
			$result					=	$rowFieldValues->updateFieldValues( $this->$k, array() );

			//Now delete the field itself without deleting the user data, preserving it for reinstall
			//$this->deleteColumn( $this->table, $this->name );	// this would delete the user data
			$result					=	parent::delete( $this->$k ) && $result;
		}

		return $result;
	}

	/**
	 * Returns the database columns used by the field
	 *
	 * @return array    Names of columns
	 */
	public function getTableColumns() {
		if ( $this->tablecolumns !== null ) {
			if ( $this->tablecolumns === '' ) {
				return array();
			} else {
				return explode( ',', $this->tablecolumns );
			}
		} else {
			return array( $this->name );		// pre-CB 1.2 database structure support
		}
	}

	/**
	 * This function will probably be removed: DO NOT USE outside of TabTable:
	 * Counts number of fields for a given tab
	 *
	 * @param  string  $tabId  Tab id
	 * @return int             Number of fields
	 */
	public function countFieldsOfTab( $tabId )
	{
		$query	=	"SELECT COUNT(*)"
				.	"\n FROM " . $this->_db->NameQuote( $this->_tbl )
				.	"\n WHERE " . $this->_db->NameQuote( 'tabid' ) . " = " . (int) $tabId;
		$this->_db->setQuery( $query );

		return (int) $this->_db->loadResult();
	}

	/**
	 * BACKEND XML FUNCTIONS:
	 */

	/**
	 * returns true or false if plugin is compatible with current major CB release
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML tabs backend
	 *
	 * @return boolean
	 */
	public function checkPluginCompatibility( ) {
		global $_PLUGINS;

		return $_PLUGINS->checkPluginCompatibility( $this->pluginid );
	}

	/**
	 * returns true or false if field is unique
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML fields backend
	 *
	 * @return boolean
	 */
	public function checkFieldUnique( ) {
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user' );

		$fieldHandler	=	new cbFieldHandler();
		$fieldXML		=	$fieldHandler->_loadFieldXML( $this );

		return ( $fieldXML && ( $fieldXML->attributes( 'unique' ) == 'true' ) );
	}

	/**
	 * USED by XML interface ONLY !!!
	 * @deprecated Do not use directly, only for XML fields backend
	 *
	 * @param  string             $value  The value of the element
	 * @param  RegistryInterface  $pluginParams
	 * @param  string             $name  The name of the form element
	 * @param  \SimpleXMLElement  $node  The xml element for the parameter
	 * @return string HTML to display
	 */
	public function renderFieldTypeSelector( /** @noinspection PhpUnusedParameterInspection */ $value, $pluginParams, $name, $node )
	{
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user' );

		$parent						=	$node->xpath( '..' );

		if ( $parent && ( $parent[0]->getName() == 'filter' ) ) {
			$checkNotSys			=	false;
		} else {
			$checkNotSys			=	true;
		}

		$fieldHandler				=	new cbFieldHandler();
		$fieldXML					=	$fieldHandler->_loadFieldXML( $this );

		$unique						=	( $fieldXML && ( $fieldXML->attributes( 'unique' ) == 'true' ) );

		if ( ( $this->fieldid > 0 ) && ( $this->sys || $this->calculated || $unique ) ) {
			$fieldHandler			=	new cbFieldHandler();
			$types[] = moscomprofilerHTML::makeOption( $this->type, CBTxt::T( $fieldHandler->getFieldTypeLabel( $this, false ) ) );
		} else {
			$types					=	array();
			$typeHandlers			=	array();

			$registeredTypes		=	$_PLUGINS->getUserFieldTypes();
			foreach ( $registeredTypes as $typ ) {
				$typeHandlers[$typ]	=	new cbFieldHandler();
				$tmpField			=	new self( $this->_db );
				$tmpField->type		=	$typ;

				/** @var cbFieldHandler[] $typeHandlers */
				$typLabel			=	$typeHandlers[$typ]->getFieldTypeLabel( $tmpField, $checkNotSys );
				if ( $typLabel ) {
					$types[]		=	moscomprofilerHTML::makeOption( $typ, CBTxt::T( $typLabel ) );
				}
			}
		}
		return $types;
	}

	/**
	 * USED by XML interface ONLY !!!
	 * @deprecated Do not use directly, only for XML fields backend
	 *
	 * @return string
	 */
	public function renderFieldTypeLabel( )
	{
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user' );

		$fieldHandler	=	new cbFieldHandler();

		$type			=	$fieldHandler->getFieldTypeLabel( $this, false );

		if ( ! $type ) {
			$type		=	$this->type;
		}

		return CBTxt::T( $type );
	}

	/**
	 * adds field values array to xml data
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML tabs backend
	 *
	 * @param string             $value
	 * @param RegistryInterface  $pluginParams
	 * @param string             $name
	 * @param \SimpleXMLElement  $node
	 * @param string             $control_name
	 * @param string             $control_name_name
	 * @param boolean            $view
	 * @param RegistryInterface  $data
	 */
	public function fetchFieldValues( /** @noinspection PhpUnusedParameterInspection */ $value, $pluginParams, $name, $node, $control_name, $control_name_name, $view, $data ) {
		if ( $this->fieldid > 0 ) {
			$fieldValuesTable	=	new FieldValueTable( $this->_db );
			$fieldValues		=	$fieldValuesTable->getFieldValuesOfField( (int) $this->fieldid );
		} else {
			$fieldValues		=	array();
		}

		$data->set( '_fieldvalues', $fieldValues );
	}
}
