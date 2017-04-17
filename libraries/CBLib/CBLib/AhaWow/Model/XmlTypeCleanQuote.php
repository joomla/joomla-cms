<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/25/13 6:01 PM $
* @package CBLib\AhaWow\Model
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\Model;

use CBLib\Application\Application;
use CBLib\Registry\GetterInterface;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Registry\ParamsInterface;
// Temporary:
use \CBCookie;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Model\XmlTypeCleanQuote Utility class to clean-quote for SQL queries
 * 
 */
class XmlTypeCleanQuote
{
	/**
	 * Cleans the field value by type in a secure way for SQL
	 *
	 * @param  mixed                    $fieldValue
	 * @param  string                   $type           const,sql,param : string,int,float,datetime,formula
	 * @param  GetterInterface          $pluginParams
	 * @param  DatabaseDriverInterface  $db
	 * @param  array|null               $extDataModels
	 * @return string|boolean                           STRING: sql-safe value, Quoted or type-casted to int or float, or FALSE in case of type error
	 */
	public static function sqlCleanQuote( $fieldValue, $type, GetterInterface $pluginParams, DatabaseDriverInterface $db, array $extDataModels = null )
	{
		$typeArray		=	explode( ':', $type, 3 );
		if ( count( $typeArray ) < 2 ) {
			$typeArray	=	array( 'const' , $type );
		}

		if ( $typeArray[0] == 'param' ) {
			$fieldValue	=	$pluginParams->get( $fieldValue );
		} elseif ( $typeArray[0] == 'user' ) {
			// TODO: Change this to use Inversion Of Control, and allow XML valuetypes to be extended dynamically (e.g. instead of calling specifically CBLib\CB\User or similar when available, it is CB that adds the type and a closure to handle that type.

			if ( $fieldValue == 'viewaccesslevels' ) {
				$fieldValue			=	Application::MyUser()->getAuthorisedViewLevels();
			} else {
				if ( $fieldValue == 'usergroups' ) {
					$fieldValue		=	Application::MyUser()->getAuthorisedGroups( false );
				} else {
					$fieldValue		=	\CBuser::getMyUserDataInstance()->get( $fieldValue );
				}
			}
		} elseif ( in_array( $typeArray[0], array( 'request', 'get', 'post', 'cookie', 'cbcookie', 'session', 'server', 'env' ) ) ) {
			$fieldValue	=	self::_globalConv( $typeArray[0], $fieldValue );
		} elseif ( $typeArray[0] == 'ext' ) {
			if ( isset( $typeArray[2] ) && $extDataModels && isset( $extDataModels[$typeArray[2]] ) ) {
				$model					=	$extDataModels[$typeArray[2]];
				if ( is_object( $model ) ) {
					if ( $model instanceof ParamsInterface ) {
						$fieldValue		=	$model->get( $fieldValue );
					}
					elseif ( isset( $model->$fieldValue ) ) {
						$fieldValue		=	$model->$fieldValue;
					}
				} elseif ( is_array( $model ) ) {
					if ( isset( $model[$fieldValue] ) ) {
						$fieldValue		=	$model[$fieldValue];
					}
				} else {
					$fieldValue		=	$model;
				}
			} else {
				trigger_error( 'SQLXML::sqlCleanQuote: ERROR: ext valuetype "' . htmlspecialchars( $type ).'" has not been setExternalDataTypeValues.', E_USER_NOTICE );
			}
			// } elseif ( ( $typeArray[0] == 'const' ) || ( $cnt_valtypeArray[0] == 'sql' ) {
			//	$fieldValue	=	$fieldValue;
		}

		if ( is_array( $fieldValue ) ) {
			return self::cleanArrayType( $fieldValue, $typeArray[1], $db );
		}

		return self::cleanScalarType( $fieldValue, $typeArray[1], $db );
	}

	/**
	 * Cleans an array type $fieldValue so that each value fits $type, then transforms to an SQL array ( value1, value2, ... )
	 *
	 * @param  mixed                    $fieldValues
	 * @param  string                   $type
	 * @param  DatabaseDriverInterface  $db
	 * @return string
	 */
	protected static function cleanArrayType( $fieldValues, $type, DatabaseDriverInterface $db )
	{
		foreach ( $fieldValues as &$value ) {
			$value			=	self::cleanScalarType( $value, $type, $db );
		}

		return '(' . implode( ', ', $fieldValues ) . ')';
	}

	/**
	 * Cleans a scalar $fieldValue to fit $type
	 *
	 * @param  mixed                    $fieldValue
	 * @param  string                   $type
	 * @param  DatabaseDriverInterface  $db
	 * @return float|int|string
	 */
	protected static function cleanScalarType( $fieldValue, $type, DatabaseDriverInterface $db )
	{
		switch ( $type ) {
			case 'int':
				$value		=	(int) $fieldValue;
				break;
			case 'float':
				$value		=	(float) $fieldValue;
				break;
			case 'formula':
				$value		=	$fieldValue;
				break;
			case 'datetime':
				if ( preg_match( '/[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}/', $fieldValue ) ) {
					$value	=	$db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'date':
				if ( preg_match( '/[0-9]{4}-[01][0-9]-[0-3][0-9]/', $fieldValue ) ) {
					$value	=	$db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'time':
				if ( preg_match( '/-?[0-9]{1,3}(:[0-5][0-9]){2}/', $fieldValue ) ) {
					$value	=	$db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'string':
				$value		=	$db->Quote( $fieldValue );
				break;
			case 'null':
				$value		=	'NULL';
				break;

			default:
				trigger_error( 'SQLXML::sqlCleanQuote: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
				$value		=	$db->Quote( $fieldValue );	// false;
				break;
		}
		return $value;
	}
	/**
	 * Gets a cleaned value from a PHP global
	 *
	 * @param  string $arn
	 * @param  string $name
	 * @param  mixed  $def
	 * @return mixed
	 */
	protected static function _globalConv( $arn, $name, $def = null )
	{
		switch ( $arn ) {
			case 'request':
				$value	=	Application::Input()->get( $name, 0, GetterInterface::STRING );
				break;

			case 'get':
			case 'post':
			case 'cookie':
			case 'server':
			case 'env':
				$value	=	Application::Input()->get( $arn . '/' . $name, 0, GetterInterface::STRING );
				break;

			case 'session':
				$value	=	Application::Session()->get( $name, null, GetterInterface::STRING );
				break;

			case 'cbcookie':
				$value	=	CBCookie::getcookie( $name, $def );
				break;

			default:
				trigger_error( sprintf( 'SQLXML::globalconv error: unknown type %s for %s.', $arn, $name ), E_USER_NOTICE );
				$value	=	null;
				break;
		}
		return stripslashes( $value );
	}
}
