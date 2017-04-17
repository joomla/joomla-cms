<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Input\Get;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CB\Database\Table\FieldTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onBeforeDeleteUser', 'onBeforeDeleteUser', 'CBfield_image' );
$_PLUGINS->registerFunction( 'onBeforeDeleteUser', 'deleteFiles', 'CBfield_file' );
$_PLUGINS->registerUserFieldTypes( array( 	'checkbox'				=> 'CBfield_checkbox',
											'multicheckbox'			=> 'CBfield_select_multi_radio',
											'date'					=> 'CBfield_date',
											'datetime'				=> 'CBfield_date',
											'select'				=> 'CBfield_select_multi_radio',
											'multiselect'			=> 'CBfield_select_multi_radio',
											'emailaddress'			=> 'CBfield_email',
											'primaryemailaddress'	=> 'CBfield_email',
											'editorta'				=> 'CBfield_editorta',
											'textarea'				=> 'CBfield_textarea',
											'text'					=> 'CBfield_text',
											'integer'				=> 'CBfield_integer',
											'radio'					=> 'CBfield_select_multi_radio',
											'webaddress'			=> 'CBfield_webaddress',
											'pm'					=> 'CBfield_pm',
											'image'					=> 'CBfield_image',
											'status'				=> 'CBfield_status',
											'formatname'			=> 'CBfield_formatname',
											'predefined'			=> 'CBfield_predefined',
											'counter'				=> 'CBfield_counter',
											'connections'			=> 'CBfield_connections',
											'password'				=> 'CBfield_password',
											'hidden'				=> 'CBfield_text',
											'delimiter'				=> 'CBfield_delimiter',
											'userparams'			=> 'CBfield_userparams',
											'file'					=> 'CBfield_file',
											'video'					=> 'CBfield_video',
											'audio'					=> 'CBfield_audio',
											'rating'				=> 'CBfield_rating',
											'points'				=> 'CBfield_points',
											'terms'					=> 'CBfield_terms' ) );	// reserved, used now: 'other_types'
																								// future reserved: 'all_types'
$_PLUGINS->registerUserFieldParams();


/**
 * Commented CBT calls for language parser pickup
 * CBTxt::T( '_UE_ADDITIONAL_INFO_HEADER', 'Additional Information' )
 * CBTxt::T( '_UE_Website', 'Web site' )
 * CBTxt::T( '_UE_Location', 'Location' )
 * CBTxt::T( '_UE_Occupation', 'Occupation' )
 * CBTxt::T( '_UE_Interests', 'Interests' )
 * CBTxt::T( '_UE_Company', 'Company' )
 * CBTxt::T( '_UE_City', 'City' )
 * CBTxt::T( '_UE_State', 'State' )
 * CBTxt::T( '_UE_ZipCode', 'Zip Code' )
 * CBTxt::T( '_UE_Country', 'Country' )
 * CBTxt::T( '_UE_Address', 'Address' )
 * CBTxt::T( '_UE_PHONE', 'Phone #' )
 * CBTxt::T( '_UE_FAX', 'Fax #' )
 */


class CBfield_text extends cbFieldHandler {

	/**
	 * formats variable array into data attribute string
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $output
	 * @param  string       $reason
	 * @param  array        $attributeArray
	 * @return null|string
	 */
	protected function getDataAttributes( $field, $user, $output, $reason, $attributeArray = array() ) {
		$pregExp					=	$this->_getRegexp( $field );

		if ( $pregExp ) {
			$attributeArray[]		=	cbValidator::getRuleHtmlAttributes( 'pattern', $pregExp, $this->pregExpErrorText( $field ) );
		}

		return parent::getDataAttributes( $field, $user, $output, $reason, $attributeArray );
	}

	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return boolean                  True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		$validated						=	parent::validate( $field, $user, $columnName, $value, $postdata, $reason );
		if ( $validated && ( $value !== '' ) && ( $value !== null ) ) {		// empty values (e.g. non-mandatory) are treated in the parent validation.
			$pregExp					=	$this->_getRegexp( $field );
			if ( $pregExp ) {
				$validated				=	preg_match( $pregExp, $value );
				if ( ! $validated ) {
					$pregExpError		=	$this->pregExpErrorText( $field );
					$this->_setValidationError( $field, $user, $reason, $pregExpError );
				}
			}
		}
		return $validated;
	}

	/**
	 * Gets the regular expression to validate
	 * @param  FieldTable  $field  Field
	 * @return string
	 */
	protected function _getRegexp( $field ) {
		$fieldValidateExpression		=	$field->params->get( 'fieldValidateExpression', '' );
		if ( $fieldValidateExpression != '' ) {
			$possibilities				=	array(	'singleword'		=>	'/^[a-z]*$/i',
											'multiplewords'		=>	'/^([a-z]+ *)*$/i',
											'singleaznum'		=>	'/^[a-z]+[a-z0-9_]*$/i',
											'atleastoneofeach'	=>	'/^(?=.*\d)(?=.*(\W|_))(?=.*[a-z])(?=.*[A-Z]).{6,255}$/'
										 );
			if ( isset( $possibilities[$fieldValidateExpression] ) ) {
				$pregExp				=	$possibilities[$fieldValidateExpression];
			} elseif ( $fieldValidateExpression == 'customregex' ) {
				$pregExp				=	$field->params->get( 'pregexp', '/^.*$/' );
			} else {
				$pregExp				=	null;
			}

			if ( ! preg_match( "#^/(?:\\\\/|[^/\\n\\r])+/[a-z]*\$#", $pregExp ) ) {
				// it's not a valid regexp: do not use it!:
				$pregExp				=	null;
			}
		} else {
			$pregExp					=	null;
		}
		return $pregExp;
	}

	/**
	 * Returns translated specific or generic 'Not a valid input' error
	 *
	 * @param  FieldTable  $field
	 * @return string
	 */
	protected function pregExpErrorText( $field )
	{
		$pregExpError		=	$field->params->get( 'pregexperror', '' );
		if ( $pregExpError ) {
			return CBTxt::T( $pregExpError , null, array( '[FIELDNAME]' => $field->title ) );
		}

		return CBTxt::T( 'NOT_A_VALID_INPUT', 'Not a valid input', array( '[FIELDNAME]' => $field->title ) );
	}
}

class CBfield_textarea extends CBfield_text {
	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		switch ( $output ) {
			case 'html':
			case 'rss':
				return str_replace( "\n", '<br />', parent::getField( $field, $user, $output, $reason, $list_compare_types ) );
			default:
				return parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}
	}
	/**
	 * converts to HTML
	 * Override to change the field type from textarea to text in case of searches.
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  string      $tag         <tag
	 * @param  string      $type        type="$type"
	 * @param  string      $value       value="$value"
	 * @param  string      $additional  'xxxx="xxx" yy="y"'  WARNING: No classes in here, use $classes
	 * @param  string      $allValues
	 * @param  boolean     $displayFieldIcons
	 * @param  array       $classes     CSS classes
	 * @return string                   HTML: <tag type="$type" value="$value" xxxx="xxx" yy="y" />
	 */
	protected function _fieldEditToHtml( &$field, &$user, $reason, $tag, $type, $value, $additional, $allValues = null, $displayFieldIcons = true, $classes = null ) {
		$rows					=	$field->rows;

		if ( $reason == 'search' ) {
			if ( $rows > 5 ) {
				$field->rows	=	5;
			}
		}

		$return					=	 parent::_fieldEditToHtml( $field, $user, $reason, $tag, $type, $value, $additional, $allValues, $displayFieldIcons, $classes );

		if ( $reason == 'search' ) {
			$field->rows		=	$rows;
		}

		return $return;
	}
}

class CBfield_predefined extends CBfield_text {

	/**
	 * formats variable array into data attribute string
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $output
	 * @param  string       $reason
	 * @param  array        $attributeArray
	 * @return null|string
	 */
	protected function getDataAttributes( $field, $user, $output, $reason, $attributeArray = array() ) {
		global $ueConfig;

		if ( $field->name == 'username' ) {
			$attributeArray[]		=	cbValidator::getRuleHtmlAttributes( 'cbusername' );

			if ( $ueConfig['reg_username_checker'] == 1 ) {
				$attributeArray[]	=	cbValidator::getRuleHtmlAttributes( 'cbfield', array( 'user' => (int) $user->id, 'field' => htmlspecialchars( $field->name ), 'reason' => htmlspecialchars( $reason ) ) );
			}
		}

		return parent::getDataAttributes( $field, $user, $output, $reason, $attributeArray );
	}

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_CB_framework, $ueConfig;

		$value									=	$user->get( $field->get( 'name' ) );

		switch ( $output ) {
			case 'html':
			case 'rss':
				if ( ( $field->type == 'predefined' ) && $user->get( '_allowProfileLink', $field->get( '_allowProfileLink', 1 ) ) && ( $reason != 'profile' ) && ( $reason != 'edit' ) ) {
					return $this->formatFieldValueLayout( '<a href="' . $_CB_framework->userProfileUrl( $user->id, true ) . '">' . htmlspecialchars( $value ) . '</a>', $reason, $field, $user );
				} else {
					return parent::getField( $field, $user, $output, $reason, $list_compare_types );
				}
				break;
			case 'htmledit':
				if ( $field->name == 'username' ) {
					if ( ! ( ( $ueConfig['usernameedit'] == 0 ) && ( $reason == 'edit' ) && ( $_CB_framework->getUi() == 1 ) ) ) {
						$profile				=	$field->get( 'profile' );

						$field->set( 'profile', 1 );

						$return					=	parent::getField( $field, $user, $output, $reason, $list_compare_types );

						$field->set( 'profile', $profile );
					} else {
						$field->set( 'readonly', 1 );

						$return					=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
					}
				} else {
					$return						=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				}

				return $return;
				break;
			default:
				return parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}
	}

	/**
	 * Direct access to field for custom operations, like for Ajax
	 *
	 * WARNING: direct unchecked access, except if $user is set, then check
	 * that the logged-in user has rights to edit that $user.
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  array       $postdata
	 * @param  string      $reason     'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string                  Expected output.
	 */
	public function fieldClass( &$field, &$user, &$postdata, $reason ) {
		global $_CB_database, $ueConfig, $_GET;

		parent::fieldClass( $field, $user, $postdata, $reason ); // Performs spoof check

		$function							=	cbGetParam( $_GET, 'function', '' );
		$valid								=	true;
		$message							=	null;

		if ( ( $function == 'checkvalue' ) || ( $function == 'testexists' ) ) {
			$usernameChecker				=	( isset( $ueConfig['reg_username_checker'] ) ? $ueConfig['reg_username_checker'] : 0 );
			$username						=	stripslashes( cbGetParam( $postdata, 'value', '' ) );

			if ( $usernameChecker && ( ( $reason == 'edit' ) || ( $reason == 'register' ) ) ) {
				if ( ( ! $user ) || ( strtolower( trim( $username ) ) != strtolower( trim( $user->username ) ) ) ) {
					if ( ! $this->validate( $field, $user, 'username', $username, $postdata, $reason ) ) {
						global $_PLUGINS;

						$valid				=	false;
						$message			=	$_PLUGINS->getErrorMSG( '<br />' );
					} else {
						$query				=	'SELECT COUNT(*)'
											.	"\n FROM " . $_CB_database->NameQuote( '#__users' );
						if ( $_CB_database->isDbCollationCaseInsensitive() ) {
							$query			.=	"\n WHERE " . $_CB_database->NameQuote( 'username' ) . " = " . $_CB_database->Quote( trim( $username ) );
						} else {
							$query			.=	"\n WHERE LOWER( " . $_CB_database->NameQuote( 'username' ) . " ) = " . $_CB_database->Quote( strtolower( trim( $username ) ) );
						}
						$_CB_database->setQuery( $query );
						$exists				=	$_CB_database->loadResult();

						if ( $function == 'testexists' ) {
							if ( $exists ) {
								$message	=	CBTxt::Th( 'UE_USERNAME_EXISTS_ON_SITE', "The username '[username]' exists on this site.", array( '[username]' =>  htmlspecialchars( $username ) ) );
							} else {
								$valid		=	false;
								$message	=	CBTxt::Th( 'UE_USERNAME_DOESNT_EXISTS', "The username '[username]' does not exist on this site.", array( '[username]' =>  htmlspecialchars( $username ) ) );
							}
						} else {
							if ( $exists ) {
								$valid		=	false;
								$message	=	CBTxt::Th( 'UE_USERNAME_NOT_AVAILABLE', "The username '[username]' is already in use.", array( '[username]' =>  htmlspecialchars( $username ) ) );
							} else {
								$message	=	CBTxt::Th( 'UE_USERNAME_AVAILABLE', "The username '[username]' is available.", array( '[username]' =>  htmlspecialchars( $username ) ) );
							}
						}
					}
				}
			}
		}

		return json_encode( array( 'valid' => $valid, 'message' => $message ) );
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $ueConfig;

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		switch ( $field->name ) {
			case 'username':
				if ( ! ( ( $ueConfig['usernameedit'] == 0 ) && ( $reason == 'edit' ) && ( $_CB_framework->getUi() == 1 ) ) ) {
					$username				=	stripslashes( cbGetParam( $postdata, 'username', null ) );

					if ( $this->validate( $field, $user, $field->name, $username, $postdata, $reason ) ) {
						if ( ( $username !== null ) && ( $username !== $user->username ) ) {
							$this->_logFieldUpdate( $field, $user, $reason, $user->username, $username );
						}
					}

					if ( $username !== null ) {
						$user->username		=	$username;
					}
				}
				break;

			case 'name':
			case 'firstname':
			case 'middlename':
			case 'lastname':
				$value							=	stripslashes( cbGetParam( $postdata, $field->name ) );
				$col							=	$field->name;
				if ( $this->validate( $field, $user, $field->name, $value, $postdata, $reason ) ) {
					if ( ( (string) $user->$col ) !== (string) $value ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
					}
				}
				if ( $value !== null ) {
					// Form name from first/middle/last name if needed:
					if ( $field->name !== 'name' ) {
						$nameArr				=	array();
						if ( $ueConfig['name_style'] >= 2 ) {
							$firstname		=	stripslashes( cbGetParam( $postdata, 'firstname', $user->firstname ) );
							if ( $firstname ) {
								$nameArr[]	=	 $firstname;
							}
							if ( $ueConfig['name_style'] == 3 ) {
								$middlename	=	stripslashes( cbGetParam( $postdata, 'middlename', $user->middlename ) );
								if ( $middlename ) {
									$nameArr[]	=	$middlename;
								}
							}
							$lastname		=	stripslashes( cbGetParam( $postdata, 'lastname', $user->lastname ) );
							if ( $lastname ) {
								$nameArr[]	=	$lastname;
							}
						}
						if ( count( $nameArr ) > 0 ) {
							$user->name			=	implode( ' ', $nameArr );
						}
					}

					$user->$col					=	$value;
				}
				break;

			default:
				$this->_setValidationError( $field, $user, $reason, sprintf(CBTxt::T( 'Unknown field %s' ), $field->name) );
				break;
		}
	}
	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return boolean                  True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		$validated				=	parent::validate( $field, $user, $columnName, $value, $postdata, $reason );

		if ( $validated ) {
			if ( $field->name == 'username' ) {
				$validated		=	( ! preg_match( '#[<>"\'%;()&\\\\]|\\.\\./#', $value ) );

				if ( ! $validated ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'VALIDATION_ERROR_FIELD_USERNAME', 'Please enter a valid username with no space at beginning or end and must not contain the following characters: < > \ " \' % ; ( ) &' ) );
				}
			}
		}

		return $validated;
	}

	/**
	 * Returns the minimum field length as set
	 *
	 * @param  FieldTable  $field
	 * @return int
	 */
	function getMinLength( $field ) {
		$min						=	parent::getMinLength( $field );

		if ( $field->name == 'username' ) {
			if ( $min < 2 ) {
				$min				=	2;
			}
		}

		return $min;
	}

	/**
	 * Returns the maximum field length as set
	 *
	 * @param  FieldTable  $field
	 * @return int
	 */
	function getMaxLength( $field ) {
		$maxLen						=	parent::getMaxLength( $field );
		if ( $maxLen ) {
			return $maxLen;
		}
		if ( $field->name == 'username' ) {
			return 150;
		} else {
			return 100;
		}
	}
}
class CBfield_password extends CBfield_text {

	/**
	 * formats variable array into data attribute string
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $output
	 * @param  string       $reason
	 * @param  array        $attributeArray
	 * @return null|string
	 */
	protected function getDataAttributes( $field, $user, $output, $reason, $attributeArray = array() ) {
		if ( $field->params->get( 'passTestSrength' ) && ( ! isset( $field->_identicalTo ) ) ) {
			$attributeArray[]		=	cbValidator::getRuleHtmlAttributes( 'passwordstrength' );
		}

		return parent::getDataAttributes( $field, $user, $output, $reason, $attributeArray );
	}

	/**
	 * Returns a PASSWORD field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output      'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting  'table', 'td', 'span', 'div', 'none'
	 * @param  string      $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getFieldRow( &$field, &$user, $output, $formatting, $reason, $list_compare_types ) {
		global $ueConfig, $_CB_OneTwoRowsStyleToggle;

		$results									=	null;

		if ( $output == 'htmledit' ) {
			if ( ( $field->name != 'password' ) || ( $reason != 'register' ) || ! ( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == "1" ) ) ) {
				if ( $field->params->get( 'fieldVerifyInput', 1 ) ) {
					$verifyField					=	new FieldTable( $field->getDbo() );

					foreach ( array_keys( get_object_vars( $verifyField ) ) as $k ) {
						$verifyField->$k			=	$field->$k;
					}

					$verifyField->name				=	$field->name . '__verify';
					$verifyField->fieldid			=	$field->fieldid . '__verify';

					if ( $field->name == 'password' ) {
						$verifyField->title			=	CBTxt::Th( 'UE_VPASS', 'Verify Password' );
					} else {
						// cbReplaceVars to be done only once later:
						$titleOfVerifyField			=	$field->params->get( 'verifyPassTitle' );
						if ( $titleOfVerifyField ) {
							$verifyField->title		=	CBTxt::Th( $titleOfVerifyField, null, array( '%s' => CBTxt::T( $field->title ) ) );
						} else {
							$verifyField->title		=	CBTxt::Th( '_UE_VERIFY_SOMETHING', 'Verify %s', array( '%s' => CBTxt::T( $field->title ) ) );
						}
					}

					$verifyField->_identicalTo		=	$field->name;
				}

				$toggleState						=	$_CB_OneTwoRowsStyleToggle;

				$results							=	parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );

				if ( $field->params->get( 'fieldVerifyInput', 1 ) ) {
					$_CB_OneTwoRowsStyleToggle		=	$toggleState;

					$results						.=	parent::getFieldRow( $verifyField, $user, $output, $formatting, $reason, $list_compare_types );

					unset( $verifyField );
				}
			} else {
				// case of "sending password by email" at registration time for main password field:
				$results							=	parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );
			}
		} else {
			$results								=	parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );
		}

		return $results;
	}
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $ueConfig;

		$value									=	'';			// passwords are never sent back to forms.

		switch ( $output ) {
			case 'htmledit':
				if ( $reason == 'search' ) {
					return null;
				}

			if ( ( $field->name != 'password' ) || ( $reason != 'register' ) || ! ( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == "1" ) ) ) {

					$req							=	$field->required;
					if ( ( $reason == 'edit' ) && in_array( $field->name, array( 'password', 'password__verify' ) ) ) {
						$field->required			=	0;
					}

					$html							=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', $field->type, $value, $this->getDataAttributes( $field, $user, $output, $reason ) );
					$field->required				=	$req;

				} else {
					// case of "sending password by email" at registration time for main password field:
					$html							=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'html', CBTxt::Th( 'SENDING_PASSWORD', 'Your password will be sent to the above e-mail address.<br />Once you have received your new password you can log in and change it.' ), '' );
				}
				return $html;
				break;

			case 'html':
				return CBTxt::T( 'HIDDEN_CHARACTERS', '********' );
				break;
			default:
				return null;
				break;
		}
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $ueConfig;

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		// For CB main password don't save if it's on registration and passwords are auto-generated.
		if ( ( $reason == 'register' ) && ( $field->name == 'password' ) ) {
			if ( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == "1" ) ) {
				return;
			}
		}

		foreach ( $field->getTableColumns() as $col ) {
			$value					=	stripslashes( cbGetParam( $postdata, $col,				'', _CB_ALLOWRAW ) );
			$valueVerify			=	stripslashes( cbGetParam( $postdata, $col . '__verify',	'', _CB_ALLOWRAW ) );

			$fieldRequired			=	$field->required;

			if ( ( ( $reason == 'edit' ) && ( $user->id != 0 ) && ( $user->$col || ( $field->name == 'password' ) ) ) || ( $_CB_framework->getUi() == 2 ) ) {
				$field->required	=	0;
			}

			$this->validate( $field, $user, $col, $value, $postdata, $reason );

			if ( ( ( $reason == 'edit' ) && ( $user->id != 0 ) && ( $user->$col || ( $field->name == 'password' ) ) ) || ( $_CB_framework->getUi() == 2 ) ) {
				$field->required	=	$fieldRequired;
			}

			$fieldMinLength			=	$this->getMinLength( $field );

			$user->$col				=	null;		// don't update unchanged (hashed) passwords unless typed-in and all validates:
			if ( $value ) {
				if ( cbIsoUtf_strlen( $value ) < $fieldMinLength ) {
					$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'UE_VALID_PASS_CHARS', 'Please enter a valid %s.  No spaces, at least %s characters and contain lower and upper-case letters, numbers and special signs' ), CBTxt::T( 'UE_PASS', 'Password' ), $fieldMinLength ) );
				} elseif ( $field->params->get( 'fieldVerifyInput', 1 ) && ( $value != $valueVerify ) ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_REGWARN_VPASS2', 'Password and verification do not match, please try again.' ) );
				} else {
					// There is no event for password changes on purpose here !
					$user->$col		=	$value;			// store only if validated
				}
			}
		}
	}
	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$user, &$postdata, $list_compare_types, $reason ) {
		return array();
	}
	/**
	 * Returns the minimum field length as set
	 *
	 * @param  FieldTable  $field
	 * @return int
	 */
	function getMinLength( $field ) {
		$defaultMin					=	6;
		return $field->params->get( 'fieldMinLength', $defaultMin );
	}
}
class CBfield_select_multi_radio extends cbFieldHandler {
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$value					=	$user->get( $field->name );

		switch ( $output ) {
			case 'html':
			case 'rss':
				global $_CB_database;

				static $fieldValues			=	array();

				$cacheId					=	(int) $field->fieldid;

				if ( ! isset( $fieldValues[$cacheId] ) ) {
					$_CB_database->setQuery( "SELECT fieldtitle, fieldlabel FROM #__comprofiler_field_values WHERE fieldid = " . $cacheId . " ORDER BY ordering" );
					$fieldValues[$cacheId]	=	$_CB_database->loadObjectList();
				}

				$allValues					=	$fieldValues[$cacheId];

				if ( $value != '' ) {
					$chosen					=	$this->_explodeCBvalues( $value );
				} else {
					$chosen					=	array();
				}

				$class						=	trim( $field->params->get( 'field_display_class' ) );
				$displayStyle				=	$field->params->get( 'field_display_style' );
				$listType					=	( $displayStyle == 1 ? 'ul' : ( $displayStyle == 2 ? 'ol' : ', ' ) );

				for( $i = 0, $n = count( $chosen ); $i < $n; $i++ ) {
					if ( $allValues ) foreach ( $allValues as $allValue ) {
						if ( ( $allValue->fieldlabel != '' ) && ( $chosen[$i] == $allValue->fieldtitle ) ) {
							$chosen[$i]		=	CBTxt::T( $allValue->fieldlabel );
						} else {
							$chosen[$i]		=	CBTxt::T( $chosen[$i] );
						}
					}
				}

				return $this->formatFieldValueLayout( $this->_arrayToFormat( $field, $chosen, $output, $listType, $class ), $reason, $field, $user );

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'htmledit':
				global $_CB_database;

				static $fieldOptions		=	array();

				$cacheId					=	(int) $field->fieldid;

				if ( ! isset( $fieldOptions[$cacheId] ) ) {
					$_CB_database->setQuery( "SELECT fieldtitle AS `value`, if ( fieldlabel != '', fieldlabel, fieldtitle ) AS `text`, concat('cbf',fieldvalueid) AS id FROM #__comprofiler_field_values"		// id needed for the labels
											. "\n WHERE fieldid = " . $cacheId
											. "\n ORDER BY ordering" );
					$fieldOptions[$cacheId]	=	$_CB_database->loadObjectList();
				}

				$allValues					=	$fieldOptions[$cacheId];
/*
				if ( $reason == 'search' ) {
					array_unshift( $allValues, $this->_valueDoesntMatter( $field, $reason, ( $field->type == 'multicheckbox' ) ) );
					if ( ( $field->type == 'multicheckbox' ) && ( $value === null ) ) {
						$value	=	array( null );			// so that "None" is really not checked if not checked...
					}
				}
*/
				if ( $reason == 'search' ) {
//					$html			=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'multicheckbox', $value, '', $allValues );
					$displayType	=	$field->type;
					if ( ( $field->type == 'radio' ) && ( ( $list_compare_types == 2 ) || ( is_array( $value ) && ( count( $value ) > 1 ) ) ) ) {
						$displayType	=	'multicheckbox';
					}
					if ( in_array( $list_compare_types, array( 0, 2 ) ) && ( $displayType != 'multicheckbox' ) ) {
						array_unshift( $allValues, moscomprofilerHTML::makeOption( '', CBTxt::T( 'UE_NO_PREFERENCE', 'No preference' ) ) );
					}
					$html			=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', $displayType, $value, '', $allValues );
					$html			=	$this->_fieldSearchModeHtml( $field, $user, $html, ( ( strpos( $displayType, 'multi' ) === 0 ) && ( $field->type != 'radio' ) ? 'multiplechoice' : 'singlechoice' ), $list_compare_types );
				} else {
					$html			=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', $field->type, $value, '', $allValues );
				}

				return $html;

			case 'xml':
			case 'json':
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'php':
				if ( substr( $reason, -11 ) == ':translated' ) {
					// Translated version in case reason finishes by :translated: (will be used later):
					if ( in_array( $field->type, array( 'radio', 'select' ) ) ) {
						$chosen			=	CBTxt::T( $value );

						return $this->_formatFieldOutput( $field->name, $chosen, $output, ( $output != 'xml' ) );
					}

					// multiselect, multicheckbox:
					$chosen			=	$this->_explodeCBvalues( $value );
					for( $i = 0, $n = count( $chosen ); $i < $n; $i++ ) {
						$chosen[$i]	=	CBTxt::T( $chosen[$i] );
					}

					return $this->_arrayToFormat( $field, $chosen, $output );
				}
				// else: fall-through on purpose here (fixes bug #2960):

			case 'csv':
				if ( in_array( $field->type, array( 'radio', 'select' ) ) ) {
					return $this->_formatFieldOutput( $field->name, $value, $output, ( $output != 'xml' ) );
				}

				// multiselect, multicheckbox:
				$chosen			=	$this->_explodeCBvalues( $value );
				return $this->_arrayToFormat( $field, $chosen, $output );

			case 'csvheader':
			case 'fieldslist':
			default:
				return parent::getField( $field, $user, $output, $reason, $list_compare_types );
		}
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_database;

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		foreach ( $field->getTableColumns() as $col ) {
			$value						=	cbGetParam( $postdata, $col, null, _CB_ALLOWRAW );
//			if ( $value === null ) {
//				$value				=	array();
//			} elseif ( $field->type == 'radio' ) {
//				$value				=	array( $value );
//			}

			if ( is_array( $value ) ) {
				if ( count( $value ) > 0 ) {

					$_CB_database->setQuery( 'SELECT fieldtitle AS id FROM #__comprofiler_field_values'
											. "\n WHERE fieldid = " . (int) $field->fieldid
											. "\n ORDER BY ordering" );
					$authorizedValues	=	$_CB_database->loadResultArray();

					$okVals				=	array();
					foreach ( $value as $k => $v ) {
						// revert escaping of cbGetParam:
						$v				=	stripslashes( $v );
						// check authorized values:
						if ( in_array( $v, $authorizedValues, true ) && ! in_array( $v, $okVals, true ) ) {		// in case a value appears multiple times in a multi-field !
							$okVals[$k]	=	$v;
						}
					}
					$value				=	$this->_implodeCBvalues( $okVals );
				} else {
					$value				=	'';
				}
			} elseif ( ( $value === null ) || ( $value === '' ) ) {
				$value					=	'';
			} else {
				$value					=	stripslashes( $value );	// compensate for cbGetParam.
				$_CB_database->setQuery( 'SELECT fieldtitle AS id FROM #__comprofiler_field_values'
											. "\n WHERE fieldid = " . (int) $field->fieldid
											. "\n AND fieldtitle = " . $_CB_database->Quote( $value ) );
				$authorizedValues	=	$_CB_database->loadResultArray();
				if ( ! in_array( $value, $authorizedValues, true ) ) {
					$value			=	null;
				}
			}
			if ( $this->validate( $field, $user, $col, $value, $postdata, $reason ) ) {
				if ( isset( $user->$col ) && ( (string) $user->$col ) !== (string) $value ) {
					$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
				}
			}
			$user->$col				=	$value;
		}
	}
	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		global $_CB_database;

		$displayType						=	$field->type;
		if ( ( $field->type == 'radio' ) && ( $list_compare_types == 2 ) ) {
			$displayType	=	'multicheckbox';
		}

		$query								=	array();
		$searchMode							=	$this->_bindSearchMode( $field, $searchVals, $postdata, ( strpos( $displayType, 'multi' ) === 0 ? 'multiplechoice' : 'singlechoice' ), $list_compare_types );
		if ( $searchMode ) {
			foreach ( $field->getTableColumns() as $col ) {
				$value						=	cbGetParam( $postdata, $col );
				if ( is_array( $value ) ) {
					if ( count( $value ) > 0 ) {
						$_CB_database->setQuery( 'SELECT fieldtitle AS id FROM #__comprofiler_field_values'
												. "\n WHERE fieldid = " . (int) $field->fieldid
												. "\n ORDER BY ordering" );
						$authorizedValues	=	$_CB_database->loadResultArray();

						foreach ( $value as $k => $v ) {
							if ( ( count( $value ) == 1 ) && ( $v === '' ) ) {
								if ( $list_compare_types == 1 ) {
									$value		=	'';		// Advanced search: "None": checked: search for nothing selected
								} else {
									$value		=	null;	// Type 0 and 2 : Simple search: "Do not care" checked: do not search
								}
								break;
							}
							// revert escaping of cbGetParam:
							$v				=	stripslashes( $v );
							// check authorized values:
							if ( in_array( $v, $authorizedValues ) ) {
								$value[$k]	=	$v;
							} else {
								unset( $value[$k] );
							}
						}

					} else {
						$value				=	null;
					}
					if ( ( $value !== null ) && ( $value !== '' ) && in_array( $searchMode, array( 'is', 'isnot' ) ) ) {		// keep $value array if search is not strict
						$value				=	stripslashes( $this->_implodeCBvalues( $value ) );	// compensate for cbGetParam.
					}
				} else {
					if ( ( $value !== null ) && ( $value !== '' ) ) {
						$value					=	stripslashes( $value );	// compensate for cbGetParam.
						$_CB_database->setQuery( 'SELECT fieldtitle AS id FROM #__comprofiler_field_values'
													. "\n WHERE fieldid = " . (int) $field->fieldid
													. "\n AND fieldtitle = " . $_CB_database->Quote( $value ) );
						$authorizedValues	=	$_CB_database->loadResultArray();
						if ( ! in_array( $value, $authorizedValues ) ) {
							$value			=	null;
						}
					} else {
						if ( ( $list_compare_types == 1 ) && in_array( $searchMode, array( 'is', 'isnot' ) ) ) {
							$value			=	'';
						} else {
	//					if ( ( $field->type == 'multicheckbox' ) && ( $value === null ) ) {
							$value			=	null;				// 'none' is not checked and no other is checked: search for DON'T CARE
						}
					}
				}
				if ( $value !== null ) {
					$searchVals->$col		=	$value;
					// $this->validate( $field, $user, $col, $value, $postdata, $reason );
					$sql					=	new cbSqlQueryPart();
					$sql->tag				=	'column';
					$sql->name				=	$col;
					$sql->table				=	$field->table;
					$sql->type				=	'sql:field';
					$sql->operator			=	'=';
					$sql->value				=	$value;
					$sql->valuetype			=	'const:string';
					$sql->searchmode		=	$searchMode;
					$query[]				=	$sql;
				}
			}
		}
		return $query;
	}
}
class CBfield_checkbox extends cbFieldHandler {
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$value			=	$user->get( $field->name );

		switch ( $output ) {
			case 'html':
			case 'rss':
				if ( $value == 1 ) {
					return $this->formatFieldValueLayout( CBTxt::T( 'UE_YES', 'Yes' ), $reason, $field, $user );
				} elseif ( $value == 0 ) {
					return $this->formatFieldValueLayout( CBTxt::T( 'UE_NO', 'No' ), $reason, $field, $user );
				} else {
					return $this->formatFieldValueLayout( null, $reason, $field, $user );
				}
				break;

			case 'htmledit':
				if ( $reason == 'search' ) {
					$choices	=	array();
					$choices[]	=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'UE_NO_PREFERENCE', 'No preference' ) );
					$choices[]	=	moscomprofilerHTML::makeOption( '1', CBTxt::T( 'UE_YES', 'Yes' ) );
					$choices[]	=	moscomprofilerHTML::makeOption( '0', CBTxt::T( 'UE_NO', 'No' ) );
					$html		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', $value, '', $choices );
					$html		=	$this->_fieldSearchModeHtml( $field, $user, $html, 'singlechoice', $list_compare_types );
					return $html;
				} else {
					$checked		=	'';
					if ( $value == 1 ) {
						$checked	=	' checked="checked"';
					}
					return $this->_fieldEditToHtml( $field, $user, $reason, 'input', 'checkbox', '1', $checked );
				}
				break;

			case 'json':
				return "'" . $field->name . "' : " . (int) $value;
				break;

			case 'php':
				return array( $field->name => (int) $value );
				break;

			case 'xml':
			case 'csvheader':
			case 'fieldslist':
			case 'csv':
			default:
				return parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		foreach ( $field->getTableColumns() as $col ) {
			$value					=	stripslashes( cbGetParam( $postdata, $col ) );

			if ( $value == '' ) {
				$value				=	0;
			} elseif ( $value == '1' ) {
				$value				=	1;
			}
			$validated				=	$this->validate( $field, $user, $col, $value, $postdata, $reason );
			if ( ( $value === 0 ) || ( $value === 1 ) ) {
				if ( $validated && isset( $user->$col ) && ( ( (string) $user->$col ) !== (string) $value ) ) {
					$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
				}
			}
			$user->$col				=	$value;
		}
	}
	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		$query							=	array();
		$searchMode						=	$this->_bindSearchMode( $field, $searchVals, $postdata, 'none', $list_compare_types );
		if ( $searchMode ) {
			foreach ( $field->getTableColumns() as $col ) {
				$value						=	stripslashes( cbGetParam( $postdata, $col ) );
				if ( $value === '0' ) {
					$value				=	0;
				} elseif ( $value == '1' ) {
					$value				=	1;
				} else {
					continue;
				}
				$searchVals->$col		=	$value;
				// $this->validate( $field, $user, $col, $value, $postdata, $reason );
				$sql					=	new cbSqlQueryPart();
				$sql->tag				=	'column';
				$sql->name				=	$col;
				$sql->table				=	$field->table;
				$sql->type				=	'sql:field';
				$sql->operator			=	'=';
				$sql->value				=	$value;
				$sql->valuetype			=	'const:int';
				$sql->searchmode		=	$searchMode;
				$query[]				=	$sql;
			}
		}
		return $query;
	}
}
/**
 * Basic CB integer field extender.
 */
class CBfield_integer extends CBfield_text {

	/**
	 * formats variable array into data attribute string
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $output
	 * @param  string       $reason
	 * @param  array        $attributeArray
	 * @return null|string
	 */
	protected function getDataAttributes( $field, $user, $output, $reason, $attributeArray = array() ) {
		$min					=	(int) $field->params->get( 'integer_minimum', 0 );

		if ( $min < 0 ) {
			$attributeArray[]	=	cbValidator::getRuleHtmlAttributes( 'integer' );
		} else {
			$attributeArray[]	=	cbValidator::getRuleHtmlAttributes( 'digits' );
		}

		if ( $min ) {
			$attributeArray[]	=	cbValidator::getRuleHtmlAttributes( 'min', (int) $min );
		}

		$max					=	(int) $field->params->get( 'integer_maximum', 1000000 );

		if ( $max ) {
			$attributeArray[]	=	cbValidator::getRuleHtmlAttributes( 'max', (int) $max );
		}

		return parent::getDataAttributes( $field, $user, $output, $reason, $attributeArray );
	}

	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$value						=	$user->get( $field->name );

		switch ( $output ) {
			case 'htmledit':
				if ( $reason == 'search' ) {
					$minNam			=	$field->name . '__minval';
					$maxNam			=	$field->name . '__maxval';

					$minVal			=	$user->get( $minNam );
					$maxVal			=	$user->get( $maxNam );

					$fieldNameSave	=	$field->name;
					$field->name	=	$minNam;
					$minHtml		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $minVal, '' );
					$field->name	=	$maxNam;
					$maxHtml		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $maxVal, '' );
					$field->name	=	$fieldNameSave;
					$ret			=	$this->_fieldSearchRangeModeHtml( $field, $user, $output, $reason, $value, $minHtml, $maxHtml, $list_compare_types );

				} else {
					$ret			=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $value, $this->getDataAttributes( $field, $user, $output, $reason ) );
				}
				break;
			case 'html':
			case 'rss':
			case 'json':
			case 'php':
			case 'xml':
			case 'csvheader':
			case 'fieldslist':
			case 'csv':
			default:
				$ret				=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}
		return $ret;
	}

	/**
	 * Mutator:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		foreach ( $field->getTableColumns() as $col ) {
			$value					=	cbGetParam( $postdata, $col );

			if ( ! is_array( $value ) ) {
				$value				=	stripslashes( $value );
				$validated			=	$this->validate( $field, $user, $col, $value, $postdata, $reason );

				if ( $value !== null ) {
					$value			=	(int) $value;		// int conversion to sanitize input.

					if ( $validated && isset( $user->$col ) && ( ( (string) $user->$col ) !== (string) $value ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
					}

					$user->$col		=	$value;
				}
			}
		}
	}

	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return boolean                  True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		$validated					=	parent::validate( $field, $user, $columnName, $value, $postdata, $reason );
		if ( $validated && ( $value !== '' ) && ( $value !== null ) ) {		// empty values (e.g. non-mandatory) are treated in the parent validation.
			$validated				=	preg_match( '/^[-0-9]*$/', $value );
			if ( $validated ) {
				// check range:
				$min				=	(int) $field->params->get( 'integer_minimum', '0' );
				$max				=	(int) $field->params->get( 'integer_maximum', '1000000' );
				if ( $max < $min ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Min setting > Max setting !' ) );
					$validated		=	false;
				}
				if ( ( ( (int) $value ) < $min ) || ( ( (int) $value ) > $max ) ) {
					$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'UE_YEAR_NOT_IN_RANGE', 'Year %s is not between %s and %s' ), (int) $value, (int) $min, (int) $max ) );		// using that year string, as we don't have a general one.
					$validated		=	false;
				}
				if ( $validated ) {
					// check for forbidden values as integers:
					$forbiddenContent			=	$field->params->get( 'fieldValidateForbiddenList_' . $reason, '' );
					if ( $forbiddenContent != '' ) {
						$forbiddenContent		=	explode( ',', $forbiddenContent );
						if ( in_array( (string) ( (int) $value ), $forbiddenContent ) ) {
							$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_INPUT_VALUE_NOT_ALLOWED', 'This input value is not authorized.' ) );
							$validated			=	false;
						}
					}
				}
			} else {
				$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Not an integer' ) );
			}
		}
		return $validated;
	}
	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		$query							=	array();
		foreach ( $field->getTableColumns() as $col ) {
			$minNam						=	$col . '__minval';
			$maxNam						=	$col . '__maxval';
			$searchMode					=	$this->_bindSearchRangeMode( $field, $searchVals, $postdata, $minNam, $maxNam, $list_compare_types );
			if ( $searchMode ) {
				$minVal					=	(int) cbGetParam( $postdata, $minNam, 0 );
				$maxVal					=	(int) cbGetParam( $postdata, $maxNam, 0 );

				if ( $minVal && ( cbGetParam( $postdata, $minNam, '' ) !== '' ) ) {
					$searchVals->$minNam =	$minVal;
					$operator			=	( $searchMode == 'isnot' ? ( $minVal == $maxVal ? '<' : '<=' ) : '>=' );
					$min				=	$this->_intToSql( $field, $col, $minVal, $operator, $searchMode );
				} else {
					$min				=	null;
				}

				if ( $maxVal && ( cbGetParam( $postdata, $maxNam, '' ) !== '' ) ) {
					$searchVals->$maxNam =	$maxVal;
					$operator			=   ( $searchMode == 'isnot' ? ( $maxVal == $minVal ? '>' : '>=' ) : '<=' );
					$max				=	$this->_intToSql( $field, $col, $maxVal, $operator, $searchMode );
				} else {
					$max				=	null;
				}

				if ( $min && $max ) {
					$sql				=	new cbSqlQueryPart();
					$sql->tag			=	'column';
					$sql->name			=	$col;
					$sql->table			=	$field->table;
					$sql->type			=	'sql:operator';
					$sql->operator		=	( $searchMode == 'isnot' ? 'OR' : 'AND' );
					$sql->searchmode	=	$searchMode;

					$sql->addChildren( array( $min, $max ) );

					$query[]			=	$sql;
				} elseif ( $min ) {
					$query[]			=	$min;
				} elseif ( $max ) {
					$query[]			=	$max;
				}
			}
		}
		return $query;
	}

	/**
	 * Internal function to create an SQL query part based on a comparison operator
	 *
	 * @param  FieldTable  $field
	 * @param  string      $col
	 * @param  int         $value
	 * @param  string      $operator
	 * @param  string      $searchMode
	 * @return cbSqlQueryPart
	 */
	protected function _intToSql( &$field, $col, $value, $operator, $searchMode ) {
		$value							=	(int) $value;
		// $this->validate( $field, $user, $col, $value, $postdata, $reason );
		$sql							=	new cbSqlQueryPart();
		$sql->tag						=	'column';
		$sql->name						=	$col;
		$sql->table						=	$field->table;
		$sql->type						=	'sql:field';
		$sql->operator					=	$operator;
		$sql->value						=	$value;
		$sql->valuetype					=	'const:int';
		$sql->searchmode				=	$searchMode;
		return $sql;
	}
}

class CBfield_date extends cbFieldHandler {

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_CB_framework;

		$value								=	$user->get( $field->get( 'name' ) );
		$return								=	null;

		switch ( $output ) {
			case 'html':
			case 'rss':
				if ( ( $value != null ) && ( $value != '' ) && ( $value != '0000-00-00 00:00:00' ) && ( $value != '0000-00-00' ) ) {
					$offset					=	(int) $field->params->get( 'date_offset', 1 );

					switch ( (int) $field->params->get( 'field_display_by', 0 ) ) {
						case 1: // Age
							$dateDiff		=	$_CB_framework->getUTCDateDiff( 'now', $value );
							$age			=	null;

							if ( $dateDiff ) {
								$age		=	$dateDiff->y;

								if ( $age < 0 ) {
									$age	=	null;
								}
							}

							$return			=	$this->formatFieldValueLayout( $age, $reason, $field, $user );
							break;
						case 2: // Timeago, with Ago
							$return			=	$this->formatFieldValueLayout( cbFormatDate( $value, $offset, 'timeago' ), $reason, $field, $user, false );
							break;
						case 6: // Timeago, without Ago
							$return			=	$this->formatFieldValueLayout( cbFormatDate( $value, $offset, 'exacttimeago' ), $reason, $field, $user, false );
							break;
						case 3: // Birthdate
							$return			=	$this->formatFieldValueLayout( htmlspecialchars( cbFormatDate( $value, $offset, true, 'F d' ) ), $reason, $field, $user );
							break;
						case 4: // Date
							$return			=	$this->formatFieldValueLayout( htmlspecialchars( cbFormatDate( $value, $offset, false ) ), $reason, $field, $user );
							break;
						case 5: // Custom
							$dateFormat		=	$field->params->get( 'custom_date_format', 'Y-m-d' );
							$timeFormat		=	$field->params->get( 'custom_time_format', 'H:i:s' );

							$return			=	$this->formatFieldValueLayout( htmlspecialchars( cbFormatDate( $value, $offset, ( $field->get( 'type' ) == 'datetime' ? true : false ), $dateFormat, $timeFormat ) ), $reason, $field, $user );
							break;
						default: // Date/Datetime
							$return			=	$this->formatFieldValueLayout( htmlspecialchars( cbFormatDate( $value, $offset, ( $field->get( 'type' ) == 'datetime' ? true : false ) ) ), $reason, $field, $user );
							break;
					}
				} else {
					$return					=	$this->formatFieldValueLayout( '', $reason, $field, $user );
				}
				break;
			case 'htmledit':
				global $_CB_framework;

				$offset						=	(int) $field->params->get( 'date_offset', 1 );

				$calendars					=	new cbCalendars( $_CB_framework->getUi() );

				$translatedTitle			=	$this->getFieldTitle( $field, $user, 'html', $reason );
				$htmlDescription			=	$this->getFieldDescription( $field, $user, 'htmledit', $reason );
				$trimmedDescription			=	trim( strip_tags( $htmlDescription ) );

				$tooltip					=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, 'data-hascbtooltip="true"' ) : null );

				if ( $reason == 'search' ) {
					$minNam					=	$field->get( 'name' ) . '__minval';
					$maxNam					=	$field->get( 'name' ) . '__maxval';

					$minVal					=	$user->get( $minNam );
					$maxVal					=	$user->get( $maxNam );

					$searchBy				=	$field->params->get( 'field_search_by', 0 );

					list( $yMin, $yMax )	=	$this->_yearsRange( $field, $searchBy );

					if ( $searchBy == 1 ) {
						// Search by age range:
						$choices			=	array();

						for ( $i = $yMin ; $i <= $yMax ; $i++ ) {
							$choices[]		=	moscomprofilerHTML::makeOption( $i, $i );
						}

						if ( $minVal === null ) {
							$minVal			=	$yMin;
						}

						if ( $maxVal === null ) {
							$maxVal			=	$yMax;
						}

						$additional			=	' class="form-control"' . ( trim( $tooltip ) ? ' ' . $tooltip : null );
						$minHtml			=	moscomprofilerHTML::selectList( $choices, $minNam, $additional, 'text', 'value', $minVal, 2 );
						$maxHtml			=	moscomprofilerHTML::selectList( $choices, $maxNam, $additional, 'text', 'value', $maxVal, 2 );
					} else {
						if ( $minVal !== null ) {
							if ( $field->get( 'type' ) == 'datetime' ) {
								$minVal		=	$_CB_framework->getUTCDate( 'Y-m-d H:i:s', $minVal );
							} else {
								$minVal		=	$_CB_framework->getUTCDate( 'Y-m-d', $minVal );
							}
						}

						if ( $maxVal !== null ) {
							if ( $field->get( 'type' ) == 'datetime' ) {
								$maxVal		=	$_CB_framework->getUTCDate( 'Y-m-d H:i:s', $maxVal );
							} else {
								$maxVal		=	$_CB_framework->getUTCDate( 'Y-m-d', $maxVal );
							}
						}

						// Search by date range:
						$minHtml			=	$calendars->cbAddCalendar( $minNam, CBTxt::Th( 'UE_SEARCH_FROM', 'Between' ) . ' ' . $this->getFieldTitle( $field, $user, 'text', $reason ), false, $minVal, false, ( $field->get( 'type' ) == 'datetime' ? true : false ), $yMin, $yMax, $tooltip, $offset );
						$maxHtml			=	$calendars->cbAddCalendar( $maxNam, CBTxt::Th( 'UE_SEARCH_TO', 'and' ) . ' ' . $this->getFieldTitle( $field, $user, 'text', $reason ), false, $maxVal, false, ( $field->get( 'type' ) == 'datetime' ? true : false ), $yMin, $yMax, $tooltip, $offset );
					}

					$return					=	$this->_fieldSearchRangeModeHtml( $field, $user, $output, $reason, $value, $minHtml, $maxHtml, $list_compare_types );
				} elseif ( ( ! in_array( $field->get( 'name' ), array( 'registerDate', 'lastvisitDate', 'lastupdatedate' ) ) ) ) {
					list( $yMin, $yMax )	=	$this->_yearsRange( $field, 0 );

					$return					=	$this->formatFieldValueLayout( $calendars->cbAddCalendar( $field->get( 'name' ), $this->getFieldTitle( $field, $user, 'text', $reason ), $this->_isRequired( $field, $user, $reason ), $value, $this->_isReadOnly( $field, $user, $reason ), ( $field->get( 'type' ) == 'datetime' ? true : false ), $yMin, $yMax, $tooltip, $offset ), $reason, $field, $user )
											.	$this->_fieldIconsHtml( $field, $user, $output, $reason, null, $field->get( 'type' ), $value, 'input', null, true, $this->_isRequired( $field, $user, $reason ) && ! $this->_isReadOnly( $field, $user, $reason ) );
				}
				break;
			case 'json':
			case 'php':
			case 'xml':
			case 'csvheader':
			case 'fieldslist':
			case 'csv':
			default:
				$return						=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}

		return $return;
	}

	/**
	 * @param  FieldTable  $field
	 * @param  int         $outputMode
	 * @return array
	 */
	function _yearsRange( &$field, $outputMode ) {
		$yMin					=	$this->_yearSetting( $field->params->get( 'year_min', '-110' ), $outputMode );
		$yMax					=	$this->_yearSetting( $field->params->get( 'year_max', '+25' ), $outputMode );

		// Reverse min and max year for age display:
		if ( $outputMode == 1 ) {
			$temp				=	$yMin;
			$yMin				=	$yMax;
			$yMax				=	$temp;
		}

		// Ensure max year is always greater than minimum year:
		if ( ( ( $yMax - $yMin ) > 1000 ) || ( $yMax < $yMin ) ) {
			$yMax				=	$yMin + 1000;
		} elseif ( $yMin == $yMax ) {
			$yMax				=	$yMax + 1;
		}

		return array( $yMin, $yMax );
	}

	/**
	 * @param  string  $setParam
	 * @param  int     $outputMode
	 * @return int|null
	 */
	function _yearSetting( $setParam, $outputMode ) {
		$yearSetting			=	trim( $setParam );
		$offset					=	null;
		$fullYear				=	null;

		if ( strlen( $yearSetting ) == 0 ) {
			$offset				=	0;
		} else {
			$sign				=	$yearSetting[0];

			if ( $sign == '+' ) {
				$offset			=	(int) substr( $yearSetting, 1 );
			} elseif ( $sign == '-' ) {
				$offset			=	- (int) substr( $yearSetting, 1 );
			} else {
				$fullYear		=	(int) $yearSetting;
			}
		}

		if ( $outputMode == 1 ) {
			if ( $offset === null ) {
				$offset			=	( $fullYear - (int) cbFormatDate( 'now', false, false, 'Y' ) );
			}

			return -$offset;
		} else {
			if ( $offset !== null ) {
				$fullYear		=	( (int) cbFormatDate( 'now', false, false, 'Y' ) + $offset );
			}

			return $fullYear;
		}
	}

	/**
	 * Labeller for title:
	 * Returns a field title
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'text' or: 'html', 'htmledit', (later 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist')
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @return string
	 */
	public function getFieldTitle( &$field, &$user, $output, $reason ) {
		$title			=	'';
		$byAge			=	( ( ( $output == 'html' ) || ( $output == 'rss' ) ) && ( $field->params->get( 'field_display_by', 0 ) > 0 ) ) || ( ( $reason == 'search' ) && ( $field->params->get( 'field_search_by', 0 ) == 1 ) );

		if ( $byAge ) {
			$title		=	$field->params->get( 'duration_title' );
		}

		if ( $title != '' ) {
			if ( $output === 'text' ) {
				return strip_tags( cbReplaceVars( $title, $user ) );
			} else {
				return cbReplaceVars( $title, $user );
			}
		} else {
			return parent::getFieldTitle( $field, $user, $output, $reason );
		}
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		if ( ( ! in_array( $field->name, array( 'registerDate', 'lastvisitDate', 'lastupdatedate' ) ) ) ) {
			foreach ( $field->getTableColumns() as $col ) {
				$value				=	stripslashes( cbGetParam( $postdata, $col ) );
				$validated			=	$this->validate( $field, $user, $col, $value, $postdata, $reason );

				if ( $value !== null ) {
					if ( $validated && isset( $user->$col ) && ( ( (string) $user->$col ) !== (string) $value ) && ! ( ( ( $user->$col === '0000-00-00' ) || ( $user->$col === '0000-00-00 00:00:00' ) ) && ( $value == '' ) ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
					}

					$user->$col		=	$value;
				}
			}
		}
	}

	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return boolean                  True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		$validate						=	parent::validate( $field, $user, $columnName, $value, $postdata, $reason );

		if ( $validate && ( $value !== null ) ) {
			$year						=	substr( $value, 0, 4 );

			if ( ( $year == '' ) || ( $year == '0000' ) ) {
				if ( $this->_isRequired( $field, $user, $reason ) ) {
					$this->_setValidationError( $field, $user, $reason, cbUnHtmlspecialchars( CBTxt::T( 'UE_REQUIRED_ERROR', 'This field is required!' ) ) );

					$validate			=	false;
				}
			} else {
				// check range:
				list( $yMin, $yMax )	=	$this->_yearsRange( $field, 0 );

				if ( ( $year < $yMin ) || ( $year > $yMax ) ) {
					$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'UE_YEAR_NOT_IN_RANGE', 'Year %s is not between %s and %s' ), (int) $year, (int) $yMin, (int) $yMax ) );
					$validate			=	false;
				}
			}
		}

		return $validate;
	}

	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		global $_CB_framework;

		$searchBy										=	$field->params->get( 'field_search_by', 0 );

		list( $yMinMin, $yMaxMax )						=	$this->_yearsRange( $field, $searchBy );

		$query											=	array();

		foreach ( $field->getTableColumns() as $col ) {
			$minNam										=	$col . '__minval';
			$maxNam										=	$col . '__maxval';
			$searchMode									=	$this->_bindSearchRangeMode( $field, $searchVals, $postdata, $minNam, $maxNam, $list_compare_types );

			if ( $searchMode ) {
				if ( $searchBy == 1 ) {
					// search by years:
					if ( $field->type == 'datetime' ) {
						list( $y, $c, $d, $h, $m, $s )	=	sscanf( $_CB_framework->getUTCDate( 'Y-m-d H:i:s' ), '%d-%d-%d %d:%d:%d' );
					} else {
						list( $y, $c, $d )				=	sscanf( $_CB_framework->getUTCDate( 'Y-m-d' ), '%d-%d-%d' );
						$h								=	null;
						$m								=	null;
						$s								=	null;
					}

					$minValIn							=	(int) cbGetParam( $postdata, $minNam, 0 );
					$maxValIn							=	(int) cbGetParam( $postdata, $maxNam, 0 );

					if ( ( $maxValIn && ( $maxValIn <= $yMaxMax ) ) && ( $minValIn && ( $minValIn > $yMinMin ) ) ) {
						$yMax							=	( $y - $minValIn );

						if ( $field->type == 'datetime' ) {
							$maxVal						=	sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $yMax, $c, $d, $h, $m, $s );
						} else {
							$maxVal						=	sprintf( '%04d-%02d-%02d', $yMax, $c, $d );
						}
					} else {
						$maxVal							=	null;
					}

					if ( ( $minValIn && ( $minValIn >= $yMinMin ) ) && ( $maxValIn && ( $maxValIn < $yMaxMax ) ) ) {
						// Pad by 1 year to account for same year searches:
						$yMin							=	( $y - $maxValIn - 1 );

						if ( $field->type == 'datetime' ) {
							$minVal						=	sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $yMin, $c, $d, $h, $m, $s );
						} else {
							$minVal						=	sprintf( '%04d-%02d-%02d', $yMin, $c, $d );
						}
					} else {
						$minVal							=	null;
					}
				} else {
					$minVal								=	stripslashes( cbGetParam( $postdata, $minNam ) );
					$maxVal								=	stripslashes( cbGetParam( $postdata, $maxNam ) );
					$minValIn							=	$minVal;
					$maxValIn							=	$maxVal;
				}

				if ( $field->type == 'datetime' ) {
					$minSearch							=	( $minVal && ( $minVal !== '0000-00-00 00:00:00' ) );
					$maxSearch							=	( $maxVal && ( $maxVal !== '0000-00-00 00:00:00' ) );
				} else {
					$minSearch							=	( $minVal && ( $minVal !== '0000-00-00' ) );
					$maxSearch							=	( $maxVal && ( $maxVal !== '0000-00-00' ) );
				}

				$forceMin								=	( ( ! $minSearch ) && $maxSearch && ( ! in_array( $field->name, array( 'lastupdatedate', 'lastvisitDate' ) ) ) );

				if ( $minSearch || $forceMin ) {
					$min								=	new cbSqlQueryPart();
					$min->tag							=	'column';
					$min->name							=	$col;
					$min->table							=	$field->table;
					$min->type							=	'sql:field';
					$min->operator						=	( ! $forceMin ? ( $searchMode == 'isnot' ? '<=' : '>=' ) : '>' );

					if ( $field->type == 'datetime' ) {
						$min->value						=	( ! $forceMin ? $minVal : '0000-00-00 00:00:00' );
						$min->valuetype					=	'const:datetime';
					} else {
						$min->value						=	( ! $forceMin ? $minVal : '0000-00-00' );
						$min->valuetype					=	'const:date';
					}

					$min->searchmode					=	$searchMode;

					if ( ! $forceMin ) {
						if ( ( ! $maxVal ) && $maxValIn ) {
							$searchVals->$maxNam		=	$maxValIn;
						}

						$searchVals->$minNam			=	$minValIn;
					}
				}

				if ( $maxSearch ) {
					$max								=	new cbSqlQueryPart();
					$max->tag							=	'column';
					$max->name							=	$col;
					$max->table							=	$field->table;
					$max->type							=	'sql:field';
					$max->operator						=	( $searchMode == 'isnot' ? '>=' : '<=' );
					$max->value							=	$maxVal;

					if ( $field->type == 'datetime' ) {
						$max->valuetype					=	'const:datetime';
					} else {
						$max->valuetype					=	'const:date';
					}

					$max->searchmode					=	$searchMode;

					if ( ( ! $minVal ) && $minValIn ) {
						$searchVals->$minNam			=	$minValIn;
					}

					$searchVals->$maxNam				=	$maxValIn;
				}

				if ( isset( $min ) && isset( $max ) ) {
					$sql								=	new cbSqlQueryPart();
					$sql->tag							=	'column';
					$sql->name							=	$col;
					$sql->table							=	$field->table;
					$sql->type							=	'sql:operator';
					$sql->operator						=	( $searchMode == 'isnot' ? 'OR' : 'AND' );
					$sql->searchmode					=	$searchMode;

					$sql->addChildren( array( $min, $max ) );

					$query[]							=	$sql;
				} elseif ( isset( $min ) ) {
					$query[]							=	$min;
				} elseif ( isset( $max ) ) {
					$query[]							=	$max;
				}
			}
		}

		return $query;
	}
}

class CBfield_editorta extends cbFieldHandler {
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_CB_framework;

		$value							=	$user->get( implode( '', $field->getTableColumns() ) );

		switch ( $output ) {
			case 'html':
			case 'rss':
				$html					=	$this->formatFieldValueLayout( Get::clean( $value, GetterInterface::HTML ), $reason, $field, $user, false );
				unset( $cbFields );
				break;
			case 'htmledit':
				if ( $reason == 'search' ) {
					$rows				=	$field->rows;
					if ( $rows > 5 ) {
						$field->rows	=	5;
					}
					$html				=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'textarea', $value, '' );
					$field->rows		=	$rows;
					$html				=	$this->_fieldSearchModeHtml( $field, $user, $html, 'text', $list_compare_types );
				} elseif ( ! ( $this->_isReadOnly( $field, $user, $reason ) ) ) {
					$value				=	Get::clean( $value, GetterInterface::HTML );
					unset( $cbFields );

					$translatedTitle	=	$this->getFieldTitle( $field, $user, 'html', $reason );
					$htmlDescription	=	$this->getFieldDescription( $field, $user, 'htmledit', $reason );
					$trimmedDescription	=	trim( strip_tags( $htmlDescription ) );

					$editor				=	$_CB_framework->displayCmsEditor( $field->name, $value, 600, 350, $field->cols, $field->rows );

					$html				=	$this->formatFieldValueLayout( ( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, $editor, null, 'style="display:block;"' ) : $editor ), $reason, $field, $user, false )
										.	$this->_fieldIconsHtml( $field, $user, $output, $reason, null, $field->type, $value, 'input', null, true, ( $this->_isRequired( $field, $user, $reason ) && ( ! $this->_isReadOnly( $field, $user, $reason ) ) ) );
					$this->_addSaveAndValidateCode( $field, $user, $reason );
				} else {
					$html				=	null;
				}
				break;

			case 'json':
			case 'php':
			case 'xml':
			case 'csvheader':
			case 'fieldslist':
			case 'csv':
			default:
				return parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}
		return $html;
	}

	/**
	 * Adds validation and saving Javascript
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason
	 * @return void
	 */
	function _addSaveAndValidateCode( $field, $user, $reason ) {
		global $_CB_framework;

		$jsSaveCode		=	$_CB_framework->saveCmsEditorJS( $field->name );
		$js				=	null;

		if ( $jsSaveCode ) {
			$js			.=	"$( '#" . addslashes( $field->name ) . "' ).closest( 'form' ).submit( function() { " . $jsSaveCode . " return true; } );";
		}

		if ( $this->_isRequired( $field, $user, $reason ) ) {
			$js			.=	"$( '#" . addslashes( $field->name ) . "' ).addClass( 'required' );";
		}

		if ( $js ) {
			$_CB_framework->outputCbJQuery( $js );
		}
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		foreach ( $field->getTableColumns() as $col ) {
			$value						=	stripslashes( cbGetParam( $postdata, $col, '', _CB_ALLOWRAW ) );
			if ( $value !== null ) {
				$value					=	Get::clean( $value, GetterInterface::HTML );
			}
			$validated					=	$this->validate( $field, $user, $col, $value, $postdata, $reason );
			if ( $value !== null ) {
				if ( $validated && isset( $user->$col ) && ( ( (string) $user->$col ) !== (string) $value ) ) {
					$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
				}
				$user->$col				=	$value;
			}
		}
	}
}
class CBfield_email extends CBfield_text {

	/**
	 * formats variable array into data attribute string
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $output
	 * @param  string       $reason
	 * @param  array        $attributeArray
	 * @return null|string
	 */
	protected function getDataAttributes( $field, $user, $output, $reason, $attributeArray = array() ) {
		$attributeArray[]		=	cbValidator::getRuleHtmlAttributes( 'email' );

		if ( $field->params->get( 'field_check_email', 0 ) && ( ! isset( $field->_identicalTo ) ) ) {
			$attributeArray[]	=	cbValidator::getRuleHtmlAttributes( 'cbfield', array( 'user' => (int) $user->id, 'field' => htmlspecialchars( $field->name ), 'reason' => htmlspecialchars( $reason ) ) );
		}

		return parent::getDataAttributes( $field, $user, $output, $reason, $attributeArray );
	}

	/**
	 * Returns a PASSWORD field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output      'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting  'table', 'td', 'span', 'div', 'none'
	 * @param  string      $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getFieldRow( &$field, &$user, $output, $formatting, $reason, $list_compare_types ) {
		global $_CB_OneTwoRowsStyleToggle;

		$results								=	null;

		if ( $output == 'htmledit' ) {
			if ( ( $reason != 'search' ) && $field->params->get( 'fieldVerifyInput', 0 ) ) {
				$verifyField					=	new FieldTable( $field->getDbo() );

				foreach ( array_keys( get_object_vars( $verifyField ) ) as $k ) {
					$verifyField->$k			=	$field->$k;
				}

				$verifyName						=	$field->name . '__verify';
				$verifyField->name				=	$verifyName;
				$verifyField->fieldid			=	$field->fieldid . '__verify';

				// cbReplaceVars to be done only once later:
				$titleOfVerifyField			=	$field->params->get( 'verifyEmailTitle' );
				if ( $titleOfVerifyField ) {
					$verifyField->title		=	CBTxt::Th( $titleOfVerifyField, null, array( '%s' => CBTxt::T( $field->title ) ) );
				} else {
					$verifyField->title		=	CBTxt::Th( '_UE_VERIFY_SOMETHING', 'Verify %s', array( '%s' => CBTxt::T( $field->title ) ) );
				}

				$verifyField->_identicalTo		=	$field->name;

				$toggleState					=	$_CB_OneTwoRowsStyleToggle;

				$results						=	parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );

				$_CB_OneTwoRowsStyleToggle		=	$toggleState;

				$user->set( $verifyName, $user->get( $field->name ) );

				$results						.=	parent::getFieldRow( $verifyField, $user, $output, $formatting, $reason, $list_compare_types );

				unset( $verifyField );
				unset( $user->$verifyName );
			} else {
				$results						=	parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );
			}
		} else {
			$results							=	parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );
		}
		return $results;
	}
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $ueConfig;

		$value								=	$user->get( $field->name );

		switch ( $output ) {
			case 'html':
			case 'rss':
				$useLayout						=	true;

				if ( $field->type == 'primaryemailaddress' ) {
					if ( isset( $field->_imgMode ) ) {
						$imgMode				=	(int) $field->get( '_imgMode' );
						$useLayout				=	false; // When using override we want to avoid layout usage
					} else {
						$imgMode				=	(int) $field->params->get( ( $reason == 'list' ? 'displayModeList' : 'displayMode' ), 0 );
					}

					if ( ( $ueConfig['allow_email_display'] == 3 ) || ( $imgMode != 0 ) ) {
						$oValueText				=	CBTxt::T( 'UE_SENDEMAIL', 'Send Email' );
					} else {
						$oValueText				=	htmlspecialchars( $value );
					}

					$emailIMG					=	'<span class="fa fa-envelope" title="' . htmlspecialchars( CBTxt::T( 'UE_SENDEMAIL', 'Send Email' ) ) . '"></span>';

					switch ( $imgMode ) {
						case 1:
							$useLayout			=	false; // We don't want to use layout for icon only display as we use it externally
							$linkItemImg		=	$emailIMG;
							$linkItemSep		=	null;
							$linkItemTxt		=	null;
							break;
						case 2:
							$linkItemImg		=	$emailIMG;
							$linkItemSep		=	' ';
							$linkItemTxt		=	$oValueText;
							break;
						case 0:
						default:
							$linkItemImg		=	null;
							$linkItemSep		=	null;
							$linkItemTxt		=	$oValueText;
							break;
					}
					$oReturn					=	'';
					//if no email or 4 (do not display email) then return empty string
					if ( ( $value == null ) || ( $ueConfig['allow_email_display'] == 4 ) || ( ( $imgMode != 0 ) && ( $ueConfig['allow_email_display'] == 1 ) ) ) {
						// $oReturn				=	'';
					} else {
						switch ( $ueConfig['allow_email_display'] ) {
							case 1: //display email only
								$oReturn		=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $value ), 0 );
								break;
							case 2: //mailTo link
								// cloacking doesn't cloack the text of the hyperlink, if that text does contain email addresses		//TODO: fix it.
								if ( ! $linkItemImg && $linkItemTxt == htmlspecialchars( $value ) ) {
									$oReturn	=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $value ), 1, '', 0 );
								} elseif ( $linkItemImg && $linkItemTxt != htmlspecialchars( $value ) ) {
									$oReturn	=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $value ), 1, $linkItemImg . $linkItemSep . $linkItemTxt, 0 );
								} elseif ( $linkItemImg && $linkItemTxt == htmlspecialchars( $value ) ) {
									$oReturn 	=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $value ), 1, $linkItemImg, 0 ) . $linkItemSep;
									$oReturn	.=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $value ), 1, '', 0 );
								} elseif ( ! $linkItemImg && $linkItemTxt != htmlspecialchars( $value ) ) {
									$oReturn	=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $value ), 1, $linkItemTxt, 0 );
								}
								break;
							case 3: //email Form (with cloacked email address if visible)
								$oReturn		=	"<a href=\""
												.	cbSef("index.php?option=com_comprofiler&amp;view=emailuser&amp;uid=" . $user->id . getCBprofileItemid(true))
												.	"\" title=\"" . CBTxt::T( 'UE_MENU_SENDUSEREMAIL_DESC', 'Send an Email to this user' ) . "\">" . $linkItemImg . $linkItemSep;
								if ( $linkItemTxt && ( $linkItemTxt != CBTxt::T( 'UE_SENDEMAIL', 'Send Email' ) ) ) {
									$oReturn	.=	moscomprofilerHTML::emailCloaking( $linkItemTxt, 0 );
								} else {
									$oReturn	.=	$linkItemTxt;
								}
								$oReturn		.=	"</a>";
								break;
						}
					}

				} else {

					// emailaddress:
					if ( $value == null ) {
						$oReturn				=	'';
					} else {
						if ( $ueConfig['allow_email'] == 1 ) {
							$oReturn			=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $value ), 1, "", 0 );
						} else {
							$oReturn			=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $value ), 0 );
						}
					}

				}

				if ( $useLayout ) {
					$oReturn					=	$this->formatFieldValueLayout( $oReturn, $reason, $field, $user );
				}
				break;

			case 'htmledit':
				$oReturn						=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $value, ( $reason != 'search' ? $this->getDataAttributes( $field, $user, $output, $reason ) : null ) );

				if ( $reason == 'search' ) {
					$oReturn					=	$this->_fieldSearchModeHtml( $field, $user, $oReturn, 'text', $list_compare_types );
				}
				break;

			case 'json':
			case 'php':
			case 'xml':
			case 'csvheader':
			case 'fieldslist':
			case 'csv':
			default:
				$oReturn				=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}
		return $oReturn;
	}
	/**
	 * Direct access to field for custom operations, like for Ajax
	 *
	 * WARNING: direct unchecked access, except if $user is set, then check
	 * that the logged-in user has rights to edit that $user.
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  array       $postdata
	 * @param  string      $reason     'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string                  Expected output.
	 */
	public function fieldClass( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_CB_database, $ueConfig, $_GET;

		parent::fieldClass( $field, $user, $postdata, $reason ); // Performs spoof check

		$function										=	cbGetParam( $_GET, 'function', '' );
		$valid											=	true;
		$message										=	null;

		if ( ( $function == 'checkvalue' ) || ( $function == 'testexists' ) ) {
			$emailChecker								=	$field->params->get( 'field_check_email', 0 );

			if ( $emailChecker && ( ( $reason == 'edit' ) || ( $reason == 'register' ) ) ) {
				$email									=	stripslashes( cbGetParam( $postdata, 'value', '' ) );
				$emailConfirmation					=	( ( $field->name == 'email' ) && $ueConfig['reg_confirmation'] );

				foreach ( $field->getTableColumns() as $col ) {
					if ( ( ! $user ) || ( strtolower( trim( $email ) ) != strtolower( trim( $user->$col ) ) ) ) {
						if ( ! $this->validate( $field, $user, $col, $email, $postdata, $reason ) ) {
							global $_PLUGINS;

							$valid						=	false;
							$message					=	$_PLUGINS->getErrorMSG( '<br />' );
						} else {
							// Advanced:
							if ( $emailChecker == 2 ) {
								$query					=	'SELECT COUNT(*)'
														.	"\n FROM " . $_CB_database->NameQuote( $field->table );
								if ( $_CB_database->isDbCollationCaseInsensitive() ) {
									$query				.=	"\n WHERE " . $_CB_database->NameQuote( $col ) . " = " . $_CB_database->Quote( trim( $email ) );
								} else {
									$query				.=	"\n WHERE LOWER( " . $_CB_database->NameQuote( $col ) . " ) = " . $_CB_database->Quote( strtolower( trim( $email ) ) );
								}
								$_CB_database->setQuery( $query );
								$exists					=	$_CB_database->loadResult();

								if ( $function == 'testexists' ) {
									if ( $exists ) {
										$message		=	CBTxt::Th( 'UE_EMAIL_EXISTS_ON_SITE', "The email '[email]' exists on this site.", array( '[email]' =>  htmlspecialchars( $email ) ) );
									} else {
										$valid			=	false;
										$message		=	CBTxt::Th( 'UE_EMAIL_DOES_NOT_EXISTS_ON_SITE', "The email '[email]' does not exist on this site.", array( '[email]' =>  htmlspecialchars( $email ) ) );
									}
								} else {
									if ( $exists ) {
										$valid			=	false;
										$message		=	CBTxt::Th( 'UE_EMAIL_NOT_AVAILABLE', "The email '[email]' is already in use.", array( '[email]' =>  htmlspecialchars( $email ) ) );
									} else {
										$message		=	CBTxt::Th( 'UE_EMAIL_AVAILABLE', "The email '[email]' is available.", array( '[email]' =>  htmlspecialchars( $email ) ) );
									}
								}
							}

							// Simple:
							if ( ( $function != 'testexists' ) && $valid ) {
								$checkResult			=	cbCheckMail( $_CB_framework->getCfg( 'mailfrom' ), $email );

								switch ( $checkResult ) {
									case -2: // Wrong Format
										$valid			=	false;
										$message		=	CBTxt::Th( 'UE_EMAIL_NOVALID', 'This is not a valid email address.', array( '[email]' =>  htmlspecialchars( $email ) ) );
										break;
									case -1: // Couldn't Check
										break;
									case 0: // Invalid
										$valid			=	false;

										if ( $emailConfirmation ) {
											$message	=	CBTxt::Th( 'UE_EMAIL_INCORRECT_CHECK_NEEDED', 'This address does not accept email: Needed for confirmation.', array( '[email]' =>  htmlspecialchars( $email ) ) );
										} else {
											$message	=	CBTxt::Th( 'UE_EMAIL_INCORRECT_CHECK', 'This email does not accept email: Please check.', array( '[email]' =>  htmlspecialchars( $email ) ) );
										}
										break;
								}
							}
						}
					}
				}
			}
		}

		return json_encode( array( 'valid' => $valid, 'message' => $message ) );
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		foreach ( $field->getTableColumns() as $col ) {
			$value					=	stripslashes( cbGetParam( $postdata, $col ) );
			$valueVerify			=	stripslashes( cbGetParam( $postdata, $col . '__verify' ) );

			if ( $value !== null ) {
				$value				=	str_replace( array( 'mailto:', 'http://', 'https://' ), '', $value );
			}

			if ( $valueVerify !== null ) {
				$valueVerify		=	str_replace( array( 'mailto:', 'http://', 'https://' ), '', $valueVerify );
			}

			$validated				=	$this->validate( $field, $user, $col, $value, $postdata, $reason );

			if ( $value !== null ) {
				if ( $validated && isset( $user->$col ) ) {
					if ( ( $reason != 'search' ) && $field->params->get( 'fieldVerifyInput', 0 ) && ( $value != $valueVerify ) ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Email and verification do not match, please try again.' ) );
					} elseif ( ( (string) $user->$col ) !== (string) $value ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
					}
				}

				$user->$col			=	$value;
			}
		}
	}
	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return boolean                            True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		$validate	=	parent::validate( $field, $user, $columnName, $value, $postdata, $reason );
		if ( $validate && ( $value != null ) ) {
			if ( ! cbIsValidEmail( $value ) ) {
				$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'UE_EMAIL_NOVALID', 'This is not a valid email address.' ), htmlspecialchars( $value ) ) );
				$validate				=	false;
			}
		}
		return $validate;
	}
}
class CBfield_webaddress extends CBfield_text {

	/**
	 * formats variable array into data attribute string
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $output
	 * @param  string       $reason
	 * @param  array        $attributeArray
	 * @return null|string
	 */
	protected function getDataAttributes( $field, $user, $output, $reason, $attributeArray = array() ) {
		$attributeArray[]	=	cbValidator::getRuleHtmlAttributes( 'cburl' );

		return parent::getDataAttributes( $field, $user, $output, $reason, $attributeArray );
	}

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $ueConfig;

		$value						=	$user->get( $field->name );

		switch ( $output ) {
			case 'html':
			case 'rss':
				if ( $value == null ) {
					return $this->formatFieldValueLayout( '', $reason, $field, $user );
				} elseif ( $ueConfig['allow_website'] == 1 ) {
					$oReturn		=	$this->_explodeCBvalues( $value );
					if ( count( $oReturn ) < 2) {
						$oReturn[1]	=	$oReturn[0];
					}
					return $this->formatFieldValueLayout( '<a href="http://' . htmlspecialchars( $oReturn[0] ) . '" target="_blank"' . ( (int) $field->params->get( 'webaddress_nofollow', 1 ) ? ' rel="nofollow"' : null ) . '>' . htmlspecialchars( $oReturn[1] ) . '</a>', $reason, $field, $user );
				} else {
					return $this->formatFieldValueLayout( htmlspecialchars( $value ), $reason, $field, $user );
				}
				break;

			case 'htmledit':
				if ( $field->params->get( 'webaddresstypes', 0 ) != 2 ) {
					$oReturn			=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $value, ( $reason != 'search' ? $this->getDataAttributes( $field, $user, $output, $reason ) : null ) );
				} else {
					$oValuesArr			=	$this->_explodeCBvalues( $value );

					if ( count( $oValuesArr ) < 2 ) {
						$oValuesArr[1]	=	'';
					}

					$oReturn			=	'<div class="form-group cb_form_line clearfix">'
										.		'<label for="' . htmlspecialchars( $field->name ) . '" class="col-sm-3 control-label">' . CBTxt::Th( 'UE_WEBURL', 'Address of Site' ) . '</label>'
										.		'<div class="cb_field col-sm-9">'
										.			$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $oValuesArr[0], ( $reason != 'search' ? $this->getDataAttributes( $field, $user, $output, $reason ) : null ) )
										.		'</div>'
										.	'</div>';

					$saveFieldName		=	$field->name;
					$saveFieldTitle		=	$field->title;
					$field->name		=	$saveFieldName . 'Text';
					$field->title		=	$field->title . ': ' . CBTxt::Th( 'UE_WEBTEXT', 'Name of Site');

					$oReturn			.=	'<div class="form-group cb_form_line clearfix">'
										.		'<label for="' . htmlspecialchars( $field->name ) . '" class="col-sm-3 control-label">' . CBTxt::Th( 'UE_WEBTEXT', 'Name of Site' ) . '</label>'
										.		'<div class="cb_field col-sm-9">'
										.			$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $oValuesArr[1], '' )
										.		'</div>'
										.	'</div>';

					$field->name		=	$saveFieldName;
					$field->title		=	$saveFieldTitle;
				}

				if ( $reason == 'search' ) {
					$oReturn			=	$this->_fieldSearchModeHtml( $field, $user, $oReturn, 'text', $list_compare_types );
				}
				return $oReturn;
				break;

			case 'json':
			case 'php':
			case 'xml':
			case 'csvheader':
			case 'fieldslist':
			case 'csv':
			default:
				return parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		foreach ( $field->getTableColumns() as $col ) {
			$value						=	stripslashes( cbGetParam( $postdata, $col, '' ) );
			$valueText					=	stripslashes( cbGetParam( $postdata, $col . 'Text', '' ) );

			if ( $value !== null ) {
				$value					=	str_replace( array( 'mailto:', 'http://', 'https://' ), '', $value );

				if ( $valueText ) {
					$oValuesArr			=	array();
					$oValuesArr[0]		=	$value;
					$oValuesArr[1]		=	str_replace( array( 'mailto:', 'http://', 'https://' ),'', $valueText );
					$value				=	$this->_implodeCBvalues( $oValuesArr );
				}
			}
			$validated					=	$this->validate( $field, $user, $col, $value, $postdata, $reason );
			if ( $value !== null ) {
				if ( $validated && isset( $user->$col ) && ( ( (string) $user->$col ) !== (string) $value ) ) {
					$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
				}
				$user->$col				=	$value;
			}
		}
	}
}
class CBfield_pm extends cbFieldHandler {

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_CB_framework, $_CB_PMS;

		$oReturn								=	null;

		if ( ! $_CB_PMS ) {
			return $oReturn;
		}

		$pmLinks								=	$_CB_PMS->getPMSlinks( $user->id, $_CB_framework->myId(), null, null, 1 ) ;

		if ( count( $pmLinks ) > 0 ) {
			switch ( $output ) {
				case 'html':
				case 'rss':
					if ( isset( $field->_imgMode ) ) {
						$imgMode				=	(int) $field->get( '_imgMode' );
						$useLayout				=	false; // When using override we want to avoid layout usage
					} else {
						$imgMode				=	(int) $field->params->get( ( $reason == 'list' ? 'displayModeList' : 'displayMode' ), 0 );
						$useLayout				=	true;
					}

					$pmIMG						=	'<span class="fa fa-comment" title="' . htmlspecialchars( CBTxt::T( '_UE_PM_USER', 'Send Private Message' ) ) . '"></span>';

					foreach ( $pmLinks as $pmLink ) {
					 	if ( is_array( $pmLink ) ) {
							switch ( $imgMode ) {
								default:
								case 0:
									$linkItem	=	$pmLink['caption'];		// Already translated in PMS plugin
									break;
								case 1:
									$useLayout	=	false; // We don't want to use layout for icon only display as we use it externally
									$linkItem	=	$pmIMG;
									break;
								case 2:
									$linkItem	=	$pmIMG . ' ' . $pmLink['caption'];
									break;
							}

							$oReturn			.=	'<a href="' . cbSef( $pmLink['url'] ) . '" title="' . htmlspecialchars( $pmLink['tooltip'] ) . '">' . $linkItem . '</a>';
					 	}
					}

					if ( $useLayout ) {
						$oReturn				=	$this->formatFieldValueLayout( $oReturn, $reason, $field, $user );
					}
					break;
				case 'htmledit':
					$oReturn					=	null;
					break;
				case 'json':
				case 'php':
				case 'xml':
				case 'csvheader':
				case 'fieldslist':
				case 'csv':
				default:
					$retArray					=	array();

					foreach ( $pmLinks as $pmLink ) {
					 	if ( is_array( $pmLink ) ) {
							$title				=	cbReplaceVars( $pmLink['caption'], $user );
							$url				=	cbSef( $pmLink['url'] );
							$description		=	cbReplaceVars( $pmLink['tooltip'], $user );

	 						$retArray[]			=	array( 'title' => $title, 'url' => $url, 'tooltip' => $description );
					 	}
					}

					$oReturn					=	$this->_linksArrayToFormat( $field, $retArray, $output );
					break;
			}
		}

		return $oReturn;
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );
		// on purpose don't log field update
		// nothing to do, PM fields don't save :-)
	}

	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$user, &$postdata, $list_compare_types, $reason ) {
		return array();
	}
}
/**
 * Avatar
 */
class CBfield_image extends cbFieldHandler {

	/**
	 * @param  FieldTable  $field
	 * @param  string      $name
	 * @param  null        $default
	 * @return null|string
	 */
	function _getImageFieldParam( &$field, $name, $default = null ) {
		global $ueConfig;

		$fieldDefault				=	'';

		if ( $field->get( 'name' ) == 'canvas' ) {
			switch ( $name ) {
				case 'avatarHeight':
					$fieldDefault	=	640;
					break;
				case 'avatarWidth':
					$fieldDefault	=	1280;
					break;
				case 'thumbHeight':
					$fieldDefault	=	320;
					break;
				case 'thumbWidth':
					$fieldDefault	=	640;
					break;
			}
		}

		$paramValue					=	$field->params->get( $name, $fieldDefault );

		if ( $paramValue == '' ) {
			if ( isset( $ueConfig[$name] ) ) {
				$paramValue			=	$ueConfig[$name];
			} else {
				$paramValue			=	$default;
			}
		}

		return $paramValue;
	}

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_CB_framework;

		switch ( $output ) {
			case 'html':
			case 'rss':
				$thumbnail			=	$field->get( '_imageThumbnail', ( $reason != 'profile' ) );
				$oReturn			=	$this->_avatarHtml( $field, $user, $reason, $thumbnail, 2 );

				$name				=	$field->name;
				$nameapproved		=	$field->name . 'approved';
				//Application::MyUser()->isSuperAdmin()
				if ( ( $reason == 'profile' ) && ( $user->$name != '' ) && ( $user->$nameapproved == 0 ) && Application::MyUser()->isModeratorFor( Application::User( (int) $user->id ) ) && ( ! $field->get( '_hideApproval', 0 ) ) ) {
					$oReturn		=	'<span>'
									.		$oReturn . ' ' . $this->_avatarHtml( $field, $user, $reason, false, 10 )
									.		'<div class="cbImagePendingApproval">'
									.			'<input type="button" class="btn btn-success cbImagePendingApprovalAccept" value="' . htmlspecialchars( CBTxt::Th( 'UE_APPROVE', 'Approve' ) ) . '" onclick="location.href=\'' . $_CB_framework->viewUrl( 'approveimage', true, array( 'flag' => 1, 'images[' . (int) $user->id . '][]' => $name ) ) . '\';" />'
									.			' <input type="button" class="btn btn-danger cbImagePendingApprovalReject" value="' . htmlspecialchars( CBTxt::Th( 'UE_REJECT', 'Reject' ) ) . '" onclick="location.href=\'' . $_CB_framework->viewUrl( 'approveimage', true, array( 'flag' => 0, 'images[' . (int) $user->id . '][]' => $name ) ) . '\';" />'
									.		'</div>'
									.	'</span>';
				}
				$oReturn			=	$this->formatFieldValueLayout( $oReturn, $reason, $field, $user );
				break;

			case 'htmledit':
				if ( $reason == 'search' ) {
					$choices		=	array();
					$choices[]		=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'UE_NO_PREFERENCE', 'No preference' ) );
					$choices[]		=	moscomprofilerHTML::makeOption( '1', CBTxt::T( 'UE_HAS_PROFILE_IMAGE', 'Has a profile image' ) );
					$choices[]		=	moscomprofilerHTML::makeOption( '0', CBTxt::T( 'UE_HAS_NO_PROFILE_IMAGE', 'Has no profile image' ) );
					$col			=	$field->name;
					$value			=	$user->$col;
					$html			=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', $value, '', $choices );
					$html			=	$this->_fieldSearchModeHtml( $field, $user, $html, 'singlechoice', $list_compare_types );		//TBD: Has avatarapproved...
				} else {
					$html			=	$this->formatFieldValueLayout( $this->_htmlEditForm( $field, $user, $reason ), $reason, $field, $user );
				}
				return $html;
			case 'json':
			case 'php':
			case 'xml':
			case 'csvheader':
			case 'fieldslist':
			case 'csv':
			default:
				$thumbnail			=	$field->get( '_imageThumbnail', ( $reason != 'profile' ) );
				$imgUrl				=	$this->_avatarLivePath( $field, $user, $thumbnail );
				$oReturn			=	$this->_formatFieldOutput( $field->name, $imgUrl, $output );
				break;
		}

		return $oReturn;
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_FILES;

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		$col										=	$field->name;
		$colapproved								=	$col . 'approved';
		$col_choice									=	$col . '__choice';
		$col_file									=	$col . '__file';
		$col_gallery								=	$col . '__gallery';

		$choice										=	stripslashes( cbGetParam( $postdata, $col_choice ) );

		switch ( $choice ) {
			case 'upload':
				$value								=	( isset( $_FILES[$col_file] ) ? $_FILES[$col_file] : null );

				// Image is uploaded in the commit, but lets validate it here as well:
				$this->validate( $field, $user, $choice, $value, $postdata, $reason );
				break;
			case 'gallery':
				$newAvatar							=	stripslashes( cbGetParam( $postdata, $col_gallery ) );

				if ( $this->validate( $field, $user, $choice, $newAvatar, $postdata, $reason ) ) {
					$value							=	'gallery/' . $newAvatar;

					if ( isset( $user->$col ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
					}

					deleteAvatar( $user->$col ); // delete old avatar

					$user->$col							=	$value;
					$user->$colapproved					=	1;
				}
				break;
			case 'delete':
				if ( $user->id && ( $user->$col != null ) && ( $user->$col != '' ) ) {
					global $_CB_database;

					if ( isset( $user->$col ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, '' );
					}

					deleteAvatar( $user->$col ); // delete old avatar

					$user->$col						=	null; // this will not update, so we do query below:
					$user->$colapproved				=	1;

					$query							=	'UPDATE ' . $_CB_database->NameQuote( $field->table )
													.	"\n SET " . $_CB_database->NameQuote( $col )			  . ' = NULL'
													.	', '	  . $_CB_database->NameQuote( $col . 'approved' ) . ' = 1'
													.	', '	  . $_CB_database->NameQuote( 'lastupdatedate' )  . ' = ' . $_CB_database->Quote( $_CB_framework->dateDbOfNow() )
													.	"\n WHERE " . $_CB_database->NameQuote( 'id' )			  . ' = ' . (int) $user->id;
					$_CB_database->setQuery( $query );
					$_CB_database->query();
				}
				break;
			case 'approve':
				if ( isset( $user->$col ) && ( $_CB_framework->getUi() == 2 ) && $user->id && ( $user->$col != null ) && ( $user->$colapproved == 0 ) ) {
					$this->_logFieldUpdate( $field, $user, $reason, '', $user->$col );	// here we are missing the old value, so can't give it...

					$user->$colapproved				=	1;
					$user->lastupdatedate			=	$_CB_framework->dateDbOfNow();

					$cbNotification					=	new cbNotification();
					$cbNotification->sendFromSystem( $user, CBTxt::T( 'UE_IMAGEAPPROVED_SUB', 'Image Approved' ), CBTxt::T( 'UE_IMAGEAPPROVED_MSG', 'Your image has been approved by a moderator.' ) );
				}
				break;
			case '':
			default:
				$this->validate( $field, $user, $choice, $newAvatar, $postdata, $reason );
				break;
		}
	}

	/**
	 * Mutator:
	 * Prepares field data commit
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function commitFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $ueConfig, $_PLUGINS, $_FILES;

		$col										=	$field->name;
		$colapproved								=	$col . 'approved';
		$col_choice									=	$col . '__choice';
		$col_file									=	$col . '__file';

		$choice										=	stripslashes( cbGetParam( $postdata, $col_choice ) );

		switch ( $choice ) {
			case 'upload':
				$value								=	( isset( $_FILES[$col_file] ) ? $_FILES[$col_file] : null );

				if ( $this->validate( $field, $user, $choice, $value, $postdata, $reason ) ) {
					$_PLUGINS->loadPluginGroup( 'user' );

					$isModerator					=	Application::MyUser()->isModeratorFor( Application::User( (int) $user->id ) );

					$_PLUGINS->trigger( 'onBeforeUserAvatarUpdate', array( &$user, &$user, $isModerator, &$value['tmp_name'] ) );
					if ( $_PLUGINS->is_errors() ) {
						$this->_setErrorMSG( $_PLUGINS->getErrorMSG() );
					}

					$conversionType					=	(int) ( isset( $ueConfig['conversiontype'] ) ? $ueConfig['conversiontype'] : 0 );
					$imageSoftware					=	( $conversionType == 5 ? 'gmagick' : ( $conversionType == 1 ? 'imagick' : 'gd' ) );
					$imagePath						=	$_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/';
					$fileName						=	( $col == 'avatar' ? '' : $col . '_' ) . uniqid( $user->id . '_' );

					try {
						$image						=	new \CBLib\Image\Image( $imageSoftware, $this->_getImageFieldParam( $field, 'avatarResizeAlways', 1 ), $this->_getImageFieldParam( $field, 'avatarMaintainRatio', 1 ) );

						$image->setName( $fileName );
						$image->setSource( $value );
						$image->setDestination( $imagePath );

						$image->processImage( $this->_getImageFieldParam( $field, 'avatarWidth', 200 ), $this->_getImageFieldParam( $field, 'avatarHeight', 500 ) );

						$newFileName				=	$image->getCleanFilename();

						$image->setName( 'tn' . $fileName );

						$image->processImage( $this->_getImageFieldParam( $field, 'thumbWidth', 60 ), $this->_getImageFieldParam( $field, 'thumbHeight', 86 ) );
					} catch ( Exception $e ) {
						$this->_setValidationError( $field, $user, $reason, $e->getMessage() );
						return;
					}

					$uploadApproval					=	$this->_getImageFieldParam( $field, 'avatarUploadApproval', 1 );

					if ( isset( $user->$col ) && ( ! ( ( $uploadApproval == 1 ) && ! $isModerator ) ) ) {
						// if auto-approved:				//TBD: else need to log update on image approval !
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $newFileName );
					}

					if ( isset( $user->$col ) && ( $user->$col != '' ) ) {
						deleteAvatar( $user->$col );
					}

					if ( ( $uploadApproval == 1 ) && ! $isModerator ) {
						$cbNotification				=	new cbNotification();
						$cbNotification->sendToModerators( cbReplaceVars( CBTxt::T( 'UE_IMAGE_ADMIN_SUB', 'Image Pending Approval' ), $user ), cbReplaceVars( CBTxt::T( 'UE_IMAGE_ADMIN_MSG', 'A user has submitted an image for approval. Please log in and take the appropriate action.'), $user ) );

						$user->$col					=	$newFileName;
						$user->$colapproved			=	0;
					} else {
						$user->$col					=	$newFileName;
						$user->$colapproved			=	1;
					}

					$_PLUGINS->trigger( 'onAfterUserAvatarUpdate', array( &$user, &$user, $isModerator, $newFileName ) );
				}
				break;
		}
	}

	/**
	 * Mutator:
	 * Prepares field data rollback
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function rollbackFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_FILES;

		$col				=	$field->name;
		$col_choice			=	$col . '__choice';
		$col_file			=	$col . '__file';

		$choice				=	stripslashes( cbGetParam( $postdata, $col_choice ) );

		switch ( $choice ) {
			case 'upload':
				$value		=	( isset( $_FILES[$col_file] ) ? $_FILES[$col_file] : null );

				if ( $this->validate( $field, $user, $choice, $value, $postdata, $reason ) ) {
					deleteAvatar( $user->$col );
				}
				break;
		}
	}

	/**	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save user edit, 'register' for save registration
	 * @return boolean                  True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		global $_CB_framework, $_FILES;

		$isRequired							=	$this->_isRequired( $field, $user, $reason );

		switch ( $columnName ) {
			case 'upload':
				if ( ! $field->params->get( 'image_allow_uploads', 1 ) ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
					return false;
				} elseif ( ! isset( $value['tmp_name'] ) || empty( $value['tmp_name'] ) || ( $value['error'] != 0 ) || ( ! is_uploaded_file( $value['tmp_name'] ) ) ) {
					if ( $isRequired ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Please select a image file before uploading' ) );
					}

					return false;
				} else {
					$upload_size_limit_max	=	(int) $this->_getImageFieldParam( $field, 'avatarSize', 2000 );
					$upload_ext_limit		=	array( 'jpg', 'jpeg', 'gif', 'png' );
					$uploaded_ext			=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_EXTENSION ) ) );

					if ( ( ! $uploaded_ext ) || ( ! in_array( $uploaded_ext, $upload_ext_limit ) ) ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'Please upload only %s' ), implode( ', ', $upload_ext_limit ) ) );
						return false;
					}

					$uploaded_size			=	$value['size'];

					if ( ( $uploaded_size / 1024 ) > $upload_size_limit_max ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'The image file size exceeds the maximum of %s' ), $this->formattedFileSize( $upload_size_limit_max * 1024 ) ) );
						return false;
					}
				}
				break;
			case 'gallery':
				if ( ! $field->params->get( 'image_allow_gallery', ( in_array( $field->name, array( 'avatar', 'canvas' ) ) ? 1 : 0 ) ) ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
					return false;
				}

				$galleryPath				=	$field->params->get( 'image_gallery_path', null );

				if ( ! $galleryPath ) {
					if ( $field->get( 'name' ) == 'canvas' ) {
						$galleryPath		=	'/images/comprofiler/gallery/canvas';
					} else {
						$galleryPath		=	'/images/comprofiler/gallery';
					}
				}

				$galleryImages				=	$this->displayImagesGallery( $_CB_framework->getCfg( 'absolute_path' ) . $galleryPath );

				if ( ! in_array( $value, $galleryImages ) ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_UPLOAD_ERROR_CHOOSE', 'You didn\'t choose an image from the gallery.' ) . $value );
					return false;
				}
				break;
			default:
				$valCol			=	$field->name;
				if ( $isRequired && ( ( ! $user ) || ( ! isset( $user->$valCol ) ) || ( ! $user->$valCol ) ) ) {
					if ( ! $value ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_FIELDREQUIRED', 'This Field is required' ) );
						return false;
					}
				}
				break;
		}

		return true;
	}

	/**	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		$query								=	array();
		$searchMode							=	$this->_bindSearchMode( $field, $searchVals, $postdata, 'none', $list_compare_types );
		$col								=	$field->name;
		$colapproved						=	$col . 'approved';
		$value								=	cbGetParam( $postdata, $col );

		if ( $value === '0' ) {
			$value							=	0;
		} elseif ( $value == '1' ) {
			$value							=	1;
		} else {
			$value							=	null;
		}

		if ( $value !== null ) {
			$searchVals->$col				=	$value;

			// When is not advanced search is used we need to invert our search:
			if ( $searchMode == 'isnot' ) {
				if ( $value === 0 ) {
					$value					=	1;
				} elseif ( $value == 1 ) {
					$value					=	0;
				}
			}

			$sql							=	new cbSqlQueryPart();
			$sql->tag						=	'column';
			$sql->name						=	$colapproved;
			$sql->table						=	$field->table;
			$sql->type						=	'sql:operator';
			$sql->operator					=	$value ? 'AND' : 'OR';
			$sql->searchmode				=	$searchMode;

			$sqlpict						=	new cbSqlQueryPart();
			$sqlpict->tag					=	'column';
			$sqlpict->name					=	$col;
			$sqlpict->table					=	$field->table;
			$sqlpict->type					=	'sql:field';
			$sqlpict->operator				=	$value ? 'IS NOT' : 'IS';
			$sqlpict->value					=	'NULL';
			$sqlpict->valuetype				=	'const:null';
			$sqlpict->searchmode			=	$searchMode;

			$sqlapproved					=	new cbSqlQueryPart();
			$sqlapproved->tag				=	'column';
			$sqlapproved->name				=	$colapproved;
			$sqlapproved->table				=	$field->table;
			$sqlapproved->type				=	'sql:field';
			$sqlapproved->operator			=	$value ? '>' : '=';
			$sqlapproved->value				=	0;
			$sqlapproved->valuetype			=	'const:int';
			$sqlapproved->searchmode		=	$searchMode;

			$sql->addChildren( array( $sqlpict, $sqlapproved ) );

			$query[]						=	$sql;
		}

		return $query;
	}

	/**
	 * returns full or thumbnail image tag
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $reason
	 * @param  boolean      $thumbnail
	 * @param  int          $showAvatar
	 * @return null|string
	 */
	function _avatarHtml( &$field, &$user, $reason, $thumbnail = true, $showAvatar = 2 ) {
		global $_CB_framework, $ueConfig;

		switch ( (int) $field->params->get( 'altText', 0 ) ) {
			case 2:
				$alt			=	cbReplaceVars( $field->params->get( 'altTextCustom' ), $user );
				break;
			case 1:
				$alt			=	null;
				break;
			default:
				if ( $field->name == 'avatar' ) {
					$alt		=	getNameFormat( $user->name, $user->username, $ueConfig['name_format'] );
				} elseif ( $field->name == 'canvas' ) {
					$alt		=	null;
				} else {
					$alt		=	cbReplaceVars( $field->title, $user );		// does htmlspecialchars()
				}
				break;
		}

		switch ( (int) $field->params->get( 'titleText', 0 ) ) {
			case 2:
				$title			=	cbReplaceVars( $field->params->get( 'titleTextCustom' ), $user );
				break;
			case 1:
				$title			=	null;
				break;
			default:
				if ( $field->name == 'avatar' ) {
					$title		=	getNameFormat( $user->name, $user->username, $ueConfig['name_format'] );
				} elseif ( $field->name == 'canvas' ) {
					$title		=	null;
				} else {
					$title		=	cbReplaceVars( $field->title, $user );		// does htmlspecialchars()
				}
				break;
		}

		$imgUrl					=	$this->_avatarLivePath( $field, $user, $thumbnail, $showAvatar );

		if ( ! $imgUrl ) {
			return null;
		}

		if ( $field->name == 'canvas' ) {
			$return				=	'<div style="background-image: url(' . $imgUrl . ')"' . ( $title ? ' title="' . htmlspecialchars( $title ) . '"' : null ) . ' class="cbImgCanvas ' . ( $thumbnail ? 'cbThumbCanvas' : 'cbFullCanvas' ) . '"></div>';
		} else {
			switch ( $field->params->get( 'imageStyle', 'roundedbordered' ) ) {
				case 'rounded':
					$style		=	' img-rounded';
					break;
				case 'roundedbordered':
					$style		=	' img-thumbnail';
					break;
				case 'circle':
					$style		=	' img-circle';
					break;
				case 'circlebordered':
					$style		=	' img-thumbnail img-circle';
					break;
				default:
					$style		=	null;
					break;
			}

			if ( $user->get( '_allowProfileLink', $field->get( '_allowProfileLink', 1 ) ) && ( ! in_array( $reason, array( 'profile', 'edit' ) ) ) ) {
				$openTag		=	'<a href="' . $_CB_framework->userProfileUrl( $user->id, true, ( $field->name == 'avatar' ? null : $field->tabid ) ) . '">';
				$closeTag		=	'</a>';
			} else {
				$openTag		=	null;
				$closeTag		=	null;
			}

			$return				=	$openTag . '<img src="' . $imgUrl . '"' . ( $alt ? ' alt="' . htmlspecialchars( $alt ) . '"' : null ) . ( $title ? ' title="' . htmlspecialchars( $title ) . '"' : null ) . ' class="cbImgPict ' . ( $thumbnail ? 'cbThumbPict' : 'cbFullPict' ) . $style . '" />' . $closeTag;
		}

		return $return;
	}

	/**
	 * returns full or thumbnail path of image
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  boolean      $thumbnail
	 * @param  int          $showAvatar
	 * @return null|string
	 */
	function _avatarLivePath( &$field, &$user, $thumbnail = true, $showAvatar = 2 ) {
		global $_CB_framework;

		$liveSite							=	$_CB_framework->getCfg( 'live_site' );
		$absolutePath						=	$_CB_framework->getCfg( 'absolute_path' );

		$return								=	null;

		if ( $user && $user->id ) {
			$fieldName						=	$field->get( 'name' );
			$approvedFieldName				=	$fieldName . 'approved';
			$value							=	$user->get( $fieldName );
			$approvedValue					=	$user->get( $approvedFieldName );

			$tn								=	( $thumbnail ? 'tn' : null );

			if ( ( $value != '' ) && ( ( $approvedValue > 0 ) || ( $showAvatar == 10 ) ) ) {
				if ( strpos( $value, 'gallery/' ) === false ) {
					$return					=	'/images/comprofiler/' . $tn . $value;
				} else {
					$galleryPath			=	$field->params->get( 'image_gallery_path', null );

					if ( ! $galleryPath ) {
						if ( $fieldName == 'canvas' ) {
							$galleryPath	=	'/images/comprofiler/gallery/canvas';
						} else {
							$galleryPath	=	'/images/comprofiler/gallery';
						}
					}

					$return					=	$galleryPath . '/' . preg_replace( '!^gallery/(tn)?!', ( $tn ? 'tn' : '' ), $value );

					if ( ! is_file( $absolutePath . $return ) ) {
						$return				=	$galleryPath . '/' . preg_replace( '!^gallery/!', '', $value );
					}
				}

				if ( ! is_file( $absolutePath . $return ) ) {
					$return					=	null;
				}
			}

			if ( ( $return === null ) && ( $showAvatar == 2 ) ) {
				$imagesBase					=	'avatar';

				if ( $field->name == 'canvas' ) {
					$imagesBase				=	'canvas';
				}

				if ( $approvedValue == 0 ) {
					$icon					=	$field->params->get( 'pendingDefaultAvatar', null );

					if ( $icon == 'none' ) {
						return null;
					} elseif ( $icon ) {
						if ( ( $icon != 'pending_n.png' ) && ( ! is_file( selectTemplate( 'absolute_path' ) . '/images/' . $imagesBase . '/' . $tn . $icon ) ) ) {
							$icon			=	null;
						}
					}

					if ( ! $icon ) {
						$icon				=	'pending_n.png';
					}
				} else {
					$icon					=	$field->params->get( 'defaultAvatar', null );

					if ( $icon == 'none' ) {
						return null;
					} elseif ( $icon ) {
						if ( ( $icon != 'nophoto_n.png' ) && ( ! is_file( selectTemplate( 'absolute_path' ) . '/images/' . $imagesBase . '/' . $tn . $icon ) ) ) {
							$icon			=	null;
						}
					}

					if ( ! $icon ) {
						$icon				=	'nophoto_n.png';
					}
				}

				// Image doesn't exist in the template; check default template:
				if ( ! is_file( selectTemplate( 'absolute_path' ) . '/images/' . $imagesBase . '/' . $tn . $icon ) ) {
					// Image doesn't exist in the default template so return null to suppress display:
					if ( ! is_file( selectTemplate( 'absolute_path', 'default' ) . '/images/' . $imagesBase . '/' . $tn . $icon ) ) {
						return null;
					}

					return selectTemplate( 'live_site', 'default' ) . 'images/' . $imagesBase . '/' . $tn . $icon;
				}

				return selectTemplate() . 'images/' . $imagesBase . '/' . $tn . $icon;
			}
		}

		if ( $return ) {
			$return							=	$liveSite . $return;
		}

		return $return;
	}

	/**
	 * returns html edit display of image field
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $reason
	 * @param  boolean      $displayFieldIcons
	 * @return null|string
	 */
	function _htmlEditForm( &$field, &$user, $reason, $displayFieldIcons = true ) {
		global $_CB_framework;

		$fieldName								=	$field->get( 'name' );

		if ( ! ( $field->params->get( 'image_allow_uploads', 1 ) || $field->params->get( 'image_allow_gallery', ( in_array( $fieldName, array( 'avatar', 'canvas' ) ) ? 1 : 0 ) ) ) ) {
			return null;
		}

		$approvedFieldName						=	$fieldName . 'approved';
		$value									=	$user->get( $fieldName );
		$approvedValue							=	$user->get( $approvedFieldName );
		$required								=	$this->_isRequired( $field, $user, $reason );

		$uploadWidthLimit						=	$this->_getImageFieldParam( $field, 'avatarWidth', 500 );
		$uploadHeightLimit						=	$this->_getImageFieldParam( $field, 'avatarHeight', 200 );
		$uploadSizeLimitMax						=	$this->_getImageFieldParam( $field, 'avatarSize', 2000 );
		$uploadExtLimit							=	array( 'gif', 'png', 'jpg', 'jpeg' );
		$restrictions							=	array();

		if ( $uploadExtLimit ) {
			$restrictions[]						=	CBTxt::Th( 'IMAGE_FILE_UPLOAD_LIMITS_EXT', 'Your image file must be of [ext] type.', array( '[ext]' => implode( ', ', $uploadExtLimit ) ) );
		}

		if ( $uploadSizeLimitMax ) {
			$restrictions[]						=	CBTxt::Th( 'IMAGE_FILE_UPLOAD_LIMITS_MAX', 'Your image file should not exceed [size].', array( '[size]' => $this->formattedFileSize( $uploadSizeLimitMax * 1024 ) ) );
		}

		if ( $uploadWidthLimit ) {
			$restrictions[]						=	CBTxt::Th( 'IMAGE_FILE_UPLOAD_LIMITS_WIDTH', 'Images exceeding the maximum width of [size] will be resized.', array( '[size]' => $uploadWidthLimit ) );
		}

		if ( $uploadHeightLimit ) {
			$restrictions[]						=	CBTxt::Th( 'IMAGE_FILE_UPLOAD_LIMITS_HEIGHT', 'Images exceeding the maximum height of [size] will be resized.', array( '[size]' => $uploadHeightLimit ) );
		}

		$existingFile							=	( $user->get( 'id' ) ? ( ( $value != null ) ? true : false ) : false );
		$choices								=	array();

		if ( ( $reason == 'register' ) || ( ( $reason == 'edit' ) && ( $user->id == 0 ) ) ) {
			if ( $required == 0 ) {
				$choices[]						=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No image' ) );
			}
		} else {
			if ( $existingFile || ( $required == 0 ) ) {
				$choices[]						=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No change of image' ) );
			}
		}

		$selected								=	null;

		if ( ( $required == 1 ) && ( ! $existingFile ) ) {
			$selected							=	'upload';
		}

		if ( $field->params->get( 'image_allow_uploads', 1 ) ) {
			$choices[]							=	moscomprofilerHTML::makeOption( 'upload', ( $existingFile ? CBTxt::T( 'Upload new image' ) : CBTxt::T( 'Upload image' ) ) );
		}

		if ( $field->params->get( 'image_allow_gallery', ( in_array( $fieldName, array( 'avatar', 'canvas' ) ) ? 1 : 0 ) ) ) {
			$choices[]							=	moscomprofilerHTML::makeOption( 'gallery', ( $existingFile ? CBTxt::T( 'Select new image from gallery' ) : CBTxt::T( 'Select image from gallery' ) ) );
		}

		if ( ( $_CB_framework->getUi() == 2 ) && $existingFile && ( $approvedValue == 0 ) ) {
			$choices[]							=	moscomprofilerHTML::makeOption( 'approve', CBTxt::T( 'Approve image' ) );
		}

		if ( $existingFile && ( $required == 0 ) ) {
			$choices[]							=	moscomprofilerHTML::makeOption( 'delete', CBTxt::T( 'Remove image' ) );
		}

		$return									=	null;

		if ( ( $reason != 'register' ) && ( $user->id != 0 ) && $existingFile ) {
			$return								.=	'<div class="form-group cb_form_line clearfix">' . $this->_avatarHtml( $field, $user, $reason ) . '</div>';
		}

		if ( ( $reason == 'edit' ) && $existingFile && ( $approvedValue == 0 ) && Application::MyUser()->isModeratorFor( Application::User( (int) $user->id ) ) ) {
			$return								.=	'<div class="form-group cb_form_line clearfix">' . $this->_avatarHtml( $field, $user, $reason, false, 10 ) . '</div>';
		}

		if ( count( $choices ) > 1 ) {
			static $functOut					=	false;

			$additional							=	' class="form-control"';

			if ( ( $_CB_framework->getUi() == 1 ) && ( $reason == 'edit' ) && $field->get( 'readonly' ) ) {
				$additional						.=	' disabled="disabled"';
			}

			$translatedTitle					=	$this->getFieldTitle( $field, $user, 'html', $reason );
			$htmlDescription					=	$this->getFieldDescription( $field, $user, 'htmledit', $reason );
			$trimmedDescription					=	trim( strip_tags( $htmlDescription ) );

			$tooltip							=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, $additional ) : $additional );

			$return								.=	'<div class="form-group cb_form_line clearfix">'
												.		moscomprofilerHTML::selectList( $choices, $fieldName . '__choice', $tooltip, 'value', 'text', $selected, $required, true, null, false )
												.		$this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, 'select', '', null, '', array(), $displayFieldIcons, $required )
												.	'</div>';

			if ( ! $functOut ) {
				$js								=	"function cbslideImageFile( choice, name ) {"
												.		"if ( ( choice == '' ) || ( choice == 'delete' ) ) {"
												.			"$( '#cbimagefile_upload_' + name + ',#cbimagefile_gallery_' + name ).slideUp( 'slow' );"
												.			"$( '#cbimagefile_upload_' + name + ' input,#cbimagefile_gallery_' + name + ' input' ).prop( 'readonly', true );"
												.		"} else if ( choice == 'upload' ) {"
												.			"$( '#cbimagefile_gallery_' + name ).slideUp( 'slow' );"
												.			"$( '#cbimagefile_upload_' + name ).slideDown( 'slow' );"
												.			"$( '#cbimagefile_gallery_' + name + ' input' ).prop( 'readonly', true );"
												.			"$( '#cbimagefile_upload_' + name + ' input' ).prop( 'readonly', false );"
												.		"} else if ( choice == 'gallery' ) {"
												.			"$( '#cbimagefile_upload_' + name ).slideUp( 'slow' );"
												.			"$( '#cbimagefile_gallery_' + name ).slideDown( 'slow' );"
												.			"$( '#cbimagefile_upload_' + name + ' input' ).prop( 'readonly', true );"
												.			"$( '#cbimagefile_gallery_' + name + ' input' ).prop( 'readonly', false );"
												.		"}"
												.	"}";

				$_CB_framework->outputCbJQuery( $js );

				$functOut						=	true;
			}

			$js									=	"$( '#cbimagefile_upload_" . addslashes( $fieldName ) . ",#cbimagefile_gallery_" . addslashes( $fieldName ) . "' ).hide();"
												.	"$( '#cbimagefile_upload_" . addslashes( $fieldName ) . " input,#cbimagefile_gallery_" . addslashes( $fieldName ) . " input' ).prop( 'readonly', true );"
												.	"$( '#" . addslashes( $fieldName ) . "__choice' ).on( 'click change', function() {"
												.		"cbslideImageFile( $( this ).val(), '" . addslashes( $fieldName ) . "' );"
												.	"}).change();";

			$_CB_framework->outputCbJQuery( $js );
		} else {
			$return								.=	'<input type="hidden" name="' . htmlspecialchars( $fieldName ) . '__choice" value="' . htmlspecialchars( $choices[0]->value ) . '" />';
		}

		if ( $field->params->get( 'image_allow_uploads', 1 ) ) {
			$validationAttributes				=	array();
			$validationAttributes[]				=	cbValidator::getRuleHtmlAttributes( 'extension', implode( ',', $uploadExtLimit ) );

			if ( $uploadSizeLimitMax ) {
				$validationAttributes[]			=	cbValidator::getRuleHtmlAttributes( 'filesize', array( 0, $uploadSizeLimitMax, 'KB' ) );
			}

			$return								.=	'<div id="cbimagefile_upload_' . htmlspecialchars( $fieldName ) . '" class="form-group cb_form_line clearfix">'
												.		( $restrictions ? '<div class="help-block">' . implode( ' ', $restrictions ) . '</div>' : null )
												.		'<div>'
												.			CBTxt::T( 'Select image file' ) . ' <input type="file" name="' . htmlspecialchars( $fieldName ) . '__file" value="" class="form-control' . ( $required == 1 ? ' required' : null ) . '"' . implode( ' ', $validationAttributes ) . ' />'
												.			( count( $choices ) <= 0 ? $this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, 'select', '', null, '', array(), $displayFieldIcons, $required ) : null )
												.		'</div>'
												.		'<div class="help-block">';

			if ( $field->params->get( 'image_terms', 0 ) ) {
				$termsOutput					=	$field->params->get( 'terms_output', 'url' );
				$termsType						=	CBTxt::T( $field->params->get( 'terms_type', null ) );
				$termsDisplay					=	$field->params->get( 'terms_display', 'modal' );
				$termsURL						=	$field->params->get( 'terms_url', null );
				$termsText						=	$field->params->get( 'terms_text', null );
				$termsWidth						=	(int) $field->params->get( 'terms_width', 400 );
				$termsHeight					=	(int) $field->params->get( 'terms_height', 200 );

				if ( ! $termsType ) {
					$termsType					=	CBTxt::T( 'TERMS_AND_CONDITIONS', 'Terms and Conditions' );
				}

				if ( ! $termsHeight ) {
					$termsHeight				=	200;
				}

				if ( ( ( $termsOutput == 'url' ) && $termsURL ) || ( ( $termsOutput == 'text' ) && $termsText ) ) {
					if ( $termsDisplay == 'iframe' ) {
						if ( $termsOutput == 'url' ) {
							$return				.=			'<iframe class="cbTermsFrameURL" height="' . $termsHeight . '" width="' . ( $termsWidth ? $termsWidth : '100%' ) . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
						} else {
							$return				.=			'<div class="cbTermsFrameText" style="height:' . $termsHeight . 'px;width:' . ( $termsWidth ? $termsWidth . 'px' : '100%' ) . ';overflow:auto;">' . CBTxt::T( $termsText ) . '</div>';
						}
					}

					if ( $termsDisplay != 'iframe' ) {
						$attributes				=	' class="cbTermsLink"';

						if ( ( $termsOutput == 'text' ) && ( $termsDisplay == 'window' ) ) {
							$termsDisplay		=	'modal';
						}

						if ( $termsDisplay == 'modal' ) {
							if ( ! $termsWidth ) {
								$termsWidth		=	400;
							}

							if ( $termsOutput == 'url' ) {
								$tooltip		=	'<iframe class="cbTermsModalURL" height="' . $termsHeight . '" width="' . $termsWidth . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
							} else {
								$tooltip		=	'<div class="cbTermsModalText" style="height:' . $termsHeight . 'px;width:' . $termsWidth . 'px;overflow:auto;">' . CBTxt::T( $termsText ) . '</div>';
							}

							$url				=	'javascript:void(0);';
							$attributes			.=	' ' . cbTooltip( $_CB_framework->getUi(), $tooltip, $termsType, 'auto', null, null, null, 'data-hascbtooltip="true" data-cbtooltip-modal="true"' );
						} else {
							$url				=	htmlspecialchars( $termsURL );
							$attributes			.=	' target="_blank"';
						}

						$return					.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_IMAGE_FILE_URL_TERMS', 'By uploading, you certify that you have the right to distribute this image and that it does not violate the <a href="[url]"[attributes]>[type]</a>',
																		  array( '[url]' => $url, '[attributes]' => $attributes, '[type]' => $termsType )
																		);
					} else {
						$return					.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_IMAGE_FILE_TERMS', 'By uploading, you certify that you have the right to distribute this image and that it does not violate the above [type].', array( '[type]' => $termsType ) );
					}
				} else {
					$return						.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_IMAGE_FILE', 'By uploading, you certify that you have the right to distribute this image.' );
				}
			} else {
				$return							.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_IMAGE_FILE', 'By uploading, you certify that you have the right to distribute this image.' );
			}

			$return								.=		'</div>'
												.	'</div>';
		}

		if ( $field->params->get( 'image_allow_gallery', ( in_array( $fieldName, array( 'avatar', 'canvas' ) ) ? 1 : 0 ) ) ) {
			$galleryPath						=	$field->params->get( 'image_gallery_path', null );

			if ( ! $galleryPath ) {
				if ( $fieldName == 'canvas' ) {
					$galleryPath				=	'/images/comprofiler/gallery/canvas';
				} else {
					$galleryPath				=	'/images/comprofiler/gallery';
				}
			}

			$galleryImages						=	$this->displayImagesGallery( $_CB_framework->getCfg( 'absolute_path' ) . $galleryPath );

			$return								.=	'<div id="cbimagefile_gallery_' . htmlspecialchars( $fieldName ) . '" class="form-group cb_form_line clearfix">';

			$itemCount							=	0;

			for ( $i = 0; $i < count( $galleryImages ); $i++ ) {
				$itemCount++;

				$imgName						=	ucfirst( str_replace( '_', ' ', preg_replace( '/^(.*)\..*$/', '\1', preg_replace( '/^tn/', '', $galleryImages[$i] ) ) ) );

				if ( $fieldName == 'canvas' ) {
					$return						.=		'<div class="form-group cb_form_line clearfix">'
												.			'<div class="col-sm-1">'
												.				'<input type="radio" name="' . htmlspecialchars( $fieldName ) . '__gallery" id="' . htmlspecialchars( $fieldName ) . '__gallery_' . (int) $i . '" value="' . htmlspecialchars( $galleryImages[$i] ) . '"' . ( $required == 1 ? ' class="required"' : null ) . ( $galleryImages[$i] == $value ? ' checked' : null ) . ' />'
												.			'</div>'
												.			'<div class="col-sm-11">'
												.				'<label for="' . htmlspecialchars( $fieldName ) . '__gallery_' . (int) $i . '" style="width: 100%;">'
												.					'<div style="height: 100px; background-image: url(' . $_CB_framework->getCfg( 'live_site' ) . $galleryPath . '/'. htmlspecialchars( $galleryImages[$i] ) . ')" title="' . htmlspecialchars( $imgName ) . '" class="cbImgCanvas cbThumbCanvas"></div>'
												.				'</label>'
												.			'</div>'
												.		'</div>';
				} else {
					$return						.=		'<div class="containerBox">'
												.			'<div class="containerBoxInner" style="width: 80px;">'
												.				'<label for="' . htmlspecialchars( $fieldName ) . '__gallery_' . (int) $i . '">'
												.					'<img src="' . $_CB_framework->getCfg( 'live_site' ) . $galleryPath . '/'. htmlspecialchars( $galleryImages[$i] ) . '" alt="' . htmlspecialchars( $imgName ) . '" title="' . htmlspecialchars( $imgName ) . '" class="img-thumbnail cbThumbPict" />'
												.					'<br />'
												.					'<input type="radio" name="' . htmlspecialchars( $fieldName ) . '__gallery" id="' . htmlspecialchars( $fieldName ) . '__gallery_' . (int) $i . '" value="' . htmlspecialchars( $galleryImages[$i] ) . '"' . ( $required == 1 ? ' class="required"' : null ) . ( $galleryImages[$i] == $value ? ' checked' : null ) . ' />'
												.				'</label>'
												.			'</div>'
												.		'</div>';
				}
			}

			$return								.=	'</div>';
		}

		return $return;
	}

	/**
	 * This event-driven method is temporary until we get another API for deleting each field:
	 *
	 * @param  UserTable  $user
	 */
	function onBeforeDeleteUser( $user ) {
		global $_CB_framework, $_CB_database;

		$query					=	'SELECT ' . $_CB_database->NameQuote( 'name' )
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_fields' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'type' ). " = " . $_CB_database->Quote( 'image' );
		$_CB_database->setQuery( $query );
		$imageFields			=	$_CB_database->loadResultArray();

		if ( $imageFields ) {
			$imgPath		=	$_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/';

			foreach ( $imageFields as $imageField ) {
				if ( isset( $user->$imageField ) && ( $user->$imageField != '' ) && ( strpos( $user->$imageField, 'gallery/' ) === false ) ) {
					if ( file_exists( $imgPath . $user->$imageField ) ) {
						@unlink( $imgPath . $user->$imageField );

						if ( file_exists( $imgPath . 'tn' . $user->$imageField ) ) {
							@unlink( $imgPath . 'tn' . $user->$imageField );
						}
					}
				}
			}
		}
	}

	public function loadDefaultImages( $name, $value, $control_name, $basePath = 'avatar' ) {
		$values					=	array();
		$values[]				=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'Normal CB Default' ) );
		$values[]				=	moscomprofilerHTML::makeOption( 'none', CBTxt::T( 'No image' ) );

		if ( is_dir( selectTemplate( 'absolute_path', null, 1 ) . '/images/' . $basePath ) ) {
			foreach ( scandir( selectTemplate( 'absolute_path', null, 1 ) . '/images/' . $basePath ) as $avatar ) {
				if ( ( ! preg_match( '/^tn/', $avatar ) ) && preg_match( '!^[\w-]+[.](jpg|jpeg|png|gif)$!', $avatar ) ) {
					$values[]	=	moscomprofilerHTML::makeOption( $avatar, $avatar );
				}
			}
		}

		return $values;
	}

	public function loadDefaultCanvasImages( $name, $value, $control_name ) {
		return $this->loadDefaultImages( $name, $value, $control_name, 'canvas' );
	}

	protected function displayImagesGallery( $path ) {
		$dir									=	@opendir( $path );
		$images									=	array();
		$index									=	0;

		while ( true == ( $file = @readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) && is_file( $path . '/' . $file ) && ( ! is_link( $path. '/' . $file ) ) ) {
				if ( preg_match( '/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $file ) ) {
					if ( preg_match( '/^tn/', $file ) ) {
						$full					=	array_search( preg_replace( '/^tn/', '', $file ), $images );

						if ( $full !== false ) {
							unset( $images[$full] );
						}

						$images[$index]			=	$file;
					} else {
						$thumb					=	array_search( 'tn' . $file, $images );

						if ( $thumb === false ) {
							$images[$index]		=	$file;
						}
					}

					$index++;
				}
			}
		}

		@closedir( $dir );

		$images									=	array_values( $images );

		@sort( $images );
		@reset( $images );

		return $images;
	}

	/**
	 * Returns file size formatted from bytes
	 *
	 * @param int $bytes
	 * @return string
	 */
	private function formattedFileSize( $bytes )
	{
		if ( $bytes >= 1099511627776 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_TB', '%%COUNT%% TB|%%COUNT%% TBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1099511627776, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1073741824 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_GB', '%%COUNT%% GB|%%COUNT%% GBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1073741824, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1048576 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_MB', '%%COUNT%% MB|%%COUNT%% MBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1048576, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1024 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_KB', '%%COUNT%% KB|%%COUNT%% KBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1024, 2, '.', '' ) ) );
		} else {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_B', '%%COUNT%% B|%%COUNT%% Bs', array( '%%COUNT%%' => (float) number_format( $bytes, 2, '.', '' ) ) );
		}

		return $size;
	}
}
class CBfield_status extends cbFieldHandler {

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_CB_framework, $ueConfig;

		$return								=	null;

		if ( ( $ueConfig['allow_onlinestatus'] == 1 ) ) {
			$lastTime						=	$_CB_framework->userOnlineLastTime( $user->id );
			$isOnline						=	( $lastTime != null );

			switch ( $output ) {
				case 'html':
				case 'rss':
					$useLayout				=	true;

					if ( isset( $user ) && $user->id ) {
						if ( $isOnline > 0 ) {
							$value			=	CBTxt::T( 'UE_ISONLINE', 'ONLINE' );
							$icon			=	'circle';
							$class			=	'cb_online text-success';
						} else {
							$value			=	CBTxt::T( 'UE_ISOFFLINE', 'OFFLINE' );
							$icon			=	'circle-o';
							$class			=	'cb_offline text-danger';
						}

						if ( isset( $field->_imgMode ) ) {
							$imgMode		=	(int) $field->get( '_imgMode' );
							$useLayout		=	false; // When using override we want to avoid layout usage
						} else {
							$imgMode		=	(int) $field->params->get( ( $reason == 'list' ? 'displayModeList' : 'displayMode' ), 2 );
						}

						switch ( $imgMode ) {
							case 0:
								$return		=	'<span class="' . $class . '">' . htmlspecialchars( $value ) . '</span>';
								break;
							case 1:
								$return		=	'<span class="' . $class . '"><span class="fa fa-' . $icon . '" title="' . htmlspecialchars( $value ) . '"></span></span>';
								break;
							case 2:
								$return		=	'<span class="' . $class . '"><span class="fa fa-' . $icon . '"></span> ' . htmlspecialchars( $value ) . '</span>';
								break;
						}
					}

					if ( $useLayout ) {
						$return				=	$this->formatFieldValueLayout( $return, $reason, $field, $user );
					}
					break;
				case 'htmledit':
//					if ( $reason == 'search' ) {
//						$choices			=	array();
//						$choices[]			=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No preference' );
//						$choices[]			=	moscomprofilerHTML::makeOption( '1', CBTxt::T( 'Is Online' ) );
//						$choices[]			=	moscomprofilerHTML::makeOption( '0', CBTxt::T( 'Is Offline' ) );
//
//						$col				=	$field->name;
//						$value				=	$user->$col;
//
//						$return				=	$this->_fieldSearchModeHtml( $field, $user, $this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', $value, null, $choices ), 'singlechoice', $list_compare_types );
//					}
					break;
				case 'json':
				case 'php':
				case 'xml':
				case 'csvheader':
				case 'fieldslist':
				case 'csv':
				default:
					if ( isset( $user ) && $user->id ) {
						$return				=	$this->_formatFieldOutputIntBoolFloat( $field->name, ( $isOnline > 0 ? 'true' : 'false' ), $output );
					}
					break;
			}
		}

		return $return;
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );
		// nothing to do, Status fields don't save :-)
	}

	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		return array(); // Online Status doesn't currently have searching
	}
}
class CBfield_counter extends cbFieldHandler {
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$oReturn							=	'';

		if ( is_object( $user ) ) {
			$values							=	array();
			foreach ( $field->getTableColumns() as $col ) {
				$values[]					=	(int) $user->$col;
			}
			$value							=	implode( ', ', $values );

			switch ( $output ) {
				case 'html':
				case 'rss':
					$oReturn				=	$this->formatFieldValueLayout( $value, $reason, $field, $user );
					break;

				case 'htmledit':
					$oReturn				=	null;

					if ( $reason == 'search' ) {
						$minNam				=	$field->name . '__minval';
						$maxNam				=	$field->name . '__maxval';

						$minVal				=	$user->get( $minNam );
						$maxVal				=	$user->get( $maxNam );

						if ( $maxVal === null ) {
							$maxVal			=	99999;
						}

						$choices			=	array();

						for ( $i = 0 ; $i <= 10000 ; ( $i < 5 ? $i += 1 : ( $i < 30 ? $i += 5 : ( $i < 100 ? $i += 10 : ( $i < 1000 ? $i += 100 : $i += 1000 ) ) ) ) ) {
							$choices[]		=	moscomprofilerHTML::makeOption( ( $i == 0 ? 0 : (string) $i ), $i );
						}

						$fieldNameSave		=	$field->name;
						$field->name		=	$minNam;

						$minHtml			=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', $minVal, null, $choices );

						$field->name		=	$maxNam;

						$choices[]			=	moscomprofilerHTML::makeOption( '99999', CBTxt::T( 'UE_ANY', 'Any' ) );

						$maxHtml			=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', $maxVal, null, $choices );

						$field->name		=	$fieldNameSave;

						$oReturn			=	$this->_fieldSearchRangeModeHtml( $field, $user, $output, $reason, $value, $minHtml, $maxHtml, $list_compare_types );
					}
					break;

				case 'json':
				case 'php':
				case 'xml':
				case 'csvheader':
				case 'fieldslist':
				case 'csv':
				default:
					$oReturn				=	$this->_formatFieldOutputIntBoolFloat( $field->name, $value, $output );;
					break;
			}
		}
		return $oReturn;
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );
		// nothing to do, counter Status fields don't save :-)
	}

	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		$query							=	array();

		$col							=	$field->name;
		$minNam							=	$col . '__minval';
		$maxNam							=	$col . '__maxval';

		$searchMode						=	$this->_bindSearchRangeMode( $field, $searchVals, $postdata, $minNam, $maxNam, $list_compare_types );

		if ( $searchMode ) {
			$minVal						=	(int) cbGetParam( $postdata, $minNam, 0 );
			$maxVal						=	(int) cbGetParam( $postdata, $maxNam, 0 );

			if ( $minVal && ( $minVal != 0 ) ) {
				$searchVals->$minNam	=	$minVal;

				$operator				=	( $searchMode == 'isnot' ? ( $minVal == $maxVal ? '<' : '<=' ) : '>=' );

				$min					=	$this->_intToSql( $field, $col, $minVal, $operator, $searchMode );
			} else {
				$min					=	null;
			}

			if ( $maxVal && ( $maxVal != 99999 ) ) {
				$searchVals->$maxNam	=	$maxVal;

				$operator				=   ( $searchMode == 'isnot' ? ( $maxVal == $minVal ? '>' : '>=' ) : '<=' );

				$max					=	$this->_intToSql( $field, $col, $maxVal, $operator, $searchMode );
			} else {
				$max					=	null;
			}

			if ( $min && $max ) {
				$sql					=	new cbSqlQueryPart();
				$sql->tag				=	'column';
				$sql->name				=	$col;
				$sql->table				=	$field->table;
				$sql->type				=	'sql:operator';
				$sql->operator			=	( $searchMode == 'isnot' ? 'OR' : 'AND' );
				$sql->searchmode		=	$searchMode;

				$sql->addChildren( array( $min, $max ) );

				$query[]				=	$sql;
			} elseif ( $min ) {
				$query[]				=	$min;
			} elseif ( $max ) {
				$query[]				=	$max;
			}
		}

		return $query;
	}

	/**
	 * Internal function to build SQL request
	 * @access private
	 *
	 * @param  FieldTable      $field
	 * @param  string          $col
	 * @param  int             $value
	 * @param  string          $operator
	 * @param  string          $searchMode
	 * @return cbSqlQueryPart
	 */
	function _intToSql( &$field, $col, $value, $operator, $searchMode ) {
		$value							=	(int) $value;
		// $this->validate( $field, $user, $col, $value, $postdata, $reason );
		$sql							=	new cbSqlQueryPart();
		$sql->tag						=	'column';
		$sql->name						=	$col;
		$sql->table						=	$field->table;
		$sql->type						=	'sql:field';
		$sql->operator					=	$operator;
		$sql->value						=	$value;
		$sql->valuetype					=	'const:int';
		$sql->searchmode				=	$searchMode;
		return $sql;
	}
}

class CBfield_connections extends CBfield_counter {
	/**
	 * Formatter:
	 * Returns a field row in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output      'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting  'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string      $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getFieldRow( &$field, &$user, $output, $formatting, $reason, $list_compare_types ) {
		global $ueConfig;

		if ( $ueConfig['allowConnections'] ) {
			return parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );
		}
		return null;
	}
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_CB_framework, $ueConfig;

		$oReturn							=	null;

		if ( $ueConfig['allowConnections'] && is_object( $user ) ) {
			$cbCon							=	new cbConnection( $_CB_framework->myId() );
			$value							=	$cbCon->getConnectionsCount( $user->id );

			switch ( $output ) {
				case 'html':
				case 'rss':
					$oReturn				=	$this->formatFieldValueLayout( $value, $reason, $field, $user );
					break;

				case 'htmledit':
					// $oReturn				=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
					$oReturn				=	null;		//TBD for now no searches...not optimal in SQL anyway.
					break;

				case 'json':
				case 'php':
				case 'xml':
				case 'csvheader':
				case 'fieldslist':
				case 'csv':
				default:
					$oReturn				=	$this->_formatFieldOutputIntBoolFloat( $field->name, $value, $output );;
					break;
			}
		}
		return $oReturn;
	}
	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		global $ueConfig;

		$searchMode						=	$this->_bindSearchMode( $field, $searchVals, $postdata, 'none', $list_compare_types );
		$query							=	array();
		if ( $ueConfig['allowConnections'] && $searchMode ) {
			$col						=	$field->name;
			$minNam						=	$col . '__minval';
			$maxNam						=	$col . '__maxval';
			$minVal						=	(int) cbGetParam( $postdata, $minNam, 0 );
			$maxVal						=	(int) cbGetParam( $postdata, $maxNam, 0 );
			if ( $minVal && ( $minVal != 0 ) ) {
				$searchVals->$minNam	=	$minVal;
				$query[]				=	$this->_intToSql( $field, $col, $minVal, '>=', $searchMode );
			}
			if ( $maxVal && ( $maxVal != 0 ) ) {
				$searchVals->$maxNam	=	$maxVal;
				$query[]				=	$this->_intToSql( $field, $col, $maxVal, '<=', $searchMode );
			}
		}
		return $query;
	}
	/**
	 * Internal function to build SQL request
	 * @access private
		<data name="change_logs" type="sql:count" distinct="id"  table="#__cpay_history" class="cbpaidHistory">
			<joinkeys dogroupby="true">
				<column name="table_name"   operator="=" value="#__cpay_payment_baskets" type="sql:field" valuetype="const:string" />
				<column name="table_key_id" operator="=" value="id" type="sql:field" valuetype="sql:field" />
			</joinkeys>
		</data>

		<where>
			<column name="id"     operator="=" value="plan_id" type="int"       valuetype="sql:formula">
				<data name="plan_id" type="sql:field" table="#__cpay_payment_items" class="cbpaidPayementItem" key="plan_id" value="id" valuetype="sql:field">
					<data name="basket_id" type="sql:field" table="#__cpay_payment_baskets" class="cbpaidPayementBasket" key="id" value="payment_basket_id" valuetype="sql:field">
						<where>
							<column name="payment_status" operator="=" value="Completed" type="sql:field" valuetype="const:string" />
						</where>
					</data>
				</data>
		    </column>
		</where>

		<column name="id"     operator="=" value="plan_id" type="int"       valuetype="sql:formula">
			<data name="plan_id" type="sql:field" table="#__cpay_payment_items" class="cbpaidPayementItem" key="plan_id" value="id" valuetype="sql:field">
				<data name="basket_id" type="sql:field" table="#__cpay_payment_baskets" class="cbpaidPayementBasket" key="id" value="payment_basket_id" valuetype="sql:field">
					<where>
						<column name="payment_status" operator="=" value="Completed" type="sql:field" valuetype="const:string" />
					</where>
				</data>
			</data>
	    </column>

	 * @param  FieldTable      $field
	 * @param  string          $col
	 * @param  int             $value
	 * @param  string          $operator
	 * @param  string          $searchMode
	 * @return cbSqlQueryPart
	 */
	function _intToSql( &$field, $col, $value, $operator, $searchMode ) {
		$value							=	(int) $value;
		// $this->validate( $field, $user, $col, $value, $postdata, $reason );
		$sql							=	new cbSqlQueryPart();
		$sql->tag						=	'column';
		$sql->name						=	$field->name;
		$sql->table						=	'#__comprofiler_members';
		$sql->type						=	'sql:count';
		$sql->distinct					=	'memberid';
		$sql->operator					=	$operator;
		$sql->value						=	$value;
		$sql->valuetype					=	'const:int';
		$sql->searchmode				=	$searchMode;
		$sql->key						=	'id';
		$sql->keyvalue					=	'referenceid';
		return $sql;
	}
}

class CBfield_formatname extends cbFieldHandler {
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_CB_framework, $ueConfig;

		$oReturn						=	'';
		if ( isset( $user ) && $user->id ) {

		$value							=	getNameFormat( $user->name, $user->username, $ueConfig['name_format'] );

			switch ( $output ) {
				case 'html':
				case 'rss':
					$allow_link			=	( $user->get( '_allowProfileLink', $field->get( '_allowProfileLink', 1 ) ) && ( $reason != 'profile') );
					if ( $allow_link ) {
						$profileURL		=	$_CB_framework->userProfileUrl( $user->id, true );
						$oReturn		=	'<a href="' . $profileURL . '">' . $value . '</a>';
					} else {
						$oReturn		=	$value;
					}
					$oReturn			=	$this->formatFieldValueLayout( $oReturn, $reason, $field, $user );
					break;

				case 'htmledit':
					$oReturn			=	null;
					break;

				case 'json':
				case 'php':
				case 'xml':
				case 'csvheader':
				case 'fieldslist':
				case 'csv':
				default:
					$oReturn			=	$this->_formatFieldOutput( $field->name, $value, $output );;
					break;
			}
		}
		return $oReturn;
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );
		// nothing to do, Formatted names fields don't save :-)
	}
	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$user, &$postdata, $list_compare_types, $reason ) {
		return array();
	}
}
class CBfield_delimiter extends cbFieldHandler {
	/**
	 * Returns a DELIMITER field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$value			=	cbReplaceVars( cbUnHtmlspecialchars( $field->description ), $user ); //TBD: unhtml is kept for backwards database compatibility until CB 2.0

		if ( $field->params->get( 'field_content_plugins', 0 ) ) {
			$value		=	Application::Cms()->prepareHtmlContentPlugins( $value );
		}

		$return			=	$this->_formatFieldOutput( $field->name, $value, $output, false );

		if ( $output == 'htmledit' ) {
			$return		.=	$this->_fieldIconsHtml( $field, $user, $output, $reason, null, null, $value, null, null, false, false );
		}

		return $return;
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );
		// nothing to do, Delimiter fields don't save :-)
	}
	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$user, &$postdata, $list_compare_types, $reason ) {
		return array();
	}
}

class CBfield_userparams extends cbFieldHandler {
	/**
	 * Initializer:
	 * Puts the default value of $field into $user (for registration or new user in backend)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 */
	public function initFieldToDefault( &$field, &$user, $reason ) {
	}
	/**
	 * Returns a USERPARAMS field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output      'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting  'table', 'td', 'span', 'div', 'none'
	 * @param  string      $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getFieldRow( &$field, &$user, $output, $formatting, $reason, $list_compare_types ) {
		global $_CB_framework, $ueConfig;

		$results							=	null;

		if ( class_exists( 'JFactory' ) ) {						// Joomla 1.5 :
				$lang						=	JFactory::getLanguage();
				$lang->load( 'com_users' );
		}

		$pseudoFields						=	array();

		//Implementing Joomla's new user parameters such as editor
		$ui									=	$_CB_framework->getUi();

		$userParams							=	$this->_getUserParams( $ui, $user );

		if ( is_array( $userParams ) && ( count( $userParams ) > 0 ) && ( ( $ui == 2 ) || ( ( isset( $ueConfig['frontend_userparams'] ) ) ? ( $ueConfig['frontend_userparams'] == 1 ) : in_array( $_CB_framework->getCfg( 'frontend_userparams' ), array( '1', null ) ) ) ) ) {
			if ( $ui == 1 ) {
				$excludeParams					=	explode( '|*|', $field->params->get( 'hide_userparams' ) );
			} else {
				$excludeParams					=	array();
			}

			//Loop through each parameter and prepare rendering appropriately.
			foreach ( $userParams as $k => $userParam ) {
				if ( checkJversion() >= 2 ) {
					$nameId						=	( isset( $userParam[4] ) ? $userParam[4] : null );
				} else {
					$nameId						=	( isset( $userParam[5] ) ? $userParam[5] : null );
				}

				if ( ( ! $excludeParams ) || ( ! $nameId ) || ( $nameId && ( ! in_array( $nameId, $excludeParams ) ) ) ) {
					$paramField						=	new FieldTable( $field->getDbo() );
					$paramField->title				=	$userParam[0];
					$paramField->_html				=	$userParam[1];
					$paramField->description		=	( isset( $userParam[2] ) && class_exists("JText") ? JText::_( $userParam[2] ) : null );
					$paramField->name				=	( isset( $userParam[3] ) && class_exists("JText") ? JText::_( $userParam[3] ) : null );		// very probably wrong!
					$paramField->fieldid			=	'userparam_' . $k;
					$paramField->type				=	'param';		// this is for cb_ftparam class to be correct.

					if ( ! preg_match( '/<(?:input|select|textarea)[^>]*class[^>]*>/i', $paramField->_html ) ) {
						$paramField->_html			=	preg_replace( '/<(input|select|textarea)/i', '<$1 class="form-control"', $paramField->_html );
					}

					$pseudoFields[]					=	$paramField;
				}
			}
		}

		if ( $ui == 2 ) {
			$i_am_super_admin				=	Application::MyUser()->isSuperAdmin();
			$canBlockUser					=	Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.edit.state', 'com_users' );
			$canEmailEvents					=	( ( $user->id == 0 ) && $canBlockUser )
												|| Application::User( (int) $user->id )->isAuthorizedToPerformActionOnAsset( 'core.edit.state', 'com_users' )
												|| Application::User( (int) $user->id )->canViewAccessLevel( Application::Config()->get( 'moderator_viewaccesslevel', 3, \CBLib\Registry\GetterInterface::INT ) );

			$lists							=	array();

			if ( $canBlockUser ) {

				// ensure user can't add group higher than themselves
				$gtree						=	$_CB_framework->acl->get_groups_below_me();

				if ( ( ! $i_am_super_admin )
					&& $user->id
					&& Application::User( (int) $user->id )->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_users' )
					&& ( Application::User( (int) $user->id )->isAuthorizedToPerformActionOnAsset( 'core.edit', 'com_users' )
						 ||  Application::User( (int) $user->id )->isAuthorizedToPerformActionOnAsset( 'core.edit.state', 'com_users' )
					   )
				)
				{
					$disabled				=	' disabled="disabled"';
				} else {
					$disabled				=	'';
				}
				if ( $user->id ) {
					$strgids				=	array_map( 'strval', Application::User( (int) $user->id )->getAuthorisedGroups( false ) );
				} else {
					$strgids				=	(string) $_CB_framework->getCfg( 'new_usertype' );
				}
				$lists['gid']				=	moscomprofilerHTML::selectList( $gtree, 'gid[]', 'class="form-control" size="11" multiple="multiple"' . $disabled, 'value', 'text', $strgids, 2, false, null, false );

				// build the html select list
				$lists['block']					=	moscomprofilerHTML::yesnoSelectList( 'block', 'class="form-control"', (string) $user->block );

				$list_banned					=	array();
				$list_banned[]					=	moscomprofilerHTML::makeOption( '1', CBTxt::T( 'Banned' ) );
				$list_banned[]					=	moscomprofilerHTML::makeOption( '2', CBTxt::T( 'Pending' ) );
				$list_banned[]					=	moscomprofilerHTML::makeOption( '0', CBTxt::T( 'Active' ) );
				$lists['banned']				=	moscomprofilerHTML::selectList( $list_banned, 'banned', 'class="form-control"', 'value', 'text', (string) $user->banned, 2, false, null, false );

				$list_approved					=	array();
				$list_approved[]				=	moscomprofilerHTML::makeOption( '2', CBTxt::T( 'Rejected' ) );
				$list_approved[]				=	moscomprofilerHTML::makeOption( '0', CBTxt::T( 'Pending' ) );
				$list_approved[]				=	moscomprofilerHTML::makeOption( '1', CBTxt::T( 'Approved' ) );
				$lists['approved']				=	moscomprofilerHTML::selectList( $list_approved, 'approved', 'class="form-control"', 'value', 'text', (string) $user->approved, 2, false, null, false );

				$lists['confirmed']				=	moscomprofilerHTML::yesnoSelectList( 'confirmed', 'class="form-control"', (string) $user->confirmed, CBTxt::T( 'Confirmed' ), CBTxt::T( 'Pending' ) );
				// build the html select list
				$lists['sendEmail']				=	moscomprofilerHTML::yesnoSelectList( 'sendEmail', 'class="form-control"', (string) $user->sendEmail );


				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Group';								// For translation parser:  CBTxt::T( 'Group' );
				$paramField->_html			=	$lists['gid'];
				$paramField->description	=	'';
				$paramField->name			=	'gid';
				$pseudoFields[]				=	$paramField;

				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Block User';							// For translation parser:  CBTxt::T( 'Block User' );
				$paramField->_html			=	$lists['block'];
				$paramField->description	=	'';
				$paramField->name			=	'block';
				$pseudoFields[]				=	$paramField;

				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Approve User';								// For translation parser:  CBTxt::T( 'Approve User' );
				$paramField->_html			=	$lists['approved'];
				$paramField->description	=	'';
				$paramField->name			=	'approved';
				$pseudoFields[]				=	$paramField;

				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Confirm User';								// For translation parser:  CBTxt::T( 'Confirm User' );
				$paramField->_html			=	$lists['confirmed'];
				$paramField->description	=	'';
				$paramField->name			=	'confirmed';
				$pseudoFields[]				=	$paramField;

				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Ban User';								// For translation parser:  CBTxt::T( 'Ban User' );
				$paramField->_html			=	$lists['banned'];
				$paramField->description	=	'';
				$paramField->name			=	'banned';
				$pseudoFields[]				=	$paramField;

				$paramField						=	new FieldTable( $field->getDbo() );
				$paramField->title				=	'Receive Moderator Emails';				// For translation parser:  CBTxt::T( 'Receive Moderator Emails' );
				if ($canEmailEvents || $user->sendEmail) {
					$paramField->_html			=	$lists['sendEmail'];
				} else {
					$paramField->_html			=	CBTxt::T( 'No (User\'s group-level doesn\'t allow this)' )
												.	'<input type="hidden" name="sendEmail" value="0" />';
				}
				$paramField->description		=	'';
				$paramField->name				=	'sendEmail';
				$pseudoFields[]					=	$paramField;
			}

			if( $user->id) {
				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Register Date';								// For translation parser:  CBTxt::T( 'Register Date' );
				$paramField->_html			=	cbFormatDate( $user->registerDate );
				$paramField->description	=	'';
				$paramField->name			=	'registerDate';
				$pseudoFields[]				=	$paramField;

				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Last Visit Date';								// For translation parser:  CBTxt::T( 'Last Visit Date' );
				$paramField->_html			=	cbFormatDate( $user->lastvisitDate );
				$paramField->description	=	'';
				$paramField->name			=	'lastvisitDate';
				$pseudoFields[]				=	$paramField;

				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Last Reset Time';								// For translation parser:  CBTxt::T( 'Last Reset Time' );
				$paramField->_html			=	cbFormatDate( $user->lastResetTime );
				$paramField->description	=	'';
				$paramField->name			=	'lastResetTime';
				$pseudoFields[]				=	$paramField;

				$paramField					=	new FieldTable( $field->getDbo() );
				$paramField->title			=	'Password Reset Count';							// For translation parser:  CBTxt::T( 'Password Reset Count' );
				$paramField->_html			=	(int) $user->resetCount;
				$paramField->description	=	'';
				$paramField->name			=	'resetCount';
				$pseudoFields[]				=	$paramField;
			}
		}

		switch ( $output ) {
			case 'htmledit':
				foreach ( $pseudoFields as $paramField ) {
					$paramField->required	=	$this->_isRequired( $field, $user, $reason );
					$paramField->profile	=	$field->profile;
					$paramField->params		=	$field->params;
					$results				.=	parent::getFieldRow( $paramField, $user, $output, $formatting, $reason, $list_compare_types );
				}
				unset( $pseudoFields );
				return $results;
				break;

			default:
				return null;
				break;
		}
	}
	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		switch ( $output ) {
			case 'htmledit':
				return $field->_html . $this->_fieldIconsHtml( $field, $user, $output, $reason, 'input', 'text', $field->_html, '', null, true, $this->_isRequired( $field, $user, $reason ) && ! $this->_isReadOnly( $field, $user, $reason ) );
				break;

			default:
				return null;
				break;
		}
	}
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		global $_CB_framework, $ueConfig;

		// Nb. frontend registration setting of usertype, gid, block, sendEmail, confirmed, approved
		// are handled in UserTable::bindSafely() so they are available to other plugins.

		// this is (for now) handled in the core of CB... except params and block/email/approved/confirmed:

		if ( $_CB_framework->getUi() == 2 ) {
			$canBlockUser					=	Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.edit.state', 'com_users' );
			if ( $canBlockUser ) {
				$user->gids					=	cbGetParam( $postdata, 'gid', array( 0 ) );

				if ( isset( $postdata['block'] ) ) {
					$user->block			=	cbGetParam( $postdata, 'block', 0 );
				}
				if ( isset( $postdata['approved'] ) ) {
					$user->approved			=	cbGetParam( $postdata, 'approved', 0 );
				}
				if ( isset( $postdata['confirmed'] ) ) {
					$user->confirmed		=	cbGetParam( $postdata, 'confirmed', 0 );
				}
				if ( isset( $postdata['banned'] ) ) {
					$banned					=	cbGetParam( $postdata, 'banned', 0 );

					if ( $banned != $user->banned ) {
						if ( $banned == 1 ) {
							$user->bannedby			=	(int) $_CB_framework->myId();
							$user->banneddate		=	$_CB_framework->getUTCDate();
						} elseif ( $banned == 0 ) {
							$user->unbannedby		=	(int) $_CB_framework->myId();
							$user->unbanneddate		=	$_CB_framework->getUTCDate();
						}
					}

					$user->banned			=	$banned;
				}
				if ( isset( $postdata['sendEmail'] ) ) {
					$user->sendEmail		=	cbGetParam( $postdata, 'sendEmail', 0 );
				}
			}
		}

		if (	( $_CB_framework->getUi() == 2 )
			||	( ( isset( $ueConfig['frontend_userparams'] ) ) ? ( $ueConfig['frontend_userparams'] == 1 ) : in_array( $_CB_framework->getCfg( "frontend_userparams" ), array( '1', null) ) ) )
		{
			// save user params
			$params							=	cbGetParam( $_POST, 'params', null );			//TBD: verify if stripslashes is needed here: it might be needed...leaving as is for now.
			if ( $params != null ) {
				if ( is_array( $params ) ) {
					if ( checkJversion() >= 2 ) {
						$registry			=	new JRegistry( $params );
						$value				=	$registry->toArray();
						$valueString		=	$registry->toString();
					} else {
						$txt				=	array();
						foreach ( $params as $k => $v) {
								$txt[]			=	$k . '=' . $v;
						}
						$value				=	implode( "\n", $txt );
						$valueString		=	$value;
					}
					if ( ( (string) $user->params ) !== (string) $valueString ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->params, $value );
					}
					$user->params			=	$value;
				}
			}
		} else {
			if ( checkJversion() >= 2 ) {
				// Joomla 2.5 has a bug, where an untouched $user->params string is not saved correctly: So let's workaround this here:
				if ( $user->id ) {
					$juser		=	JUser::getInstance( $user->id );
				} else {
					$juser		=	JUser::getInstance();
				}
				$user->params	=	$juser->getParameters( true )->toArray();
			}
		}
	}
	/**
	 * Retrieve joomla standard user parameters so that they can be displayed in user edit mode.
	 *
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  UserTable  $user      the user being displayed
	 * @param  string     $name      Name of variable
	 * @return array                 of user parameter attributes (title,value)
	 */
	function _getUserParams( /** @noinspection PhpUnusedParameterInspection */ $ui, $user,$name = "params" ) {
		global $_CB_framework;

		$juser				=	$_CB_framework->_getCmsUserObject( $user->id );

		$result				=	array();

		if ( checkJversion( '3.0+' ) ) {
			// Include jQuery
			JHtml::_('jquery.framework');
		}

		jimport( 'joomla.form.form' );

		JForm::addFormPath( JPATH_ADMINISTRATOR . '/components/com_users/models/forms' );

		$form				=	JForm::getInstance( 'com_users.params', 'user', array( 'load_data' => true ) );
		$params				=	$juser->getParameters( true )->toArray();

		if ( $params ) foreach ( $params as $k => $v ) {
			$form->setValue( $k, 'params', $v );
		}

		$fields				=	$form->getFieldset( 'settings' );

		if ( $fields ) foreach ( $fields as $fieldId => $field ) {
			$admin_field	=	( strpos( $field->name, 'admin' ) || strpos( $field->name, 'help' ) );

			if ( ( $admin_field && ( $juser->authorise( 'canManageUsers' ) || ( ! $user->id ) ) ) || ( ! $admin_field ) ) {
				$result[]	=	array( $field->title, $field->input, $field->description, $field->name, $fieldId );
			}
		}
		return $result;
	}
}

class CBfield_file extends cbFieldHandler {

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$value					=	$user->get( $field->name );

		switch ( $output ) {
			case 'html':
			case 'rss':
				$return			=	$this->formatFieldValueLayout( $this->_fileLivePath( $field, $user, $reason ), $reason, $field, $user );
				break;
			case 'htmledit':
				if ( $reason == 'search' ) {
					$choices	=	array();
					$choices[]	=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'UE_NO_PREFERENCE', 'No preference' ) );
					$choices[]	=	moscomprofilerHTML::makeOption( '1', CBTxt::T( 'Has a file' ) );
					$choices[]	=	moscomprofilerHTML::makeOption( '0', CBTxt::T( 'Has no file' ) );
					$html		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', $value, '', $choices );
					$return		=	$this->_fieldSearchModeHtml( $field, $user, $html, 'singlechoice', $list_compare_types );
				} else {
					$return		=	$this->formatFieldValueLayout( $this->_htmlEditForm( $field, $user, $reason ), $reason, $field, $user );
				}
				break;
			default:
				$fileUrl		=	$this->_fileLivePath( $field, $user, $reason, false );
				$return			=	$this->_formatFieldOutput( $field->name, $fileUrl, $output );
				break;
		}

		return $return;
	}

	/**
	 * Direct access to field for custom operations, like for Ajax
	 *
	 * WARNING: direct unchecked access, except if $user is set, then check
	 * that the logged-in user has rights to edit that $user.
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  array       $postdata
	 * @param  string      $reason     'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string                  Expected output.
	 */
	public function fieldClass( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework;

		if ( cbGetParam( $_GET, 'function', null ) == 'download' ) {
			$col						=	$field->name;
			$file						=	$user->$col;

			if ( $file != null ) {
				if ( $reason == 'edit' ) {
					$redirect_url		=	$_CB_framework->userProfileEditUrl( $user->id, false );
				} elseif ( $reason == 'list' ) {
					$redirect_url		=	$_CB_framework->userProfilesListUrl( cbGetParam( $_REQUEST, 'listid', 0 ), false );
				} elseif ( $reason == 'register' ) {
					$redirect_url		=	$_CB_framework->viewUrl( 'registers', false );
				} else {
					$redirect_url		=	$_CB_framework->userProfileUrl( $user->id, false );
				}

				$clean_file				=	preg_replace( '/[^-a-zA-Z0-9_.]/', '', $file );
				$file_path				=	$_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/plug_cbfilefield/' . (int) $user->id . '/' . $clean_file;

				if ( ! file_exists( $file_path ) ) {
					cbRedirect( $redirect_url, CBTxt::T( 'File failed to download! Error: File not found' ), 'error' );
					exit();
				}

				$file_ext				=	strtolower( pathinfo( $clean_file, PATHINFO_EXTENSION ) );

				if ( ! $file_ext ) {
					cbRedirect( $redirect_url, CBTxt::T( 'File failed to download! Error: Unknown extension' ), 'error' );
					exit();
				}

				$file_name				=	substr( rtrim( pathinfo( $clean_file, PATHINFO_BASENAME ), '.' . $file_ext ), 0, -14 ) . '.' . $file_ext;

				if ( ! $file_name ) {
					cbRedirect( $redirect_url, CBTxt::T( 'File failed to download! Error: File not found' ), 'error' );
					exit();
				}

				$file_mime				=	cbGetMimeFromExt( $file_ext );

				if ( $file_mime == 'application/octet-stream' ) {
					cbRedirect( $redirect_url, CBTxt::T( 'File failed to download! Error: Unknown MIME' ), 'error' );
					exit();
				}

				$file_size				=	@filesize( $file_path );
				$file_modified			=	$_CB_framework->getUTCDate( 'r', filemtime( $file_path ) );

				while ( @ob_end_clean() );

				if ( ini_get( 'zlib.output_compression' ) ) {
					ini_set( 'zlib.output_compression', 'Off' );
				}

				if ( function_exists( 'apache_setenv' ) ) {
					apache_setenv( 'no-gzip', '1' );
				}

				header( "Content-Type: $file_mime" );
				header( 'Content-Disposition: ' . ( $field->params->get( 'fieldFile_force', 0 ) ? 'attachment' : 'inline' ) . '; filename="' . $file_name . '"; modification-date="' . $file_modified . '"; size=' . $file_size .';' );
				header( "Content-Transfer-Encoding: binary" );
				header( "Expires: 0" );
				header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
				header( "Pragma: public" );
				header( "Content-Length: $file_size" );

				if ( ! ini_get( 'safe_mode' ) ) {
					@set_time_limit( 0 );
				}

				$handle			=	fopen( $file_path, 'rb' );

				if ( $handle === false ) {
					exit();
				}

				$chunksize		=	( 1 * ( 1024 * 1024 ) );

				while ( ! feof( $handle ) ) {
					$buffer		=	fread( $handle, $chunksize );
					echo $buffer;
					@ob_flush();
					flush();
				}

				fclose( $handle );
				exit();
			}
		} else {
			parent::fieldClass( $field, $user, $postdata, $reason ); // Performs spoof check
		}

		return null;
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_CB_database;

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		$col					=	$field->name;
		$col_choice				=	$col . '__choice';
		$col_file				=	$col . '__file';
		$choice					=	stripslashes( cbGetParam( $postdata, $col_choice ) );

		switch ( $choice ) {
			case 'upload':
				$value			=	( isset( $_FILES[$col_file] ) ? $_FILES[$col_file] : null );

				$this->validate( $field, $user, $choice, $value, $postdata, $reason );
				break;
			case 'delete':
				if ( $user->id && ( $user->$col != null ) && ( $user->$col != '' ) ) {
					if ( isset( $user->$col ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, '' );
					}

					$this->deleteFiles( $user, $user->$col );

					$user->$col	=	null;

					$query		=	'UPDATE ' . $_CB_database->NameQuote( $field->table )
								.	"\n SET " . $_CB_database->NameQuote( $col ) . " = NULL"
								.	', ' . $_CB_database->NameQuote( 'lastupdatedate' ) . ' = ' . $_CB_database->Quote( $_CB_framework->dateDbOfNow() )
								.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $user->id;
					$_CB_database->setQuery( $query );
					$_CB_database->query();
				}
				break;
			default:
				$value			=	$user->get( $col );

				$this->validate( $field, $user, $choice, $value, $postdata, $reason );
				break;
		}
	}

	/**
	 * Mutator:
	 * Prepares field data commit
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function commitFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_PLUGINS, $_FILES;

		$col						=	$field->name;
		$col_choice					=	$col . '__choice';
		$col_file					=	$col . '__file';
		$choice						=	stripslashes( cbGetParam( $postdata, $col_choice ) );

		switch ( $choice ) {
			case 'upload':
				$value				=	( isset( $_FILES[$col_file] ) ? $_FILES[$col_file] : null );

				if ( $this->validate( $field, $user, $choice, $value, $postdata, $reason ) ) {
					$_PLUGINS->loadPluginGroup( 'user' );

					$_PLUGINS->trigger( 'onBeforeUserFileUpdate', array( &$user, &$value['tmp_name'] ) );

					if ( $_PLUGINS->is_errors() ) {
						$this->_setErrorMSG( $_PLUGINS->getErrorMSG() );
						return;
					}

					$path			=	$_CB_framework->getCfg( 'absolute_path' );
					$index_path		=	$path . '/components/com_comprofiler/plugin/user/plug_cbfilefield/index.html';
					$files_path		=	$path . '/images/comprofiler/plug_cbfilefield';
					$file_path		=	$files_path . '/' . (int) $user->id;

					if ( ! is_dir( $files_path ) ) {
						$oldmask	=	@umask( 0 );

						if ( @mkdir( $files_path, 0755, true ) ) {
							@umask( $oldmask );
							@chmod( $files_path, 0755 );

							if ( ! file_exists( $files_path . '/index.html' ) ) {
								@copy( $index_path, $files_path . '/index.html' );
								@chmod( $files_path . '/index.html', 0755 );
							}
						} else {
							@umask( $oldmask );
						}
					}

					if ( ! file_exists( $files_path . '/.htaccess' ) ) {
						file_put_contents( $files_path . '/.htaccess', 'deny from all' );
					}

					if ( ! is_dir( $file_path ) ) {
						$oldmask	=	@umask( 0 );

						if ( @mkdir( $file_path, 0755, true ) ) {
							@umask( $oldmask );
							@chmod( $file_path, 0755 );

							if ( ! file_exists( $file_path . '/index.html' ) ) {
								@copy( $index_path, $file_path . '/index.html' );
								@chmod( $file_path . '/index.html', 0755 );
							}
						} else {
							@umask( $oldmask );
						}
					}

					$uploaded_name	=	preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_FILENAME ) );
					$uploaded_ext	=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_EXTENSION ) ) );
					$newFileName	=	uniqid( $uploaded_name . '_' ). '.' . $uploaded_ext;

					if ( ! move_uploaded_file( $value['tmp_name'], $file_path . '/'. $newFileName ) ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'CBFile-failed to upload file: %s' ), $newFileName ) );
						return;
					} else {
						@chmod( $file_path . '/' . $value['tmp_name'], 0755 );
					}

					if ( isset( $user->$col ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, '' );
					}

					if ( isset( $user->$col ) && ( $user->$col != '' ) ) {
						$this->deleteFiles( $user, $user->$col );
					}

					$user->$col		=	$newFileName;

					$_PLUGINS->trigger( 'onAfterUserFileUpdate', array( &$user, $newFileName ) );
				}
				break;
		}
	}

	/**
	 * Mutator:
	 * Prepares field data rollback
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function rollbackFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_FILES;

		$col			=	$field->name;
		$col_choice		=	$col . '__choice';
		$col_file		=	$col . '__file';

		$choice			=	stripslashes( cbGetParam( $postdata, $col_choice ) );

		switch ( $choice ) {
			case 'upload':
				$value	=	( isset( $_FILES[$col_file] ) ? $_FILES[$col_file] : null );

				if ( $this->validate( $field, $user, $choice, $value, $postdata, $reason ) ) {
					$this->deleteFiles( $user, $user->$col );
				}
				break;
		}
	}

	/**
	 * outputs a secure list of allowed file extensions
	 *
	 * @param  string  $extensions
	 * @return array
	 */
	function allowedExtensions( $extensions = 'zip,rar,doc,pdf,txt,xls' ) {
		$allowed			=	explode( ',', $extensions );

		if ( $allowed ) {
			$not_allowed	=	array( 'php', 'php3', 'php4', 'php5', 'asp', 'exe', 'py' );

			foreach ( $not_allowed as $extension ) {
				$key		=	array_search( $extension, $allowed );

				if ( $key ) {
					unset( $allowed[$key] );
				}
			}
		}

		return $allowed;
	}

	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save user edit, 'register' for save registration
	 * @return boolean                            True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		$isRequired							=	$this->_isRequired( $field, $user, $reason );

		switch ( $columnName ) {
			case 'upload':
				if ( ! isset( $value['tmp_name'] ) || empty( $value['tmp_name'] ) || ( $value['error'] != 0 ) || ( ! is_uploaded_file( $value['tmp_name'] ) ) ) {
					if ( $isRequired ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Please select a file before uploading' ) );
					}

					return false;
				} else {
					$upload_size_limit_max	=	(int) $field->params->get( 'fieldValidateFile_sizeMax', 1024 );
					$upload_size_limit_min	=	(int) $field->params->get( 'fieldValidateFile_sizeMin', 0 );
					$upload_ext_limit		=	$this->allowedExtensions( $field->params->get( 'fieldValidateFile_types', 'zip,rar,doc,pdf,txt,xls' ) );

					$uploaded_name			=	preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_FILENAME ) );

					if ( ! $uploaded_name ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Please select a file before uploading' ) );
						return false;
					}

					$uploaded_ext			=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_EXTENSION ) ) );

					if ( ( ! $uploaded_ext ) || ( ! in_array( $uploaded_ext, $upload_ext_limit ) ) ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'Please upload only %s' ), implode( ', ', $upload_ext_limit ) ) );
						return false;
					}

					$uploaded_size			=	$value['size'];

					if ( ( $uploaded_size / 1024 ) > $upload_size_limit_max ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'The file size exceeds the maximum of %s' ), $this->formattedFileSize( $upload_size_limit_max * 1024 ) ) );
						return false;
					}

					if ( ( $uploaded_size / 1024 ) < $upload_size_limit_min ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'The file is too small, the minimum is %s' ), $this->formattedFileSize( $upload_size_limit_min * 1024 ) ) );
						return false;
					}
				}
				break;
			default:
				$valCol						=	$field->name;

				if ( $isRequired && ( ( ! $user ) || ( ! isset( $user->$valCol ) ) || ( ! $user->$valCol ) ) ) {
					if ( ! $value ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_FIELDREQUIRED', 'This Field is required' ) );
						return false;
					}
				}
				break;
		}

		return true;
	}

	/**	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		$query					=	array();
		$searchMode				=	$this->_bindSearchMode( $field, $searchVals, $postdata, 'none', $list_compare_types );
		$col					=	$field->name;
		$value					=	cbGetParam( $postdata, $col );

		if ( $value === '0' ) {
			$value				=	0;
		} elseif ( $value == '1' ) {
			$value				=	1;
		} else {
			$value				=	null;
		}

		if ( $value !== null ) {
			$searchVals->$col	=	$value;

			// When is not advanced search is used we need to invert our search:
			if ( $searchMode == 'isnot' ) {
				if ( $value === 0 ) {
					$value		=	1;
				} elseif ( $value == 1 ) {
					$value		=	0;
				}
			}

			$sql				=	new cbSqlQueryPart();
			$sql->tag			=	'column';
			$sql->name			=	$col;
			$sql->table			=	$field->table;
			$sql->type			=	'sql:field';
			$sql->operator		=	$value ? 'IS NOT' : 'IS';
			$sql->value			=	'NULL';
			$sql->valuetype		=	'const:null';
			$sql->searchmode	=	$searchMode;

			$query[]			=	$sql;
		}

		return $query;
	}

	/**
	 * Returns full URL of the file
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason
	 * @param  bool        $html
	 * @return null|string
	 */
	function _fileLivePath( &$field, &$user, $reason, $html = true ) {
		global $_CB_framework;

		$oValue					=	null;

		if ( $user && $user->id ) {
			$fieldName			=	$field->get( 'name' );
			$value				=	$user->get( $fieldName );
			$fileName			=	null;

			if ( $value != null ) {
				$cleanFile		=	preg_replace( '/[^-a-zA-Z0-9_.]/', '', $value );
				$fileExt		=	strtolower( pathinfo( $cleanFile, PATHINFO_EXTENSION ) );
				$fileName		=	substr( rtrim( pathinfo( $cleanFile, PATHINFO_BASENAME ), '.' . $fileExt ), 0, -14 ) . '.' . $fileExt;
				$oValue			=	'/images/comprofiler/plug_cbfilefield/' . (int) $user->id . '/' . $cleanFile;
			}

			if ( $oValue ) {
				$oValue			=	'index.php?option=com_comprofiler&view=fieldclass&field=' . urlencode( $fieldName ) . '&function=download&user=' . (int) $user->id . '&reason=' . urlencode( $reason );

				if ( $_CB_framework->getUi() == 2 ) {
					$oValue		=	$_CB_framework->backendUrl( $oValue, true );
				} else {
					$oValue		=	cbSef( $oValue, true );
				}

				if ( $html ) {
					$oValue		=	' <a href="' . $oValue . '" title="' . htmlspecialchars( CBTxt::T( 'Click or right-click filename to download' ) ) . '" target="_blank" rel="nofollow">' . $fileName . '</a>';
				}
			}
		}

		return $oValue;
	}

	/**
	 *
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason             'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  boolean     $displayFieldIcons
	 * @return string                          HTML: <tag type="$type" value="$value" xxxx="xxx" yy="y" />
	 */
	function _htmlEditForm( &$field, &$user, $reason, $displayFieldIcons = true ) {
		global $_CB_framework;

		$fieldName							=	$field->get( 'name' );
		$value								=	$user->get( $fieldName );
		$required							=	$this->_isRequired( $field, $user, $reason );

		$uploadSizeLimitMax					=	$field->params->get( 'fieldValidateFile_sizeMax', 1024 );
		$uploadSizeLimitMin					=	$field->params->get( 'fieldValidateFile_sizeMin', 0 );
		$uploadExtLimit						=	$this->allowedExtensions( $field->params->get( 'fieldValidateFile_types', 'zip,rar,doc,pdf,txt,xls' ) );
		$restrictions						=	array();

		if ( $uploadExtLimit ) {
			$restrictions[]					=	CBTxt::Th( 'FILE_UPLOAD_LIMITS_EXT', 'Your file must be of [ext] type.', array( '[ext]' => implode( ', ', $uploadExtLimit ) ) );
		}

		if ( $uploadSizeLimitMin ) {
			$restrictions[]					=	CBTxt::Th( 'FILE_UPLOAD_LIMITS_MIN', 'Your file should exceed [size].', array( '[size]' => $this->formattedFileSize( $uploadSizeLimitMin * 1024 ) ) );
		}

		if ( $uploadSizeLimitMax ) {
			$restrictions[]					=	CBTxt::Th( 'FILE_UPLOAD_LIMITS_MAX', 'Your file should not exceed [size].', array( '[size]' => $this->formattedFileSize( $uploadSizeLimitMax * 1024 ) ) );
		}

		$existingFile						=	( $user->id ? ( ( $value != null ) ? true : false ) : false );
		$choices							=	array();

		if ( ( $reason == 'register' ) || ( ( $reason == 'edit' ) && ( $user->id == 0 ) ) ) {
			if ( $required == 0 ) {
				$choices[]					=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No file' ) );
			}
		} else {
			if ( $existingFile || ( $required == 0 ) ) {
				$choices[]					=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No change of file' ) );
			}
		}

		$choices[]							=	moscomprofilerHTML::makeOption( 'upload', ( $existingFile ? CBTxt::T( 'Upload new file' ) : CBTxt::T( 'Upload file' ) ) );

		if ( $existingFile && ( $required == 0 ) ) {
			$choices[]						=	moscomprofilerHTML::makeOption( 'delete', CBTxt::T( 'Remove file' ) );
		}

		$return								=	null;

		if ( ( $reason != 'register' ) && ( $user->id != 0 ) ) {
			$return							.=	'<div class="form-group cb_form_line clearfix">' . $this->_fileLivePath( $field, $user, $reason ) . '</div>';
		}

		if ( count( $choices ) > 1 ) {
			static $functOut				=	false;

			$additional						=	' class="form-control"';

			if ( ( $_CB_framework->getUi() == 1 ) && ( $reason == 'edit' ) && $field->readonly ) {
				$additional					.=	' disabled="disabled"';
			}

			$translatedTitle				=	$this->getFieldTitle( $field, $user, 'html', $reason );
			$htmlDescription				=	$this->getFieldDescription( $field, $user, 'htmledit', $reason );
			$trimmedDescription				=	trim( strip_tags( $htmlDescription ) );

			$tooltip						=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, $additional ) : $additional );

			$return							.=	'<div class="form-group cb_form_line clearfix">'
											.		moscomprofilerHTML::selectList( $choices, $fieldName . '__choice', $tooltip, 'value', 'text', null, $required, true, null, false )
											.		$this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, 'select', '', null, '', array(), $displayFieldIcons, $required )
											.	'</div>';

			if ( ! $functOut ) {
				$js							=	"function cbslideFile( choice, name ) {"
											.		"if ( ( choice == '' ) || ( choice == 'delete' ) ) {"
											.			"$( '#cbfile_upload_' + name ).slideUp( 'slow' );"
											.			"$( '#cbfile_upload_' + name + ' input' ).prop( 'readonly', true );"
											.		"} else if ( choice == 'upload' ) {"
											.			"$( '#cbfile_upload_' + name ).slideDown( 'slow' );"
											.			"$( '#cbfile_upload_' + name + ' input' ).prop( 'readonly', false );"
											.		"}"
											.	"}";

				$_CB_framework->outputCbJQuery( $js );

				$functOut					=	true;
			}

			$js								=	"$( '#cbfile_upload_" . addslashes( $fieldName ) . "' ).hide();"
											.	"$( '#cbfile_upload_" . addslashes( $fieldName ) . " input' ).prop( 'readonly', true );"
											.	"$( '#" . addslashes( $fieldName ) . "__choice' ).on( 'click change', function() {"
											.		"cbslideFile( $( this ).val(), '" . addslashes( $fieldName ) . "' );"
											.	"}).change();";

			$_CB_framework->outputCbJQuery( $js );
		} else {
			$return							.=	'<input type="hidden" name="' . htmlspecialchars( $fieldName ) . '__choice" value="' . htmlspecialchars( $choices[0]->value ) . '" />';
		}

		$validationAttributes				=	array();
		$validationAttributes[]				=	cbValidator::getRuleHtmlAttributes( 'extension', implode( ',', $uploadExtLimit ) );

		if ( $uploadSizeLimitMin || $uploadSizeLimitMax ) {
			$validationAttributes[]			=	cbValidator::getRuleHtmlAttributes( 'filesize', array( $uploadSizeLimitMin, $uploadSizeLimitMax, 'KB' ) );
		}

		$return								.=	'<div id="cbfile_upload_' . htmlspecialchars( $fieldName ) . '" class="form-group cb_form_line clearfix">'
											.		( $restrictions ? '<div class="help-block">' . implode( ' ', $restrictions ) . '</div>' : null )
											.		'<div>'
											.			CBTxt::T( 'Select file' ) . ' <input type="file" name="' . htmlspecialchars( $fieldName ) . '__file" value="" class="form-control' . ( $required == 1 ? ' required' : null ) . '"' . implode( ' ', $validationAttributes ) . ' />'
											.			( count( $choices ) <= 0 ? $this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, 'select', '', null, '', array(), $displayFieldIcons, $required ) : null )
											.		'</div>'
											.		'<div class="help-block">';

		if ( $field->params->get( 'video_terms', 0 ) ) {
			$termsOutput					=	$field->params->get( 'terms_output', 'url' );
			$termsType						=	CBTxt::T( $field->params->get( 'terms_type', null ) );
			$termsDisplay					=	$field->params->get( 'terms_display', 'modal' );
			$termsURL						=	$field->params->get( 'terms_url', null );
			$termsText						=	$field->params->get( 'terms_text', null );
			$termsWidth						=	(int) $field->params->get( 'terms_width', 400 );
			$termsHeight					=	(int) $field->params->get( 'terms_height', 200 );

			if ( ! $termsType ) {
				$termsType					=	CBTxt::T( 'TERMS_AND_CONDITIONS', 'Terms and Conditions' );
			}

			if ( ! $termsHeight ) {
				$termsHeight				=	200;
			}

			if ( ( ( $termsOutput == 'url' ) && $termsURL ) || ( ( $termsOutput == 'text' ) && $termsText ) ) {
				if ( $termsDisplay == 'iframe' ) {
					if ( $termsOutput == 'url' ) {
						$return				.=			'<iframe class="cbTermsFrameURL" height="' . $termsHeight . '" width="' . ( $termsWidth ? $termsWidth : '100%' ) . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
					} else {
						$return				.=			'<div class="cbTermsFrameText" style="height:' . $termsHeight . 'px;width:' . ( $termsWidth ? $termsWidth . 'px' : '100%' ) . ';overflow:auto;">' . CBTxt::T( $termsText ) . '</div>';
					}
				}

				if ( $termsDisplay != 'iframe' ) {
					$attributes				=	' class="cbTermsLink"';

					if ( ( $termsOutput == 'text' ) && ( $termsDisplay == 'window' ) ) {
						$termsDisplay		=	'modal';
					}

					if ( $termsDisplay == 'modal' ) {
						if ( ! $termsWidth ) {
							$termsWidth		=	400;
						}

						if ( $termsOutput == 'url' ) {
							$tooltip		=	'<iframe class="cbTermsModalURL" height="' . $termsHeight . '" width="' . $termsWidth . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
						} else {
							$tooltip		=	'<div class="cbTermsModalText" style="height:' . $termsHeight . 'px;width:' . $termsWidth . 'px;overflow:auto;">' . CBTxt::T( $termsText ) . '</div>';
						}

						$url				=	'javascript:void(0);';
						$attributes			.=	' ' . cbTooltip( $_CB_framework->getUi(), $tooltip, $termsType, 'auto', null, null, null, 'data-hascbtooltip="true" data-cbtooltip-modal="true"' );
					} else {
						$url				=	htmlspecialchars( $termsURL );
						$attributes			.=	' target="_blank"';
					}

					$return					.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_FILE_URL_TERMS', 'By uploading, you certify that you have the right to distribute this file and that it does not violate the <a href="[url]"[attributes]>[type]</a>',
																	   array( '[url]' => $url, '[attributes]' => $attributes, '[type]' => $termsType )
																	 );
				} else {
					$return					.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_FILE_TERMS', 'By uploading, you certify that you have the right to distribute this file and that it does not violate the above [type].',
																	   array( '[type]' => $termsType )
																	 );
				}
			} else {
				$return						.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_FILE', 'By uploading, you certify that you have the right to distribute this file.' );
			}
		} else {
			$return							.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_FILE', 'By uploading, you certify that you have the right to distribute this file.' );
		}

		$return								.=		'</div>'
											.	'</div>';

		return $return;
	}

	/**
	 * Deletes file from users folder
	 *
	 * @param  UserTable  $user
	 * @param  string     $file
	 */
	function deleteFiles( $user, $file = null ) {
		global $_CB_framework;

		if ( ! is_object( $user ) ) {
			return;
		}

		$file_path	=	$_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/plug_cbfilefield/' . (int) $user->id . '/';

		if ( ! is_dir( $file_path ) ) {
			return;
		}

		if ( ! $file ) {
			if ( false !== ( $handle = opendir( $file_path ) ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file && ( ( $file != '.' ) && ( $file != '..' ) ) ) {
						@unlink( $file_path . $file );
					}
				}
				closedir( $handle );
			}

			if ( is_dir( $file_path ) ) {
				@rmdir( $file_path );
			}
		} else {
			if ( file_exists( $file_path . $file ) ) {
				@unlink( $file_path . $file );
			}
		}
	}

	/**
	 * Returns file size formatted from bytes
	 *
	 * @param int $bytes
	 * @return string
	 */
	private function formattedFileSize( $bytes )
	{
		if ( $bytes >= 1099511627776 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_TB', '%%COUNT%% TB|%%COUNT%% TBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1099511627776, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1073741824 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_GB', '%%COUNT%% GB|%%COUNT%% GBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1073741824, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1048576 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_MB', '%%COUNT%% MB|%%COUNT%% MBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1048576, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1024 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_KB', '%%COUNT%% KB|%%COUNT%% KBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1024, 2, '.', '' ) ) );
		} else {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_B', '%%COUNT%% B|%%COUNT%% Bs', array( '%%COUNT%%' => (float) number_format( $bytes, 2, '.', '' ) ) );
		}

		return $size;
	}
}

class CBfield_video extends CBfield_text {

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$return						=	null;

		$value						=	$user->get( $field->get( 'name' ) );

		switch ( $output ) {
			case 'html':
			case 'rss':
				if ( $value ) {
					$return			=	$this->getEmbed( $field, $user, $value, $reason );
				}

				$return				=	$this->formatFieldValueLayout( $return, $reason, $field, $user );
				break;
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'htmledit':
				// fallthrough is on purpose encase we don't allow uploads
				if ( $field->params->get( 'video_allow_uploads', 1 ) ) {
					if ( $reason == 'search' ) {
						$choices	=	array();
						$choices[]	=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'UE_NO_PREFERENCE', 'No preference' ) );
						$choices[]	=	moscomprofilerHTML::makeOption( '1', ( $field->params->get( 'video_allow_links', 1 ) ? CBTxt::T( 'Has video file or link' ) : CBTxt::T( 'Has a video file' ) ) );
						$choices[]	=	moscomprofilerHTML::makeOption( '0', ( $field->params->get( 'video_allow_links', 1 ) ? CBTxt::T( 'Has no video file or link' ) : CBTxt::T( 'Has no video file' ) ) );

						$html		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', $value, '', $choices );

						$return		=	$this->_fieldSearchModeHtml( $field, $user, $html, 'singlechoice', $list_compare_types );
					} else {
						$return		=	$this->formatFieldValueLayout( $this->_htmlEditForm( $field, $user, $output, $reason ), $reason, $field, $user );
					}
					break;
				}
			default:
				$field->set( 'type', 'text' );

				$return				=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}

		return $return;
	}

	/**
	 * returns video embed based off video url
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $value
	 * @param  string       $reason
	 * @return null|string
	 */
	public function getEmbed( $field, $user, $value, $reason ) {
		global $_CB_framework;

		$domain						=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $value, PHP_URL_HOST ) );

		if ( ! $domain ) {
			$value					=	$_CB_framework->getCfg( 'live_site' ) . '/images/comprofiler/video/' . (int) $user->get( 'id' ) . '/' . urlencode( $value );
		}

		$embed						=	null;

		if ( $value ) {
			$currentScheme			=	( ( isset( $_SERVER['HTTPS'] ) && ( ! empty( $_SERVER['HTTPS'] ) ) && ( $_SERVER['HTTPS'] != 'off' ) ) ? 'https' : 'http' );
			$urlScheme				=	parse_url( $value, PHP_URL_SCHEME );

			if ( ! $urlScheme ) {
				$urlScheme			=	$currentScheme;
			}

			if ( ( $currentScheme == 'https' ) && ( $urlScheme != $currentScheme ) ) {
				$value				=	str_replace( 'http', 'https', $value );
			}

			if ( $reason != 'profile' ) {
				$width				=	(int) $field->params->get( 'video_thumbwidth', 400 );
			} else {
				$width				=	(int) $field->params->get( 'video_width', 400 );
			}

			$embed					=	'<div class="cbVideoField' . ( $reason == 'list' ? ' cbClicksInside' : null ) . '" style="' . ( $width ? 'max-width: ' . (int) $width . 'px;' : null ) . '">'
									.		'<video width="' . ( $width ? (int) $width : '640' ) . '" height="' . round( ( $width ? (int) $width : 640 ) / 1.78 ) . '" style="width: 100%; height: 100%;" src="' . htmlspecialchars( $value ) . '" type="' . htmlspecialchars( $this->getMimeType( $value ) ) . '" class="cbVideoFieldEmbed"></video>'
									.	'</div>';

			if ( $embed ) {
				static $JS_loaded	=	0;

				if ( ! $JS_loaded++ ) {
					$_CB_framework->outputCbJQuery( "$( '.cbVideoFieldEmbed' ).mediaelementplayer();", 'media' );
				}
			}
		}

		return $embed;
	}

	/**
	 *
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output            'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason            'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  boolean     $displayFieldIcons
	 * @return string                          HTML: <tag type="$type" value="$value" xxxx="xxx" yy="y" />
	 */
	function _htmlEditForm( &$field, &$user, $output, $reason, $displayFieldIcons = true ) {
		global $_CB_framework;

		$fieldName							=	$field->get( 'name' );
		$value								=	$user->get( $fieldName );
		$required							=	$this->_isRequired( $field, $user, $reason );

		$uploadSizeLimitMax					=	$field->params->get( 'fieldValidateVideo_sizeMax', 1024 );
		$uploadSizeLimitMin					=	$field->params->get( 'fieldValidateVideo_sizeMin', 0 );
		$uploadExtensionLimit				=	$this->allowedExtensions();
		$restrictions						=	array();

		if ( $uploadExtensionLimit ) {
			$restrictions[]					=	CBTxt::Th( 'VIDEO_FILE_UPLOAD_LIMITS_EXT', 'Your video file must be of [ext] type.', array( '[ext]' => implode( ', ', $uploadExtensionLimit ) ) );
		}

		if ( $uploadSizeLimitMin ) {
			$restrictions[]					=	CBTxt::Th( 'VIDEO_FILE_UPLOAD_LIMITS_MIN', 'Your video file should exceed [size].', array( '[size]' => $this->formattedFileSize( $uploadSizeLimitMin * 1024 ) ) );
		}

		if ( $uploadSizeLimitMax ) {
			$restrictions[]					=	CBTxt::Th( 'VIDEO_FILE_UPLOAD_LIMITS_MAX', 'Your video file should not exceed [size].', array( '[size]' => $this->formattedFileSize( $uploadSizeLimitMax * 1024 ) ) );
		}

		$existingFile						=	( $user->get( 'id' ) ? ( ( $value != null ) ? true : false ) : false );
		$choices							=	array();

		if ( ( $reason == 'register' ) || ( ( $reason == 'edit' ) && ( $user->id == 0 ) ) ) {
			if ( $required == 0 ) {
				$choices[]					=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No video file' ) );
			}
		} else {
			if ( $existingFile || ( $required == 0 ) ) {
				$choices[]					=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No change of video file' ) );
			}
		}

		$selected							=	null;

		if ( ( $required == 1 ) && ( ! $existingFile ) ) {
			$selected						=	'upload';
		}

		if ( $field->params->get( 'video_allow_links', 1 ) ) {
			$choices[]						=	moscomprofilerHTML::makeOption( 'link', ( $existingFile ? CBTxt::T( 'Link to new video file' ) : CBTxt::T( 'Link to video file' ) ) );
		}

		$choices[]							=	moscomprofilerHTML::makeOption( 'upload', ( $existingFile ? CBTxt::T( 'Upload new video file' ) : CBTxt::T( 'Upload video file' ) ) );

		if ( $existingFile && ( $required == 0 ) ) {
			$choices[]						=	moscomprofilerHTML::makeOption( 'delete', CBTxt::T( 'Remove video file' ) );
		}

		$return								=	null;

		if ( ( $reason != 'register' ) && ( $user->id != 0 ) && $existingFile ) {
			$return							.=	'<div class="form-group cb_form_line clearfix">' . $this->getEmbed( $field, $user, $value, $reason ) . '</div>';
		}

		if ( count( $choices ) > 1 ) {
			static $functOut				=	false;

			$additional						=	' class="form-control"';

			if ( ( $_CB_framework->getUi() == 1 ) && ( $reason == 'edit' ) && $field->get( 'readonly' ) ) {
				$additional					.=	' disabled="disabled"';
			}

			$translatedTitle				=	$this->getFieldTitle( $field, $user, 'html', $reason );
			$htmlDescription				=	$this->getFieldDescription( $field, $user, 'htmledit', $reason );
			$trimmedDescription				=	trim( strip_tags( $htmlDescription ) );

			$tooltip						=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, $additional ) : $additional );

			$return							.=	'<div class="form-group cb_form_line clearfix">'
											.		moscomprofilerHTML::selectList( $choices, $fieldName . '__choice', $tooltip, 'value', 'text', $selected, $required, true, null, false )
											.		$this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, 'select', '', null, '', array(), $displayFieldIcons, $required )
											.	'</div>';

			if ( ! $functOut ) {
				$js							=	"function cbslideVideoFile( choice, name ) {"
											.		"if ( ( choice == '' ) || ( choice == 'delete' ) ) {"
											.			"$( '#cbvideofile_upload_' + name + ',#cbvideofile_link_' + name ).slideUp( 'slow' );"
											.			"$( '#cbvideofile_upload_' + name + ' input,#cbvideofile_link_' + name + ' input' ).prop( 'readonly', true );"
											.		"} else if ( choice == 'upload' ) {"
											.			"$( '#cbvideofile_link_' + name ).slideUp( 'slow' );"
											.			"$( '#cbvideofile_upload_' + name ).slideDown( 'slow' );"
											.			"$( '#cbvideofile_link_' + name + ' input' ).prop( 'readonly', true );"
											.			"$( '#cbvideofile_upload_' + name + ' input' ).prop( 'readonly', false );"
											.		"} else if ( choice == 'link' ) {"
											.			"$( '#cbvideofile_upload_' + name ).slideUp( 'slow' );"
											.			"$( '#cbvideofile_link_' + name ).slideDown( 'slow' );"
											.			"$( '#cbvideofile_upload_' + name + ' input' ).prop( 'readonly', true );"
											.			"$( '#cbvideofile_link_' + name + ' input' ).prop( 'readonly', false );"
											.		"}"
											.	"}";

				$_CB_framework->outputCbJQuery( $js );

				$functOut					=	true;
			}

			$js								=	"$( '#cbvideofile_upload_" . addslashes( $fieldName ) . ",#cbvideofile_link_" . addslashes( $fieldName ) . "' ).hide();"
											.	"$( '#cbvideofile_upload_" . addslashes( $fieldName ) . " input,#cbvideofile_link_" . addslashes( $fieldName ) . " input' ).prop( 'readonly', true );"
											.	"$( '#" . addslashes( $fieldName ) . "__choice' ).on( 'click change', function() {"
											.		"cbslideVideoFile( $( this ).val(), '" . addslashes( $fieldName ) . "' );"
											.	"}).change();";

			$_CB_framework->outputCbJQuery( $js );
		} else {
			$return							.=	'<input type="hidden" name="' . htmlspecialchars( $fieldName ) . '__choice" value="' . htmlspecialchars( $choices[0]->value ) . '" />';
		}

		$validationAttributes				=	array();
		$validationAttributes[]				=	cbValidator::getRuleHtmlAttributes( 'extension', implode( ',', $uploadExtensionLimit ) );

		if ( $uploadSizeLimitMin || $uploadSizeLimitMax ) {
			$validationAttributes[]			=	cbValidator::getRuleHtmlAttributes( 'filesize', array( $uploadSizeLimitMin, $uploadSizeLimitMax, 'KB' ) );
		}

		$return								.=	'<div id="cbvideofile_upload_' . htmlspecialchars( $fieldName ) . '" class="form-group cb_form_line clearfix">'
											.		( $restrictions ? '<div class="help-block">' . implode( ' ', $restrictions ) . '</div>' : null )
											.		'<div>'
											.			CBTxt::T( 'Select video file' ) . ' <input type="file" name="' . htmlspecialchars( $fieldName ) . '__file" value="" class="form-control' . ( $required == 1 ? ' required' : null ) . '"' . implode( ' ', $validationAttributes ) . ' />'
											.			( count( $choices ) <= 0 ? $this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, 'select', '', null, '', array(), $displayFieldIcons, $required ) : null )
											.		'</div>'
											.		'<div class="help-block">';

		if ( $field->params->get( 'video_terms', 0 ) ) {
			$termsOutput					=	$field->params->get( 'terms_output', 'url' );
			$termsType						=	CBTxt::T( $field->params->get( 'terms_type', null ) );
			$termsDisplay					=	$field->params->get( 'terms_display', 'modal' );
			$termsURL						=	$field->params->get( 'terms_url', null );
			$termsText						=	$field->params->get( 'terms_text', null );
			$termsWidth						=	(int) $field->params->get( 'terms_width', 400 );
			$termsHeight					=	(int) $field->params->get( 'terms_height', 200 );

			if ( ! $termsType ) {
				$termsType					=	CBTxt::T( 'TERMS_AND_CONDITIONS', 'Terms and Conditions' );
			}

			if ( ! $termsHeight ) {
				$termsHeight				=	200;
			}

			if ( ( ( $termsOutput == 'url' ) && $termsURL ) || ( ( $termsOutput == 'text' ) && $termsText ) ) {
				if ( $termsDisplay == 'iframe' ) {
					if ( $termsOutput == 'url' ) {
						$return				.=			'<iframe class="cbTermsFrameURL" height="' . $termsHeight . '" width="' . ( $termsWidth ? $termsWidth : '100%' ) . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
					} else {
						$return				.=			'<div class="cbTermsFrameText" style="height:' . $termsHeight . 'px;width:' . ( $termsWidth ? $termsWidth . 'px' : '100%' ) . ';overflow:auto;">' . CBTxt::T( $termsText ) . '</div>';
					}
				}

				if ( $termsDisplay != 'iframe' ) {
					$attributes				=	' class="cbTermsLink"';

					if ( ( $termsOutput == 'text' ) && ( $termsDisplay == 'window' ) ) {
						$termsDisplay		=	'modal';
					}

					if ( $termsDisplay == 'modal' ) {
						if ( ! $termsWidth ) {
							$termsWidth		=	400;
						}

						if ( $termsOutput == 'url' ) {
							$tooltip		=	'<iframe class="cbTermsModalURL" height="' . $termsHeight . '" width="' . $termsWidth . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
						} else {
							$tooltip		=	'<div class="cbTermsModalText" style="height:' . $termsHeight . 'px;width:' . $termsWidth . 'px;overflow:auto;">' . CBTxt::T( $termsText ) . '</div>';
						}

						$url				=	'javascript:void(0);';
						$attributes			.=	' ' . cbTooltip( $_CB_framework->getUi(), $tooltip, $termsType, 'auto', null, null, null, 'data-hascbtooltip="true" data-cbtooltip-modal="true"' );
					} else {
						$url				=	htmlspecialchars( $termsURL );
						$attributes			.=	' target="_blank"';
					}

					$return					.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_VIDEO_FILE_URL_TERMS', 'By uploading, you certify that you have the right to distribute this video file and that it does not violate the <a href="[url]"[attributes]>[type]</a>', array( '[url]' => $url, '[attributes]' => $attributes, '[type]' => $termsType ) );
				} else {
					$return					.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_VIDEO_FILE_TERMS', 'By uploading, you certify that you have the right to distribute this video file and that it does not violate the above [type].', array( '[type]' => $termsType ) );
				}
			} else {
				$return						.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_VIDEO_FILE', 'By uploading, you certify that you have the right to distribute this video file.' );
			}
		} else {
			$return							.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_VIDEO_FILE', 'By uploading, you certify that you have the right to distribute this video file.' );
		}

		$return								.=		'</div>'
											.	'</div>'
											.	'<div id="cbvideofile_link_' . htmlspecialchars( $fieldName ) . '" class="form-group cb_form_line clearfix">'
											.		'<p>';

		if ( $field->params->get( 'video_allow_links', 1 ) ) {
			$linkField						=	new FieldTable( $field->getDbo() );

			foreach ( array_keys( get_object_vars( $linkField ) ) as $k ) {
				$linkField->set( $k, $field->get( $k ) );
			}

			$linkField->set( 'type', 'text' );
			$linkField->set( 'description', null );

			$user->set( $fieldName, ( ( strpos( $value, '/' ) !== false ) || ( strpos( $value, '\\' ) !== false ) ? $value : null ) );

			$return							.=				parent::getField( $linkField, $user, $output, $reason, 0 );

			$user->set( $fieldName, $value );

			unset( $linkField );
		}

		$return								.=		'</p>'
											.	'</div>';

		return $return;
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_CB_database;

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		$col					=	$field->get( 'name' );
		$colChoice				=	$col . '__choice';
		$colFile				=	$col . '__file';
		$choice					=	stripslashes( cbGetParam( $postdata, $colChoice ) );

		switch ( $choice ) {
			case 'upload':
				$value			=	( isset( $_FILES[$colFile] ) ? $_FILES[$colFile] : null );

				$this->validate( $field, $user, $choice, $value, $postdata, $reason );
				break;
			case 'link':
				parent::prepareFieldDataSave( $field, $user, $postdata, $reason );
				break;
			case 'delete':
				if ( $user->get( 'id' ) && ( $user->get( $col ) != null ) && ( $user->get( $col ) != '' ) ) {
					if ( isset( $user->$col ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->get( $col ), '' );
					}

					$value		=	$user->get( $col );

					if ( ( strpos( $value, '/' ) === false ) && ( strpos( $value, '\\' ) === false ) ) {
						$this->deleteFiles( $user, $user->get( $col ) );
					}

					$user->set( $col, null );

					$query		=	'UPDATE ' . $_CB_database->NameQuote( $field->get( 'table' ) )
								.	"\n SET " . $_CB_database->NameQuote( $col ) . " = NULL"
								.	', ' . $_CB_database->NameQuote( 'lastupdatedate' ) . ' = ' . $_CB_database->Quote( $_CB_framework->dateDbOfNow() )
								.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $user->get( 'id' );
					$_CB_database->setQuery( $query );
					$_CB_database->query();
				}
				break;
			default:
				if ( $field->params->get( 'video_allow_uploads', 1 ) ) {
					$value		=	$user->get( $col );

					$this->validate( $field, $user, $choice, $value, $postdata, $reason );
				} else {
					parent::prepareFieldDataSave( $field, $user, $postdata, $reason );
				}
				break;
		}
	}

	/**
	 * Mutator:
	 * Prepares field data commit
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function commitFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_PLUGINS, $_FILES;

		$col						=	$field->get( 'name' );
		$colChoice					=	$col . '__choice';
		$colFile					=	$col . '__file';
		$choice						=	stripslashes( cbGetParam( $postdata, $colChoice ) );

		switch ( $choice ) {
			case 'upload':
				$value				=	( isset( $_FILES[$colFile] ) ? $_FILES[$colFile] : null );

				if ( $this->validate( $field, $user, $choice, $value, $postdata, $reason ) ) {
					$_PLUGINS->loadPluginGroup( 'user' );

					$_PLUGINS->trigger( 'onBeforeUserVideoUpdate', array( &$user, &$value['tmp_name'] ) );

					if ( $_PLUGINS->is_errors() ) {
						$this->_setErrorMSG( $_PLUGINS->getErrorMSG() );
						return;
					}

					$path			=	$_CB_framework->getCfg( 'absolute_path' );
					$indexPath		=	$path . '/components/com_comprofiler/index.html';
					$filesPath		=	$path . '/images/comprofiler/video';
					$filePath		=	$filesPath . '/' . (int) $user->get( 'id' );

					if ( ! is_dir( $filesPath ) ) {
						$oldmask	=	@umask( 0 );

						if ( @mkdir( $filesPath, 0755, true ) ) {
							@umask( $oldmask );
							@chmod( $filesPath, 0755 );

							if ( ! file_exists( $filesPath . '/index.html' ) ) {
								@copy( $indexPath, $filesPath . '/index.html' );
								@chmod( $filesPath . '/index.html', 0755 );
							}
						} else {
							@umask( $oldmask );
						}
					}

					if ( ! is_dir( $filePath ) ) {
						$oldmask	=	@umask( 0 );

						if ( @mkdir( $filePath, 0755, true ) ) {
							@umask( $oldmask );
							@chmod( $filePath, 0755 );

							if ( ! file_exists( $filePath . '/index.html' ) ) {
								@copy( $indexPath, $filePath . '/index.html' );
								@chmod( $filePath . '/index.html', 0755 );
							}
						} else {
							@umask( $oldmask );
						}
					}

					$uploadedExt	=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_EXTENSION ) ) );
					$newFileName	=	$col . '_' . uniqid( $user->id . '_' ) . '.' . $uploadedExt;

					if ( ! move_uploaded_file( $value['tmp_name'], $filePath . '/'. $newFileName ) ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'CBVideo-failed to upload video file: %s' ), $newFileName ) );
						return;
					} else {
						@chmod( $filePath . '/' . $value['tmp_name'], 0755 );
					}

					if ( isset( $user->$col ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->get( $col ), '' );
					}

					if ( isset( $user->$col ) && ( $user->get( $col ) != '' ) ) {
						$this->deleteFiles( $user, $user->get( $col ) );
					}

					$user->set( $col, $newFileName );

					$_PLUGINS->trigger( 'onAfterUserVideoUpdate', array( &$user, $newFileName ) );
				}
				break;
		}
	}

	/**
	 * Mutator:
	 * Prepares field data rollback
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function rollbackFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_FILES;

		$col			=	$field->get( 'name' );
		$colChoice		=	$col . '__choice';
		$colFile		=	$col . '__file';

		$choice			=	stripslashes( cbGetParam( $postdata, $colChoice ) );

		switch ( $choice ) {
			case 'upload':
				$value	=	( isset( $_FILES[$colFile] ) ? $_FILES[$colFile] : null );

				if ( $this->validate( $field, $user, $choice, $value, $postdata, $reason ) ) {
					$this->deleteFiles( $user, $user->get( $col ) );
				}
				break;
		}
	}

	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save user edit, 'register' for save registration
	 * @return boolean                  True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		$isRequired							=	$this->_isRequired( $field, $user, $reason );

		$col								=	$field->get( 'name' );
		$colChoice							=	$col . '__choice';
		$choice								=	stripslashes( cbGetParam( $postdata, $colChoice ) );

		switch ( $choice ) {
			case 'upload':
				if ( ! $field->params->get( 'video_allow_uploads', 1 ) ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
					return false;
				} elseif ( ! isset( $value['tmp_name'] ) || empty( $value['tmp_name'] ) || ( $value['error'] != 0 ) || ( ! is_uploaded_file( $value['tmp_name'] ) ) ) {
					if ( $isRequired ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Please select a video file before uploading' ) );
					}

					return false;
				} else {
					$uploadSizeLimitMax		=	$field->params->get( 'fieldValidateVideo_sizeMax', 1024 );
					$uploadSizeLimitMin		=	$field->params->get( 'fieldValidateVideo_sizeMin', 0 );
					$uploadExtensionLimit	=	$this->allowedExtensions();
					$uploadedExt			=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_EXTENSION ) ) );

					if ( ( ! $uploadedExt ) || ( ! in_array( $uploadedExt, $uploadExtensionLimit ) ) ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'Please upload only %s' ), implode( ', ', $uploadExtensionLimit ) ) );
						return false;
					}

					$uploadedSize			=	$value['size'];

					if ( ( $uploadedSize / 1024 ) > $uploadSizeLimitMax ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'The video file size exceeds the maximum of %s' ), $this->formattedFileSize( $uploadSizeLimitMax * 1024 ) ) );
						return false;
					}

					if ( ( $uploadedSize / 1024 ) < $uploadSizeLimitMin ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'The video file is too small, the minimum is %s' ), $this->formattedFileSize( $uploadSizeLimitMin * 1024 ) ) );
						return false;
					}
				}
				break;
			case 'link':
				if ( ! $field->params->get( 'video_allow_links', 1 ) ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
					return false;
				}

				$validated					=	parent::validate( $field, $user, $columnName, $value, $postdata, $reason );

				if ( $validated && ( $value !== '' ) && ( $value !== null ) ) {
					$domain					=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $value, PHP_URL_HOST ) );

					if ( ! in_array( $domain, array( 'youtube', 'youtu' ) ) ) {
						$linkHeaders		=	@get_headers( $value );
						$linkExists			=	false;

						if ( $linkHeaders ) {
							$linkExists		=	( isset( $linkHeaders[0] ) && ( strpos( $linkHeaders[0], '200' ) !== false ) ? true : false );
						}

						if ( ! $linkExists ) {
							$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Please input a video file url before linking' ) );
							return false;
						}

						$linkExtLimit		=	$this->allowedExtensions();
						$linkExt			=	strtolower( pathinfo( $value, PATHINFO_EXTENSION ) );

						if ( ( ! $linkExt ) || ( ! in_array( $linkExt, $linkExtLimit ) ) ) {
							$linkExtLimit[]	=	'youtube';

							$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'Please link only %s' ), implode( ', ', $linkExtLimit ) ) );
							return false;
						}
					}
				}

				return $validated;
				break;
			default:
				$valCol						=	$field->get( 'name' );

				if ( $isRequired && ( ( ! $user ) || ( ! isset( $user->$valCol ) ) || ( ! $user->get( $valCol ) ) ) ) {
					if ( ! $value ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_FIELDREQUIRED', 'This Field is required' ) );
						return false;
					}
				}
				break;
		}

		return true;
	}

	/**
	 * Deletes file from users folder
	 *
	 * @param  UserTable  $user
	 * @param  string     $file
	 */
	function deleteFiles( $user, $file = null ) {
		global $_CB_framework;

		if ( ! is_object( $user ) ) {
			return;
		}

		$filePath	=	$_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/video/' . (int) $user->id . '/';

		if ( ! is_dir( $filePath ) ) {
			return;
		}

		if ( ! $file ) {
			if ( false !== ( $handle = opendir( $filePath ) ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file && ( ( $file != '.' ) && ( $file != '..' ) ) ) {
						@unlink( $filePath . $file );
					}
				}
				closedir( $handle );
			}

			if ( is_dir( $filePath ) ) {
				@rmdir( $filePath );
			}
		} else {
			if ( file_exists( $filePath . $file ) ) {
				@unlink( $filePath . $file );
			}
		}
	}

	/**
	 * returns the mimetype of the supplied file or link
	 *
	 * @param  string  $value
	 * @return string
	 */
	function getMimeType( $value ) {
		$domain			=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $value, PHP_URL_HOST ) );

		if ( $domain && in_array( $domain, array( 'youtube', 'youtu' ) ) ) {
			return 'video/youtube';
		}

		$extension		=	strtolower( pathinfo( ( $domain ? $value : preg_replace( '/[^-a-zA-Z0-9_.]/', '', $value ) ), PATHINFO_EXTENSION ) );

		return ( $extension == 'm4v' ? 'video/mp4' : cbGetMimeFromExt( $extension ) );
	}

	/**
	 * outputs a secure list of allowed file extensions
	 *
	 * @return array
	 */
	private function allowedExtensions() {
		return array( 'mp4', 'ogv', 'ogg', 'webm', 'm4v' );
	}

	/**
	 * Returns file size formatted from bytes
	 *
	 * @param int $bytes
	 * @return string
	 */
	private function formattedFileSize( $bytes )
	{
		if ( $bytes >= 1099511627776 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_TB', '%%COUNT%% TB|%%COUNT%% TBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1099511627776, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1073741824 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_GB', '%%COUNT%% GB|%%COUNT%% GBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1073741824, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1048576 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_MB', '%%COUNT%% MB|%%COUNT%% MBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1048576, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1024 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_KB', '%%COUNT%% KB|%%COUNT%% KBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1024, 2, '.', '' ) ) );
		} else {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_B', '%%COUNT%% B|%%COUNT%% Bs', array( '%%COUNT%%' => (float) number_format( $bytes, 2, '.', '' ) ) );
		}

		return $size;
	}
}

class CBfield_audio extends CBfield_text {

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$return						=	null;

		$value						=	$user->get( $field->get( 'name' ) );

		switch ( $output ) {
			case 'html':
			case 'rss':
				if ( $value ) {
					$return			=	$this->getEmbed( $field, $user, $value, $reason );
				}

				$return				=	$this->formatFieldValueLayout( $return, $reason, $field, $user );
				break;
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'htmledit':
				// fallthrough is on purpose encase we don't allow uploads
				if ( $field->params->get( 'audio_allow_uploads', 1 ) ) {
					if ( $reason == 'search' ) {
						$choices	=	array();
						$choices[]	=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'UE_NO_PREFERENCE', 'No preference' ) );
						$choices[]	=	moscomprofilerHTML::makeOption( '1', ( $field->params->get( 'audio_allow_links', 1 ) ? CBTxt::T( 'Has audio file or link' ) : CBTxt::T( 'Has a audio file' ) ) );
						$choices[]	=	moscomprofilerHTML::makeOption( '0', ( $field->params->get( 'audio_allow_links', 1 ) ? CBTxt::T( 'Has no audio file or link' ) : CBTxt::T( 'Has no audio file' ) ) );

						$html		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', $value, '', $choices );

						$return		=	$this->_fieldSearchModeHtml( $field, $user, $html, 'singlechoice', $list_compare_types );
					} else {
						$return		=	$this->formatFieldValueLayout( $this->_htmlEditForm( $field, $user, $output, $reason ), $reason, $field, $user );
					}
					break;
				}
			default:
				$field->set( 'type', 'text' );

				$return				=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}

		return $return;
	}

	/**
	 * returns audio embed based off audio url
	 *
	 * @param  FieldTable   $field
	 * @param  UserTable    $user
	 * @param  string       $value
	 * @param  string       $reason
	 * @return null|string
	 */
	public function getEmbed( $field, $user, $value, $reason ) {
		global $_CB_framework;

		$domain						=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $value, PHP_URL_HOST ) );

		if ( ! $domain ) {
			$value					=	$_CB_framework->getCfg( 'live_site' ) . '/images/comprofiler/audio/' . (int) $user->get( 'id' ) . '/' . urlencode( $value );
		}

		$embed						=	null;

		if ( $value ) {
			$currentScheme			=	( ( isset( $_SERVER['HTTPS'] ) && ( ! empty( $_SERVER['HTTPS'] ) ) && ( $_SERVER['HTTPS'] != 'off' ) ) ? 'https' : 'http' );
			$urlScheme				=	parse_url( $value, PHP_URL_SCHEME );

			if ( ! $urlScheme ) {
				$urlScheme			=	$currentScheme;
			}

			if ( ( $currentScheme == 'https' ) && ( $urlScheme != $currentScheme ) ) {
				$value				=	str_replace( 'http', 'https', $value );
			}

			if ( $reason != 'profile' ) {
				$width				=	(int) $field->params->get( 'audio_thumbwidth', 400 );
			} else {
				$width				=	(int) $field->params->get( 'audio_width', 400 );
			}

			$embed					=	'<div class="cbAudioField' . ( $reason == 'list' ? ' cbClicksInside' : null ) . '" style="' . ( $width ? 'max-width: ' . (int) $width . 'px;' : null ) . '">'
									.		'<audio width="' . ( $width ? (int) $width : '640' ) . '" style="width: 100%;" src="' . htmlspecialchars( $value ) . '" type="' . htmlspecialchars( $this->getMimeType( $value ) ) . '" class="cbAudioFieldEmbed"></audio>'
									.	'</div>';

			if ( $embed ) {
				static $JS_loaded	=	0;

				if ( ! $JS_loaded++ ) {
					$_CB_framework->outputCbJQuery( "$( '.cbAudioFieldEmbed' ).mediaelementplayer({ isVideo: false });", 'media' );
				}
			}
		}

		return $embed;
	}

	/**
	 *
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output             'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason             'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  boolean     $displayFieldIcons
	 * @return string                          HTML: <tag type="$type" value="$value" xxxx="xxx" yy="y" />
	 */
	function _htmlEditForm( &$field, &$user, $output, $reason, $displayFieldIcons = true ) {
		global $_CB_framework;

		$fieldName							=	$field->get( 'name' );
		$value								=	$user->get( $fieldName );
		$required							=	$this->_isRequired( $field, $user, $reason );

		$uploadSizeLimitMax					=	$field->params->get( 'fieldValidateAudio_sizeMax', 1024 );
		$uploadSizeLimitMin					=	$field->params->get( 'fieldValidateAudio_sizeMin', 0 );
		$uploadExtensionLimit				=	$this->allowedExtensions();
		$restrictions						=	array();

		if ( $uploadExtensionLimit ) {
			$restrictions[]					=	CBTxt::Th( 'AUDIO_FILE_UPLOAD_LIMITS_EXT', 'Your audio file must be of [ext] type.', array( '[ext]' => implode( ', ', $uploadExtensionLimit ) ) );
		}

		if ( $uploadSizeLimitMin ) {
			$restrictions[]					=	CBTxt::Th( 'AUDIO_FILE_UPLOAD_LIMITS_MIN', 'Your audio file should exceed [size].', array( '[size]' => $this->formattedFileSize( $uploadSizeLimitMin * 1024 ) ) );
		}

		if ( $uploadSizeLimitMax ) {
			$restrictions[]					=	CBTxt::Th( 'AUDIO_FILE_UPLOAD_LIMITS_MAX', 'Your audio file should not exceed [size].', array( '[size]' => $this->formattedFileSize( $uploadSizeLimitMax * 1024 ) ) );
		}

		$existingFile						=	( $user->get( 'id' ) ? ( ( $value != null ) ? true : false ) : false );
		$choices							=	array();

		if ( ( $reason == 'register' ) || ( ( $reason == 'edit' ) && ( $user->id == 0 ) ) ) {
			if ( $required == 0 ) {
				$choices[]					=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No audio file' ) );
			}
		} else {
			if ( $existingFile || ( $required == 0 ) ) {
				$choices[]					=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'No change of audio file' ) );
			}
		}

		$selected							=	null;

		if ( ( $required == 1 ) && ( ! $existingFile ) ) {
			$selected						=	'upload';
		}

		if ( $field->params->get( 'audio_allow_links', 1 ) ) {
			$choices[]						=	moscomprofilerHTML::makeOption( 'link', ( $existingFile ? CBTxt::T( 'Link to new audio file' ) : CBTxt::T( 'Link to audio file' ) ) );
		}

		$choices[]							=	moscomprofilerHTML::makeOption( 'upload', ( $existingFile ? CBTxt::T( 'Upload new audio file' ) : CBTxt::T( 'Upload audio file' ) ) );

		if ( $existingFile && ( $required == 0 ) ) {
			$choices[]						=	moscomprofilerHTML::makeOption( 'delete', CBTxt::T( 'Remove audio file' ) );
		}

		$return								=	null;

		if ( ( $reason != 'register' ) && ( $user->id != 0 ) && $existingFile ) {
			$return							.=	'<div class="form-group cb_form_line clearfix">' . $this->getEmbed( $field, $user, $value, $reason ) . '</div>';
		}

		if ( count( $choices ) > 1 ) {
			static $functOut			=	false;

			$additional						=	' class="form-control"';

			if ( ( $_CB_framework->getUi() == 1 ) && ( $reason == 'edit' ) && $field->get( 'readonly' ) ) {
				$additional					.=	' disabled="disabled"';
			}

			$translatedTitle				=	$this->getFieldTitle( $field, $user, 'html', $reason );
			$htmlDescription				=	$this->getFieldDescription( $field, $user, 'htmledit', $reason );
			$trimmedDescription				=	trim( strip_tags( $htmlDescription ) );

			$tooltip						=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, $additional ) : $additional );

			$return							.=	'<div class="form-group cb_form_line clearfix">'
											.		moscomprofilerHTML::selectList( $choices, $fieldName . '__choice', $tooltip, 'value', 'text', $selected, $required, true, null, false )
											.		$this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, 'select', '', null, '', array(), $displayFieldIcons, $required )
											.	'</div>';

			if ( ! $functOut ) {
				$js							=	"function cbslideAudioFile( choice, name ) {"
											.		"if ( ( choice == '' ) || ( choice == 'delete' ) ) {"
											.			"$( '#cbaudiofile_upload_' + name + ',#cbaudiofile_link_' + name ).slideUp( 'slow' );"
											.			"$( '#cbaudiofile_upload_' + name + ' input,#cbaudiofile_link_' + name + ' input' ).prop( 'readonly', true );"
											.		"} else if ( choice == 'upload' ) {"
											.			"$( '#cbaudiofile_link_' + name ).slideUp( 'slow' );"
											.			"$( '#cbaudiofile_upload_' + name ).slideDown( 'slow' );"
											.			"$( '#cbaudiofile_link_' + name + ' input' ).prop( 'readonly', true );"
											.			"$( '#cbaudiofile_upload_' + name + ' input' ).prop( 'readonly', false );"
											.		"} else if ( choice == 'link' ) {"
											.			"$( '#cbaudiofile_upload_' + name ).slideUp( 'slow' );"
											.			"$( '#cbaudiofile_link_' + name ).slideDown( 'slow' );"
											.			"$( '#cbaudiofile_upload_' + name + ' input' ).prop( 'readonly', true );"
											.			"$( '#cbaudiofile_link_' + name + ' input' ).prop( 'readonly', false );"
											.		"}"
											.	"}";

				$_CB_framework->outputCbJQuery( $js );

				$functOut					=	true;
			}

			$js								=	"$( '#cbaudiofile_upload_" . addslashes( $fieldName ) . ",#cbaudiofile_link_" . addslashes( $fieldName ) . "' ).hide();"
											.	"$( '#cbaudiofile_upload_" . addslashes( $fieldName ) . " input,#cbaudiofile_link_" . addslashes( $fieldName ) . " input' ).prop( 'readonly', true );"
											.	"$( '#" . addslashes( $fieldName ) . "__choice' ).on( 'click change', function() {"
											.		"cbslideAudioFile( $( this ).val(), '" . addslashes( $fieldName ) . "' );"
											.	"}).change();";

			$_CB_framework->outputCbJQuery( $js );
		} else {
			$return							.=	'<input type="hidden" name="' . htmlspecialchars( $fieldName ) . '__choice" value="' . htmlspecialchars( $choices[0]->value ) . '" />';
		}

		$validationAttributes				=	array();
		$validationAttributes[]				=	cbValidator::getRuleHtmlAttributes( 'extension', implode( ',', $uploadExtensionLimit ) );

		if ( $uploadSizeLimitMin || $uploadSizeLimitMax ) {
			$validationAttributes[]			=	cbValidator::getRuleHtmlAttributes( 'filesize', array( $uploadSizeLimitMin, $uploadSizeLimitMax, 'KB' ) );
		}

		$return								.=	'<div id="cbaudiofile_upload_' . htmlspecialchars( $fieldName ) . '" class="form-group cb_form_line clearfix">'
											.		( $restrictions ? '<div class="help-block">' . implode( ' ', $restrictions ) . '</div>' : null )
											.		'<div>'
											.			CBTxt::T( 'Select audio file' ) . ' <input type="file" name="' . htmlspecialchars( $fieldName ) . '__file" value="" class="form-control' . ( $required == 1 ? ' required' : null ) . '"' . implode( ' ', $validationAttributes ) . ' />'
											.			( count( $choices ) <= 0 ? $this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, 'select', '', null, '', array(), $displayFieldIcons, $required ) : null )
											.		'</div>'
											.		'<div class="help-block">';

		if ( $field->params->get( 'audio_terms', 0 ) ) {
			$termsOutput					=	$field->params->get( 'terms_output', 'url' );
			$termsType						=	CBTxt::T( $field->params->get( 'terms_type', null ) );
			$termsDisplay					=	$field->params->get( 'terms_display', 'modal' );
			$termsURL						=	$field->params->get( 'terms_url', null );
			$termsText						=	$field->params->get( 'terms_text', null );
			$termsWidth						=	(int) $field->params->get( 'terms_width', 400 );
			$termsHeight					=	(int) $field->params->get( 'terms_height', 200 );

			if ( ! $termsType ) {
				$termsType					=	CBTxt::T( 'TERMS_AND_CONDITIONS', 'Terms and Conditions' );
			}

			if ( ! $termsHeight ) {
				$termsHeight				=	200;
			}

			if ( ( ( $termsOutput == 'url' ) && $termsURL ) || ( ( $termsOutput == 'text' ) && $termsText ) ) {
				if ( $termsDisplay == 'iframe' ) {
					if ( $termsOutput == 'url' ) {
						$return				.=			'<iframe class="cbTermsFrameURL" height="' . $termsHeight . '" width="' . ( $termsWidth ? $termsWidth : '100%' ) . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
					} else {
						$return				.=			'<div class="cbTermsFrameText" style="height:' . $termsHeight . 'px;width:' . ( $termsWidth ? $termsWidth . 'px' : '100%' ) . ';overflow:auto;">' . CBTxt::T( $termsText ) . '</div>';
					}
				}

				if ( $termsDisplay != 'iframe' ) {
					$attributes				=	' class="cbTermsLink"';

					if ( ( $termsOutput == 'text' ) && ( $termsDisplay == 'window' ) ) {
						$termsDisplay		=	'modal';
					}

					if ( $termsDisplay == 'modal' ) {
						if ( ! $termsWidth ) {
							$termsWidth		=	400;
						}

						if ( $termsOutput == 'url' ) {
							$tooltip		=	'<iframe class="cbTermsModalURL" height="' . $termsHeight . '" width="' . $termsWidth . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
						} else {
							$tooltip		=	'<div class="cbTermsModalText" style="height:' . $termsHeight . 'px;width:' . $termsWidth . 'px;overflow:auto;">' . CBTxt::T( $termsText ) . '</div>';
						}

						$url				=	'javascript:void(0);';
						$attributes			.=	' ' . cbTooltip( $_CB_framework->getUi(), $tooltip, $termsType, 'auto', null, null, null, 'data-hascbtooltip="true" data-cbtooltip-modal="true"' );
					} else {
						$url				=	htmlspecialchars( $termsURL );
						$attributes			.=	' target="_blank"';
					}

					$return					.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_AUDIO_FILE_URL_TERMS', 'By uploading, you certify that you have the right to distribute this audio file and that it does not violate the <a href="[url]"[attributes]>[type]</a>', array( '[url]' => $url, '[attributes]' => $attributes, '[type]' => $termsType ) );
				} else {
					$return					.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_AUDIO_FILE_TERMS', 'By uploading, you certify that you have the right to distribute this audio file and that it does not violate the above [type].', array( '[type]' => $termsType ) );
				}
			} else {
				$return						.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_AUDIO_FILE', 'By uploading, you certify that you have the right to distribute this audio file.' );
			}
		} else {
			$return							.=			CBTxt::Th( 'BY_UPLOADING_YOU_CERTIFY_THAT_YOU_HAVE_THE_RIGHT_TO_DISTRIBUTE_THIS_AUDIO_FILE', 'By uploading, you certify that you have the right to distribute this audio file.' );
		}

		$return								.=		'</div>'
											.	'</div>'
											.	'<div id="cbaudiofile_link_' . htmlspecialchars( $fieldName ) . '" class="form-group cb_form_line clearfix">'
											.		'<p>';

		if ( $field->params->get( 'audio_allow_links', 1 ) ) {
			$linkField						=	new FieldTable( $field->getDbo() );

			foreach ( array_keys( get_object_vars( $linkField ) ) as $k ) {
				$linkField->set( $k, $field->get( $k ) );
			}

			$linkField->set( 'type', 'text' );
			$linkField->set( 'description', null );

			$user->set( $fieldName, ( ( strpos( $value, '/' ) !== false ) || ( strpos( $value, '\\' ) !== false ) ? $value : null ) );

			$return							.=				parent::getField( $linkField, $user, $output, $reason, 0 );

			$user->set( $fieldName, $value );

			unset( $linkField );
		}

		$return							.=		'</p>'
										.	'</div>';

		return $return;
	}

	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_CB_database;

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		$col					=	$field->get( 'name' );
		$colChoice				=	$col . '__choice';
		$colFile				=	$col . '__file';
		$choice					=	stripslashes( cbGetParam( $postdata, $colChoice ) );

		switch ( $choice ) {
			case 'upload':
				$value			=	( isset( $_FILES[$colFile] ) ? $_FILES[$colFile] : null );

				$this->validate( $field, $user, $choice, $value, $postdata, $reason );
				break;
			case 'link':
				parent::prepareFieldDataSave( $field, $user, $postdata, $reason );
				break;
			case 'delete':
				if ( $user->get( 'id' ) && ( $user->get( $col ) != null ) && ( $user->get( $col ) != '' ) ) {
					if ( isset( $user->$col ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->get( $col ), '' );
					}

					$value		=	$user->get( $col );

					if ( ( strpos( $value, '/' ) === false ) && ( strpos( $value, '\\' ) === false ) ) {
						$this->deleteFiles( $user, $user->get( $col ) );
					}

					$user->set( $col, null );

					$query		=	'UPDATE ' . $_CB_database->NameQuote( $field->get( 'table' ) )
								.	"\n SET " . $_CB_database->NameQuote( $col ) . " = NULL"
								.	', ' . $_CB_database->NameQuote( 'lastupdatedate' ) . ' = ' . $_CB_database->Quote( $_CB_framework->dateDbOfNow() )
								.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $user->get( 'id' );
					$_CB_database->setQuery( $query );
					$_CB_database->query();
				}
				break;
			default:
				if ( $field->params->get( 'audio_allow_uploads', 1 ) ) {
					$value		=	$user->get( $col );

					$this->validate( $field, $user, $choice, $value, $postdata, $reason );
				} else {
					parent::prepareFieldDataSave( $field, $user, $postdata, $reason );
				}
				break;
		}
	}

	/**
	 * Mutator:
	 * Prepares field data commit
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function commitFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_PLUGINS, $_FILES;

		$col						=	$field->get( 'name' );
		$colChoice					=	$col . '__choice';
		$colFile					=	$col . '__file';
		$choice						=	stripslashes( cbGetParam( $postdata, $colChoice ) );

		switch ( $choice ) {
			case 'upload':
				$value				=	( isset( $_FILES[$colFile] ) ? $_FILES[$colFile] : null );

				if ( $this->validate( $field, $user, $choice, $value, $postdata, $reason ) ) {
					$_PLUGINS->loadPluginGroup( 'user' );

					$_PLUGINS->trigger( 'onBeforeUserAudioUpdate', array( &$user, &$value['tmp_name'] ) );

					if ( $_PLUGINS->is_errors() ) {
						$this->_setErrorMSG( $_PLUGINS->getErrorMSG() );
						return;
					}

					$path			=	$_CB_framework->getCfg( 'absolute_path' );
					$indexPath		=	$path . '/components/com_comprofiler/index.html';
					$filesPath		=	$path . '/images/comprofiler/audio';
					$filePath		=	$filesPath . '/' . (int) $user->get( 'id' );

					if ( ! is_dir( $filesPath ) ) {
						$oldmask	=	@umask( 0 );

						if ( @mkdir( $filesPath, 0755, true ) ) {
							@umask( $oldmask );
							@chmod( $filesPath, 0755 );

							if ( ! file_exists( $filesPath . '/index.html' ) ) {
								@copy( $indexPath, $filesPath . '/index.html' );
								@chmod( $filesPath . '/index.html', 0755 );
							}
						} else {
							@umask( $oldmask );
						}
					}

					if ( ! is_dir( $filePath ) ) {
						$oldmask	=	@umask( 0 );

						if ( @mkdir( $filePath, 0755, true ) ) {
							@umask( $oldmask );
							@chmod( $filePath, 0755 );

							if ( ! file_exists( $filePath . '/index.html' ) ) {
								@copy( $indexPath, $filePath . '/index.html' );
								@chmod( $filePath . '/index.html', 0755 );
							}
						} else {
							@umask( $oldmask );
						}
					}

					$uploadedExt	=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_EXTENSION ) ) );
					$newFileName	=	$col . '_' . uniqid( $user->id . '_' ) . '.' . $uploadedExt;

					if ( ! move_uploaded_file( $value['tmp_name'], $filePath . '/'. $newFileName ) ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'CBAudio-failed to upload audio file: %s' ), $newFileName ) );
						return;
					} else {
						@chmod( $filePath . '/' . $value['tmp_name'], 0755 );
					}

					if ( isset( $user->$col ) ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->get( $col ), '' );
					}

					if ( isset( $user->$col ) && ( $user->get( $col ) != '' ) ) {
						$this->deleteFiles( $user, $user->get( $col ) );
					}

					$user->set( $col, $newFileName );

					$_PLUGINS->trigger( 'onAfterUserAudioUpdate', array( &$user, $newFileName ) );
				}
				break;
		}
	}

	/**
	 * Mutator:
	 * Prepares field data rollback
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function rollbackFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_FILES;

		$col			=	$field->get( 'name' );
		$colChoice		=	$col . '__choice';
		$colFile		=	$col . '__file';

		$choice			=	stripslashes( cbGetParam( $postdata, $colChoice ) );

		switch ( $choice ) {
			case 'upload':
				$value	=	( isset( $_FILES[$colFile] ) ? $_FILES[$colFile] : null );

				if ( $this->validate( $field, $user, $choice, $value, $postdata, $reason ) ) {
					$this->deleteFiles( $user, $user->get( $col ) );
				}
				break;
		}
	}

	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save user edit, 'register' for save registration
	 * @return boolean                  True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, $columnName, &$value, &$postdata, $reason ) {
		$isRequired							=	$this->_isRequired( $field, $user, $reason );

		$col								=	$field->get( 'name' );
		$colChoice							=	$col . '__choice';
		$choice								=	stripslashes( cbGetParam( $postdata, $colChoice ) );

		switch ( $choice ) {
			case 'upload':
				if ( ! $field->params->get( 'audio_allow_uploads', 1 ) ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
					return false;
				} elseif ( ! isset( $value['tmp_name'] ) || empty( $value['tmp_name'] ) || ( $value['error'] != 0 ) || ( ! is_uploaded_file( $value['tmp_name'] ) ) ) {
					if ( $isRequired ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Please select a audio file before uploading' ) );
					}

					return false;
				} else {
					$uploadSizeLimitMax		=	$field->params->get( 'fieldValidateAudio_sizeMax', 1024 );
					$uploadSizeLimitMin		=	$field->params->get( 'fieldValidateAudio_sizeMin', 0 );
					$uploadExtensionLimit	=	$this->allowedExtensions();
					$uploadedExt			=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $value['name'], PATHINFO_EXTENSION ) ) );

					if ( ( ! $uploadedExt ) || ( ! in_array( $uploadedExt, $uploadExtensionLimit ) ) ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'Please upload only %s' ), implode( ', ', $uploadExtensionLimit ) ) );
						return false;
					}

					$uploadedSize			=	$value['size'];

					if ( ( $uploadedSize / 1024 ) > $uploadSizeLimitMax ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'The audio file size exceeds the maximum of %s' ), $this->formattedFileSize( $uploadSizeLimitMax * 1024 ) ) );
						return false;
					}

					if ( ( $uploadedSize / 1024 ) < $uploadSizeLimitMin ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'The audio file is too small, the minimum is %s' ), $this->formattedFileSize( $uploadSizeLimitMin * 1024 ) ) );
						return false;
					}
				}
				break;
			case 'link':
				if ( ! $field->params->get( 'audio_allow_links', 1 ) ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
					return false;
				}

				$validated					=	parent::validate( $field, $user, $columnName, $value, $postdata, $reason );

				if ( $validated && ( $value !== '' ) && ( $value !== null ) ) {
					$linkHeaders			=	@get_headers( $value );
					$linkExists				=	false;

					if ( $linkHeaders ) {
						$linkExists			=	( isset( $linkHeaders[0] ) && ( strpos( $linkHeaders[0], '200' ) !== false ) ? true : false );
					}

					if ( ! $linkExists ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'Please input a audio file url before linking' ) );
						return false;
					}

					$linkExtLimit			=	$this->allowedExtensions();
					$linkExt				=	strtolower( pathinfo( $value, PATHINFO_EXTENSION ) );

					if ( ( ! $linkExt ) || ( ! in_array( $linkExt, $linkExtLimit ) ) ) {
						$this->_setValidationError( $field, $user, $reason, sprintf( CBTxt::T( 'Please link only %s' ), implode( ', ', $linkExtLimit ) ) );
						return false;
					}
				}

				return $validated;
				break;
			default:
				$valCol						=	$field->get( 'name' );

				if ( $isRequired && ( ( ! $user ) || ( ! isset( $user->$valCol ) ) || ( ! $user->get( $valCol ) ) ) ) {
					if ( ! $value ) {
						$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_FIELDREQUIRED', 'This Field is required' ) );
						return false;
					}
				}
				break;
		}

		return true;
	}

	/**
	 * Deletes file from users folder
	 *
	 * @param  UserTable  $user
	 * @param  string     $file
	 */
	function deleteFiles( $user, $file = null ) {
		global $_CB_framework;

		if ( ! is_object( $user ) ) {
			return;
		}

		$filePath	=	$_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/audio/' . (int) $user->id . '/';

		if ( ! is_dir( $filePath ) ) {
			return;
		}

		if ( ! $file ) {
			if ( false !== ( $handle = opendir( $filePath ) ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file && ( ( $file != '.' ) && ( $file != '..' ) ) ) {
						@unlink( $filePath . $file );
					}
				}
				closedir( $handle );
			}

			if ( is_dir( $filePath ) ) {
				@rmdir( $filePath );
			}
		} else {
			if ( file_exists( $filePath . $file ) ) {
				@unlink( $filePath . $file );
			}
		}
	}

	/**
	 * returns the mimetype of the supplied file or link
	 *
	 * @param  string  $value
	 * @return string
	 */
	function getMimeType( $value ) {
		$domain			=	preg_replace( '/^(?:(?:\w+\.)*)?(\w+)\..+$/', '\1', parse_url( $value, PHP_URL_HOST ) );
		$extension		=	strtolower( pathinfo( ( $domain ? $value : preg_replace( '/[^-a-zA-Z0-9_.]/', '', $value ) ), PATHINFO_EXTENSION ) );

		if ( $extension == 'mp3' ) {
			return 'audio/mp3';
		}

		if ( $extension == 'm4a' ) {
			return 'audio/mp4';
		}

		return cbGetMimeFromExt( $extension );
	}

	/**
	 * outputs a secure list of allowed file extensions
	 *
	 * @return array
	 */
	private function allowedExtensions() {
		return array( 'mp3', 'oga', 'ogg', 'weba', 'wav', 'm4a' );
	}

	/**
	 * Returns file size formatted from bytes
	 *
	 * @param int $bytes
	 * @return string
	 */
	private function formattedFileSize( $bytes )
	{
		if ( $bytes >= 1099511627776 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_TB', '%%COUNT%% TB|%%COUNT%% TBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1099511627776, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1073741824 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_GB', '%%COUNT%% GB|%%COUNT%% GBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1073741824, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1048576 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_MB', '%%COUNT%% MB|%%COUNT%% MBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1048576, 2, '.', '' ) ) );
		} elseif ( $bytes >= 1024 ) {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_KB', '%%COUNT%% KB|%%COUNT%% KBs', array( '%%COUNT%%' => (float) number_format( $bytes / 1024, 2, '.', '' ) ) );
		} else {
			$size	=	CBTxt::Th( 'FILESIZE_FORMATTED_B', '%%COUNT%% B|%%COUNT%% Bs', array( '%%COUNT%%' => (float) number_format( $bytes, 2, '.', '' ) ) );
		}

		return $size;
	}
}

class CBfield_rating extends cbFieldHandler {

	/**
	 * Checks if user has vote access to this field
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  null|int    $myId
	 * @return boolean
	 */
	private function getVoteAccess( &$field, &$user, $myId = null ) {
		global $_CB_framework;

		static $cache					=	array();

		if ( $myId === null ) {
			$myId						=	(int) $_CB_framework->myId();
		} else {
			$myId						=	(int) $myId;
		}

		$userId							=	(int) $user->get( 'id' );
		$fieldId						=	(int) $field->get( 'fieldid' );

		$cacheId						=	$myId . $userId . $fieldId;

		if ( ! isset( $cache[$cacheId] ) ) {
			$ratingAccess				=	(int) $field->params->get( 'rating_access', 1 );
			$excludeSelf				=	(int) $field->params->get( 'rating_access_exclude', 0 );
			$includeSelf				=	(int) $field->params->get( 'rating_access_include', 0 );
			$viewAccessLevel			=	(int) $field->params->get( 'rating_access_custom', 1 );
			$access						=	false;

			switch ( $ratingAccess ) {
				case 8:
					if ( Application::MyUser()->canViewAccessLevel( $viewAccessLevel ) && ( ( ( $userId == $myId ) && ( ! $excludeSelf ) ) || ( $userId != $myId ) ) ) {
						$access			=	true;
					}
					break;
				case 7:
					if ( Application::MyUser()->isModeratorFor( Application::User( (int) $userId ) ) && ( ( ( $userId == $myId ) && ( ! $excludeSelf ) ) || ( $userId != $myId ) ) ) {
						$access			=	true;
					}
					break;
				case 6:
					if ( $userId != $myId ) {
						$cbConnection	=	new cbConnection( $userId );

						if ( $cbConnection->getConnectionDetails( $userId, $myId ) !== false ) {
							$access		=	true;
						}
					} elseif ( ( $userId == $myId ) && $includeSelf ) {
						$access			=	true;
					}
					break;
				case 5:
					if ( ( $myId == 0 ) && ( $userId != $myId ) || ( ( $userId == $myId ) && $includeSelf ) ) {
						$access			=	true;
					}
					break;
				case 4:
					if ( ( $myId > 0 ) && ( ( ( $userId == $myId ) && ( ! $excludeSelf ) ) || ( $userId != $myId ) ) ) {
						$access			=	true;
					}
					break;
				case 3:
					if ( $userId != $myId ) {
						$access			=	true;
					}
					break;
				case 2:
					if ( $userId == $myId ) {
						$access			=	true;
					}
					break;
				case 1:
				default:
					if ( ( ( $userId == $myId ) && ( ! $excludeSelf ) ) || ( $userId != $myId ) ) {
						$access			=	true;
					}
					break;
			}

			$cache[$cacheId]			=	$access;
		}

		return $cache[$cacheId];
	}

	/**
	 * Get viewing users current vote
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  null|int    $myId
	 * @return int
	 */
	private function getCurrentVote( &$field, &$user, $myId = null ) {
		global $_CB_database, $_CB_framework;

		static $cache				=	array();

		if ( $myId === null ) {
			$myId					=	(int) $_CB_framework->myId();
		} else {
			$myId					=	(int) $myId;
		}

		$userId						=	(int) $user->get( 'id' );
		$fieldId					=	(int) $field->get( 'fieldid' );
		$ipAddresses				=	cbGetIParray();
		$ipAddress					=	trim( array_shift( $ipAddresses ) );

		$cacheId					=	md5( ( $myId == 0 ? $ipAddress : $myId ) . $userId . $fieldId );

		if ( ! isset( $cache[$cacheId] ) ) {
			$query					=	'SELECT ' . $_CB_database->NameQuote( 'rating' )
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_ratings' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'field' )
									.	"\n AND " . $_CB_database->NameQuote( 'item' ) . " = " . $fieldId
									.	"\n AND " . $_CB_database->NameQuote( 'target' ) . " = " . $userId
									.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . $myId;
			if ( $myId == 0 ) {
				$query				.=	"\n AND " . $_CB_database->NameQuote( 'ip_address' ) . " = " . $_CB_database->Quote( $ipAddress );
			}
			$_CB_database->setQuery( $query );
			$cache[$cacheId]		=	$_CB_database->loadResult();
		}

		return $cache[$cacheId];
	}

	/**
	 * Inserts a new vote into the database
	 *
	 * @param  float       $value
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  null|int    $myId
	 * @return float
	 */
	private function insertVote( $value, &$field, &$user, $myId = null ) {
		global $_CB_database, $_CB_framework;

		if ( $myId === null ) {
			$myId			=	(int) $_CB_framework->myId();
		} else {
			$myId			=	(int) $myId;
		}

		$userId				=	(int) $user->get( 'id' );
		$fieldId			=	(int) $field->get( 'fieldid' );
		$ipAddresses		=	cbGetIParray();
		$ipAddress			=	trim( array_shift( $ipAddresses ) );

		if ( ! $value ) {
			$query			=	'DELETE'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_ratings' )
							.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'field' )
							.	"\n AND " . $_CB_database->NameQuote( 'item' ) . " = " . $fieldId
							.	"\n AND " . $_CB_database->NameQuote( 'target' ) . " = " . $userId
							.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . $myId;
			if ( $myId == 0 ) {
				$query		.=	"\n AND " . $_CB_database->NameQuote( 'ip_address' ) . " = " . $_CB_database->Quote( $ipAddress );
			}
			$_CB_database->setQuery( $query );
			$_CB_database->query();
		} else {
			$query			=	'SELECT ' . $_CB_database->NameQuote( 'id' )
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_ratings' )
							.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'field' )
							.	"\n AND " . $_CB_database->NameQuote( 'item' ) . " = " . $fieldId
							.	"\n AND " . $_CB_database->NameQuote( 'target' ) . " = " . $userId
							.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . $myId;
			if ( $myId == 0 ) {
				$query		.=	"\n AND " . $_CB_database->NameQuote( 'ip_address' ) . " = " . $_CB_database->Quote( $ipAddress );
			}
			$_CB_database->setQuery( $query );
			$ratingId		=	$_CB_database->loadResult();

			if ( $ratingId ) {
				$query		=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler_ratings' )
							.	"\n SET " . $_CB_database->NameQuote( 'rating' ) . " = " . (float) $value
							.	', ' . $_CB_database->NameQuote( 'ip_address' ) . " = " . $_CB_database->Quote( $ipAddress )
							.	', ' . $_CB_database->NameQuote( 'date' ) . ' = ' . $_CB_database->Quote( $_CB_framework->getUTCDate() )
							.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $ratingId;
				$_CB_database->setQuery( $query );
				$_CB_database->query();
			} else {
				$query		=	'INSERT INTO ' . $_CB_database->NameQuote( '#__comprofiler_ratings' )
							.	"\n ("
							.		$_CB_database->NameQuote( 'user_id' )
							.		', ' . $_CB_database->NameQuote( 'type' )
							.		', ' . $_CB_database->NameQuote( 'item' )
							.		', ' . $_CB_database->NameQuote( 'target' )
							.		', ' . $_CB_database->NameQuote( 'rating' )
							.		', ' . $_CB_database->NameQuote( 'ip_address' )
							.		', ' . $_CB_database->NameQuote( 'date' )
							.	')'
							.	"\n VALUES ("
							.		$myId
							.		', ' . $_CB_database->Quote( 'field' )
							.		', ' . $fieldId
							.		', ' . $userId
							.		', ' . (float) $value
							.		', ' . $_CB_database->Quote( $ipAddress )
							.		', ' . $_CB_database->Quote( $_CB_framework->getUTCDate() )
							.	')';
				$_CB_database->setQuery( $query );
				$_CB_database->query();
			}
		}

		$query				=	'SELECT ROUND( AVG( ' . $_CB_database->NameQuote( 'rating' ) . ' ), 1 )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_ratings' )
							.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'field' )
							.	"\n AND " . $_CB_database->NameQuote( 'item' ) . " = " . $fieldId
							.	"\n AND " . $_CB_database->NameQuote( 'target' ) . " = " . $userId;
		$_CB_database->setQuery( $query );

		return $_CB_database->loadResult();
	}

	/**
	 * Get the number of a fields votes
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @return mixed
	 */
	private function getVoteCount( &$field, &$user ) {
		global $_CB_database;

		static $cache				=	array();

		$userId						=	(int) $user->get( 'id' );
		$fieldId					=	(int) $field->get( 'fieldid' );

		$cacheId					=	$userId . $fieldId;

		if ( ! isset( $cache[$cacheId] ) ) {
			$query					=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_ratings' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'field' )
									.	"\n AND " . $_CB_database->NameQuote( 'item' ) . " = " . $fieldId
									.	"\n AND " . $_CB_database->NameQuote( 'target' ) . " = " . $userId;
			$_CB_database->setQuery( $query );
			$cache[$cacheId]		=	(int) $_CB_database->loadResult();
		}

		return $cache[$cacheId];
	}

	/**
	 * output rating field html display
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason
	 * @return string
	 */
	private function getRatingHTML( &$field, &$user, $reason ) {
		global $_CB_framework;

		static $JS_loaded			=	0;

		$userId						=	(int) $user->get( 'id' );
		$fieldName					=	$field->get( 'name' );

		if ( in_array( $reason, array( 'edit', 'register' ) ) && ( (int) $_CB_framework->myId() != $userId ) ) {
			$myId					=	$userId;
		} else {
			$myId					=	null;
		}

		$value						=	(float) $user->get( $fieldName );

		$readOnly					=	$this->_isReadOnly( $field, $user, $reason );
		$required					=	$this->_isRequired( $field, $user, $reason );

		$maxRating					=	(int) $field->params->get( 'rating_number', 5 );
		$voteCount					=	(int) $field->params->get( 'rating_votes', 0 );
		$voteNumerical				=	(int) $field->params->get( 'rating_numerical', 0 );
		$ratingStep					=	(float) number_format( $field->params->get( 'rating_step', '1.0' ), 1, '.', '' );
		$forceWhole					=	(int) $field->params->get( 'rating_whole', 0 );
		$userlistVote				=	(int) $field->params->get( 'rating_list', 0 );
		$userlistAccess				=	false;

		if ( ! $ratingStep ) {
			$ratingStep				=	(float) '1.0';
		}

		if ( $reason == 'list' ) {
			$fieldName				=	$fieldName . $userId;

			if ( $userlistVote ) {
				$userlistAccess		=	true;
			}
		}

		$canVote					=	( ( ! $readOnly ) && $this->getVoteAccess( $field, $user, $myId ) && ( ( ( $reason == 'list' ) && $userlistAccess ) || ( $reason != 'list' ) ) );

		if ( $forceWhole ) {
			$value					=	(float) round( $value );
		}

		if ( $value > $maxRating ) {
			$value					=	(float) $maxRating;
		} elseif ( $value < 0 ) {
			$value					=	(float) '0';
		}

		$js							=	null;
		$return						=	null;

		if ( ( ! in_array( $reason, array( 'edit', 'register' ) ) ) && ( $value || ( ( ! $value ) && ( ! $canVote ) ) ) ) {
			$return					.=		'<div id="' . $fieldName . 'Total" class="cbRatingFieldTotal">'
									.			'<div class="rateit" data-rateit-value="' . $value . '" data-rateit-ispreset="true" data-rateit-readonly="true" data-rateit-min="0" data-rateit-max="' . $maxRating . '"></div>';

			if ( $voteNumerical && $value ) {
				$return				.=			' <span class="cbRatingFieldNumerical" title="' . htmlspecialchars( CBTxt::T( 'Rating' ) ) . '"><small>(' . $value . ')</small></span>';
			}

			if ( $voteCount ) {
				$count				=	$this->getVoteCount( $field, $user );

				if ( $count ) {
					$return			.=			' <span class="cbRatingFieldCount" title="' . htmlspecialchars( CBTxt::T( 'Number of Votes' ) ) . '"><small>(' . $count . ')</small></span>';
				}
			}

			$return					.=		'</div>';
		}

		if ( in_array( $reason, array( 'edit', 'register' ) ) && ( (int) $_CB_framework->myId() != $userId ) ) {
			$myId					=	$userId;
		} else {
			$myId					=	null;
		}

		if ( $canVote ) {
			$rating					=	(float) $this->getCurrentVote( $field, $user, $myId );

			if ( $rating > $maxRating ) {
				$rating				=	(float) $maxRating;
			} elseif ( $rating < 0 ) {
				$rating				=	(float) '0';
			}

			$return					.=		'<div id="' . $fieldName . 'Rating" class="cbRatingFieldRating">'
									.			'<input type="hidden" id="' . $fieldName . '" name="' . $fieldName . '" value="' . $rating . '" />'
									.			'<div class="rateit" data-field="' . $field->get( 'name' ) . '" data-target="' . $userId . '" data-rateit-backingfld="#' . $fieldName . '" data-rateit-step="' . $ratingStep . '" data-rateit-value="' . $rating . '" data-rateit-ispreset="true" data-rateit-resetable="' . ( $required ? 'false' : 'true' ) . '" data-rateit-min="0" data-rateit-max="' . $maxRating . '"></div>'
									.		'</div>';
		}

		if ( $return ) {
			$return					=	'<div id="' . $fieldName . 'Container" class="cbRatingField' . ( $userlistAccess ? ' cbClicksInside' : null ) . '">'
									.		$return
									.	'</div>';
		}

		if ( ! $JS_loaded++ ) {
			if ( ! in_array( $reason, array( 'edit', 'register' ) ) ) {
				cbGetRegAntiSpamInputTag();

				$cbGetRegAntiSpams	=	cbGetRegAntiSpams();

				$js					=	"$( '.cbRatingField' ).on( 'rated reset', '.rateit', function ( e ) {"
									.		"var rating = $( this ).parents( '.cbRatingField' );"
									.		"var vote = $( this ).rateit( 'value' );"
									.		"var field = $( this ).data( 'field' );"
									.		"var target = $( this ).data( 'target' );"
									.		"$.ajax({"
									.			"type: 'POST',"
									.			"url: '" . addslashes( cbSef( 'index.php?option=com_comprofiler&view=fieldclass&function=savevalue&reason=' . urlencode( $reason ), false, 'raw' ) ) . "',"
									.			"data: {"
									.				"field: field,"
									.				"user: target,"
									.				"value: vote,"
									.				cbSpoofField() . ": '" . addslashes( cbSpoofString( null, 'fieldclass' ) ) . "',"
									.				cbGetRegAntiSpamFieldName() . ": '" . addslashes( $cbGetRegAntiSpams[0] ) . "'"
									.			"}"
									.		"}).done( function( data, textStatus, jqXHR ) {"
									.			"rating.find( '.cbRatingFieldTotal,.alert' ).remove();"
									.			"rating.prepend( data );"
									.			"rating.find( '.cbRatingFieldTotal .rateit' ).rateit();"
									.		"});"
									.	"});";
			}

			$_CB_framework->outputCbJQuery( $js, 'rateit' );
		}

		return $return;
	}

	/**
	 * Direct access to field for custom operations, like for Ajax
	 *
	 * WARNING: direct unchecked access, except if $user is set, then check well for the $reason ...
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  array       $postdata
	 * @param  string      $reason     'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string                  Expected output.
	 */
	public function fieldClass( &$field, &$user, &$postdata, $reason ) {
		global $_CB_database, $_PLUGINS;

		parent::fieldClass( $field, $user, $postdata, $reason ); // Performs spoof check

		$userId								=	(int) $user->get( 'id' );
		$fieldName							=	$field->get( 'name' );

		$readOnly							=	$this->_isReadOnly( $field, $user, $reason );

		$maxRating							=	(int) $field->params->get( 'rating_number', 5 );
		$voteCount							=	(int) $field->params->get( 'rating_votes', 0 );
		$voteNumerical						=	(int) $field->params->get( 'rating_numerical', 0 );
		$forceWhole							=	(int) $field->params->get( 'rating_whole', 0 );

		if ( ( cbGetParam( $_GET, 'function', null ) == 'savevalue' ) && ( ( ! $readOnly ) && $this->getVoteAccess( $field, $user ) ) && $userId ) {
			$oldUserComplete				=	new UserTable( $field->getDbo() );

			foreach ( array_keys( get_object_vars( $user ) ) as $k ) {
				if ( substr( $k, 0, 1 ) != '_' ) {
					$oldUserComplete->set( $k, $user->get( $k ) );
				}
			}

			$value							=	(float) stripslashes( cbGetParam( $postdata, 'value' ) );

			if ( $value > $maxRating ) {
				$value						=	(float) $maxRating;
			} elseif ( $value < 0 ) {
				$value						=	(float) '0';
			}

			$postdata[$fieldName]			=	$value;

			if ( $this->validate( $field, $user, $fieldName, $value, $postdata, $reason ) && ( (float) $this->getCurrentVote( $field, $user ) !== (float) $value ) ) {
				$user->set( $fieldName, (float) $this->insertVote( $value, $field, $user ) );

				$_PLUGINS->trigger( 'onBeforeUserUpdate', array( &$user, &$user, &$oldUserComplete, &$oldUserComplete ) );

				$query						=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
											.	"\n SET " . $_CB_database->NameQuote( $fieldName ) . " = " . (float) $user->get( $fieldName )
											.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . $userId;
				$_CB_database->setQuery( $query );

				if ( $_CB_database->query() ) {
					$_PLUGINS->trigger( 'onAfterUserUpdate', array( &$user, &$user, $oldUserComplete ) );
				}
			}
		}

		$value								=	(float) $user->get( $fieldName );

		if ( $reason == 'list' ) {
			$fieldName						=	$fieldName . $userId;
		}

		if ( $forceWhole ) {
			$value							=	(float) round( $value );
		}

		if ( $value > $maxRating ) {
			$value							=	(float) $maxRating;
		} elseif ( $value < 0 ) {
			$value							=	(float) '0';
		}

		$return								=	null;

		if ( ( ! in_array( $reason, array( 'edit', 'register' ) ) ) && $value ) {
			$return							.=	'<div id="' . $fieldName . 'Total" class="cbRatingFieldTotal">'
											.		'<div class="rateit" data-rateit-value="' . $value . '" data-rateit-ispreset="true" data-rateit-readonly="true" data-rateit-min="0" data-rateit-max="' . $maxRating . '"></div>';

			if ( $voteNumerical && $value ) {
				$return						.=		' <span class="cbRatingFieldNumerical" title="' . htmlspecialchars( CBTxt::T( 'Rating' ) ) . '"><small>(' . $value . ')</small></span>';
			}

			if ( $voteCount ) {
				$count						=	$this->getVoteCount( $field, $user );

				if ( $count ) {
					$return					.=		' <span class="cbRatingFieldCount" title="' . htmlspecialchars( CBTxt::T( 'Number of Votes' ) ) . '"><small>(' . $count . ')</small></span>';
				}
			}

			$return							.=	'</div>';
		}

		return $return;
	}

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$return						=	null;

		switch ( $output ) {
			case 'html':
			case 'htmledit':
				if ( $reason == 'search' ) {
					$fieldName		=	$field->get( 'name' );
					$minNam			=	$fieldName . '__minval';
					$maxNam			=	$fieldName . '__maxval';

					$minVal			=	$user->get( $minNam );
					$maxVal			=	$user->get( $maxNam );

					$field->set( 'name', $minNam );

					$minHtml		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $minVal, null );

					$field->set( 'name', $maxNam );

					$maxHtml		=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'text', $maxVal, null );

					$field->set( 'name', $fieldName );

					$return			=	$this->_fieldSearchRangeModeHtml( $field, $user, $output, $reason, null, $minHtml, $maxHtml, $list_compare_types );
				} else {
					$return			=	$this->formatFieldValueLayout( $this->getRatingHTML( $field, $user, $reason ), $reason, $field, $user );
				}
				break;
			default:
				$return				=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}

		return $return;
	}

	/**
	 * Mutator:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		$maxRating				=	(int) $field->params->get( 'rating_number', 5 );

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		foreach ( $field->getTableColumns() as $col ) {
			$value				=	cbGetParam( $postdata, $col );

			if ( ( $value !== null ) && ( ! is_array( $value ) ) ) {
				$value			=	(float) stripslashes( $value );

				if ( $value > $maxRating ) {
					$value		=	(float) $maxRating;
				} elseif ( $value < 0 ) {
					$value		=	(float) '0';
				}

				$this->validate( $field, $user, $col, $value, $postdata, $reason );
			}
		}
	}

	/**
	 * Mutator:
	 * Prepares field data commit
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function commitFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework;

		$maxRating				=	(int) $field->params->get( 'rating_number', 5 );

		foreach ( $field->getTableColumns() as $col ) {
			$value				=	cbGetParam( $postdata, $col );

			if ( ( $value !== null ) && ( ! is_array( $value ) ) ) {
				$value			=	(float) stripslashes( $value );

				if ( $value > $maxRating ) {
					$value		=	(float) $maxRating;
				} elseif ( $value < 0 ) {
					$value		=	(float) '0';
				}

				if ( $this->validate( $field, $user, $col, $value, $postdata, $reason ) && ( (float) $this->getCurrentVote( $field, $user ) !== (float) $value ) ) {
					$userId		=	(int) $user->get( 'id' );

					if ( in_array( $reason, array( 'edit', 'register' ) ) && ( (int) $_CB_framework->myId() != $userId ) ) {
						$myId	=	$userId;
					} else {
						$myId	=	null;
					}

					$rating		=	(float) $this->insertVote( $value, $field, $user, $myId );

					$this->_logFieldUpdate( $field, $user, $reason, $user->get( $col ), $rating );

					$user->set( $col, $rating );
				}
			}
		}
	}

	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals  RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason      'edit' for save profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart[]
	 */
	public function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ) {
		$query								=	array();

		foreach ( $field->getTableColumns() as $col ) {
			$minNam							=	$col . '__minval';
			$maxNam							=	$col . '__maxval';
			$searchMode						=	$this->_bindSearchRangeMode( $field, $searchVals, $postdata, $minNam, $maxNam, $list_compare_types );

			if ( $searchMode ) {
				$minVal						=	(float) cbGetParam( $postdata, $minNam, 0 );
				$maxVal						=	(float) cbGetParam( $postdata, $maxNam, 0 );

				if ( $minVal && ( cbGetParam( $postdata, $minNam, '' ) !== '' ) ) {
					$searchVals->$minNam	=	$minVal;
					$operator				=	( $searchMode == 'isnot' ? ( $minVal == $maxVal ? '<' : '<=' ) : '>=' );
					$min					=	$this->_floatToSql( $field, $col, $minVal, $operator, $searchMode );
				} else {
					$min					=	null;
				}

				if ( $maxVal && ( cbGetParam( $postdata, $maxNam, '' ) !== '' ) ) {
					$searchVals->$maxNam	=	$maxVal;
					$operator				=	( $searchMode == 'isnot' ? ( $maxVal == $minVal ? '>' : '>=' ) : '<=' );
					$max					=	$this->_floatToSql( $field, $col, $maxVal, $operator, $searchMode );
				} else {
					$max					=	null;
				}

				if ( $min && $max ) {
					$sql					=	new cbSqlQueryPart();
					$sql->tag				=	'column';
					$sql->name				=	$col;
					$sql->table				=	$field->table;
					$sql->type				=	'sql:operator';
					$sql->operator			=	( $searchMode == 'isnot' ? 'OR' : 'AND' );
					$sql->searchmode		=	$searchMode;

					$sql->addChildren( array( $min, $max ) );

					$query[]				=	$sql;
				} elseif ( $min ) {
					$query[]				=	$min;
				} elseif ( $max ) {
					$query[]				=	$max;
				}
			}
		}

		return $query;
	}

	/**
	 * Internal function to create an SQL query part based on a comparison operator
	 *
	 * @param  FieldTable      $field
	 * @param  string          $col
	 * @param  int             $value
	 * @param  string          $operator
	 * @param  string          $searchMode
	 * @return cbSqlQueryPart
	 */
	protected function _floatToSql( &$field, $col, $value, $operator, $searchMode ) {
		$value				=	(float) $value;

		$sql				=	new cbSqlQueryPart();
		$sql->tag			=	'column';
		$sql->name			=	$col;
		$sql->table			=	$field->table;
		$sql->type			=	'sql:field';
		$sql->operator		=	$operator;
		$sql->value			=	$value;
		$sql->valuetype		=	'const:float';
		$sql->searchmode	=	$searchMode;

		return $sql;
	}
}

class CBfield_points extends CBfield_integer
{
	/**
	 * Checks if user has increment access to this field
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @return boolean
	 */
	private function getIncrementAccess( &$field, &$user ) {
		global $_CB_framework, $_CB_database;

		static $cache					=	array();

		$myId							=	(int) $_CB_framework->myId();
		$userId							=	(int) $user->get( 'id' );
		$fieldId						=	(int) $field->get( 'fieldid' );
		$ipAddresses					=	cbGetIParray();
		$ipAddress						=	trim( array_shift( $ipAddresses ) );

		$incrementDelay					=	$field->params->get( 'points_inc_delay', null );
		$customDelay					=	$field->params->get( 'points_inc_delay_custom', null );

		$cacheId						=	$myId . $userId . $fieldId;

		if ( ! isset( $cache[$cacheId] ) ) {
			$ratingAccess				=	(int) $field->params->get( 'points_access', 1 );
			$excludeSelf				=	(int) $field->params->get( 'points_access_exclude', 0 );
			$includeSelf				=	(int) $field->params->get( 'points_access_include', 0 );
			$viewAccessLevel			=	(int) $field->params->get( 'points_access_custom', 1 );
			$access						=	false;

			switch ( $ratingAccess ) {
				case 8:
					if ( Application::MyUser()->canViewAccessLevel( $viewAccessLevel ) && ( ( ( $userId == $myId ) && ( ! $excludeSelf ) ) || ( $userId != $myId ) ) ) {
						$access			=	true;
					}
					break;
				case 7:
					if ( Application::MyUser()->isModeratorFor( Application::User( (int) $userId ) ) && ( ( ( $userId == $myId ) && ( ! $excludeSelf ) ) || ( $userId != $myId ) ) ) {
						$access			=	true;
					}
					break;
				case 6:
					if ( $userId != $myId ) {
						$cbConnection	=	new cbConnection( $userId );

						if ( $cbConnection->getConnectionDetails( $userId, $myId ) !== false ) {
							$access		=	true;
						}
					} else if ( ( $userId == $myId ) && $includeSelf ) {
						$access			=	true;
					}
					break;
				case 5:
					if ( ( $myId == 0 ) && ( $userId != $myId ) || ( ( $userId == $myId ) && $includeSelf ) ) {
						$access			=	true;
					}
					break;
				case 4:
					if ( ( $myId > 0 ) && ( ( ( $userId == $myId ) && ( ! $excludeSelf ) ) || ( $userId != $myId ) ) ) {
						$access			=	true;
					}
					break;
				case 3:
					if ( $userId != $myId ) {
						$access			=	true;
					}
					break;
				case 2:
					if ( $userId == $myId ) {
						$access			=	true;
					}
					break;
				case 1:
				default:
					if ( ( ( $userId == $myId ) && ( ! $excludeSelf ) ) || ( $userId != $myId ) ) {
						$access			=	true;
					}
					break;
			}

			$cache[$cacheId]			=	$access;
		}

		$canAccess						=	$cache[$cacheId];

		if ( $canAccess && $incrementDelay ) {
			$query						=	'SELECT ' . $_CB_database->NameQuote( 'date' )
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_ratings' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'field' )
										.	"\n AND " . $_CB_database->NameQuote( 'item' ) . " = " . $fieldId
										.	"\n AND " . $_CB_database->NameQuote( 'target' ) . " = " . $userId
										.	"\n AND " . $_CB_database->NameQuote( 'user_id' ) . " = " . $myId;
			if ( $myId == 0 ) {
				$query					.=	"\n AND " . $_CB_database->NameQuote( 'ip_address' ) . " = " . $_CB_database->Quote( $ipAddress );
			}
			$query						.=	"\n ORDER BY " . $_CB_database->NameQuote( 'date' ) . " DESC";
			$_CB_database->setQuery( $query, 0, 1 );
			$incrementDate				=	$_CB_database->loadResult();

			if ( $incrementDate ) {
				if ( $incrementDelay == 'FOREVER' ) {
					$canAccess			=	false;
				} elseif ( $incrementDelay == 'CUSTOM' ) {
					if ( $customDelay && ( $_CB_framework->getUTCTimestamp( strtoupper( $customDelay ), $_CB_framework->getUTCTimestamp( $incrementDate ) ) >= $_CB_framework->getUTCNow() ) ) {
						$canAccess		=	false;
					}
				} elseif ( $_CB_framework->getUTCTimestamp( $incrementDelay, $_CB_framework->getUTCTimestamp( $incrementDate ) ) >= $_CB_framework->getUTCNow() ) {
					$canAccess			=	false;
				}
			}
		}

		return $canAccess;
	}

	/**
	 * output points field html display
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason
	 * @param  boolean     $ajax
	 * @return string
	 */
	private function getPointsHTML( &$field, &$user, $reason, $ajax = false ) {
		global $_CB_framework;

		static $JS_loaded				=	0;

		$userId							=	(int) $user->get( 'id' );
		$fieldName						=	$field->get( 'name' );
		$value							=	(int) $user->get( $fieldName );

		$readOnly						=	$this->_isReadOnly( $field, $user, $reason );

		$maxPoints						=	(int) $field->params->get( 'integer_maximum', '1000000' );
		$pointsLayout					=	$field->params->get( 'points_layout', '' );
		$userlistIncrement				=	(int) $field->params->get( 'points_list', 0 );
		$userlistAccess					=	false;

		if ( $reason == 'list' ) {
			$fieldName					=	$fieldName . $userId;

			if ( $userlistIncrement ) {
				$userlistAccess			=	true;
			}
		}


		$canIncrement					=	( ( ! $readOnly ) && $this->getIncrementAccess( $field, $user ) && ( ( ( $reason == 'list' ) && $userlistAccess ) || ( $reason != 'list' ) ) );

		if ( $canIncrement ) {
			$plusCSS					=	$field->params->get( 'points_plus_class', '' );
			$minusCSS					=	$field->params->get( 'points_minus_class', '' );

			$plusIcon					=	'<span class="' . ( $plusCSS ? htmlspecialchars( $plusCSS ) : 'fa fa-plus-circle fa-lg' ) . '"></span>';
			$minusIcon					=	'<span class="' . ( $minusCSS ? htmlspecialchars( $minusCSS ) : 'fa fa-minus-circle fa-lg' ) . '"></span>';

			$replace					=	array(	'[plus]' => ( $value < $maxPoints ? '<span class="cbPointsFieldIncrement cbPointsFieldIncrementPlus" data-value="plus" data-field="' . $field->get( 'name' ) . '" data-target="' . $userId . '">' . $plusIcon . '</span>' : null ),
													'[minus]' => ( $value > 0 ? '<span class="cbPointsFieldIncrement cbPointsFieldIncrementMinus" data-value="minus" data-field="' . $field->get( 'name' ) . '" data-target="' . $userId . '">' . $minusIcon . '</span>' : null ),
													'[value]' => '<span class="cbPointsFieldValue">' . $value . '</span>',
												);

			if ( $pointsLayout ) {
				$pointsLayout			=	CBTxt::Th( $pointsLayout, null, $replace );
			} else {
				$pointsLayout			=	CBTxt::Th( 'POINTS_FIELD_LAYOUT_VALUE_PLUS_MINUS', '[value] [plus] [minus]', $replace );
			}

			if ( $ajax ) {
				$return					=	$pointsLayout;
			} else {
				$return					=	'<span id="' . $fieldName . 'Container" class="cbPointsField' . ( $userlistAccess ? ' cbClicksInside' : null ) . '">'
										.		$pointsLayout
										.	'</span>';

				if ( ! $JS_loaded++ ) {
					cbGetRegAntiSpamInputTag();

					$cbGetRegAntiSpams	=	cbGetRegAntiSpams();

					$js					=	"$( '.cbPointsField' ).on( 'click', '.cbPointsFieldIncrement', function ( e ) {"
										.		"var points = $( this ).parents( '.cbPointsField' );"
										.		"var increment = $( this ).data( 'value' );"
										.		"var field = $( this ).data( 'field' );"
										.		"var target = $( this ).data( 'target' );"
										.		"$.ajax({"
										.			"type: 'POST',"
										.			"url: '" . addslashes( cbSef( 'index.php?option=com_comprofiler&view=fieldclass&function=savevalue&reason=' . urlencode( $reason ), false, 'raw' ) ) . "',"
										.			"data: {"
										.				"field: field,"
										.				"user: target,"
										.				"value: increment,"
										.				cbSpoofField() . ": '" . addslashes( cbSpoofString( null, 'fieldclass' ) ) . "',"
										.				cbGetRegAntiSpamFieldName() . ": '" . addslashes( $cbGetRegAntiSpams[0] ) . "'"
										.			"}"
										.		"}).done( function( data, textStatus, jqXHR ) {"
										.			"points.html( data );"
										.		"});"
										.	"});";

					$_CB_framework->outputCbJQuery( $js );
				}
			}
		} else {
			$return						=	parent::getField( $field, $user, 'html', $reason, 0 );
		}

		return $return;
	}

	/**
	 * Direct access to field for custom operations, like for Ajax
	 *
	 * WARNING: direct unchecked access, except if $user is set, then check well for the $reason ...
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  array       $postdata
	 * @param  string      $reason     'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string                  Expected output.
	 */
	public function fieldClass( &$field, &$user, &$postdata, $reason ) {
		global $_CB_framework, $_CB_database, $_PLUGINS;

		parent::fieldClass( $field, $user, $postdata, $reason ); // Performs spoof check

		$myId								=	(int) $_CB_framework->myId();
		$userId								=	(int) $user->get( 'id' );
		$fieldId							=	(int) $field->get( 'fieldid' );
		$ipAddresses						=	cbGetIParray();
		$ipAddress							=	trim( array_shift( $ipAddresses ) );
		$fieldName							=	$field->get( 'name' );

		$readOnly							=	$this->_isReadOnly( $field, $user, $reason );

		if ( ( cbGetParam( $_GET, 'function', null ) == 'savevalue' ) && ( ( ! $readOnly ) && $this->getIncrementAccess( $field, $user ) ) && $userId ) {
			$oldUserComplete				=	new UserTable( $field->getDbo() );

			foreach ( array_keys( get_object_vars( $user ) ) as $k ) {
				if ( substr( $k, 0, 1 ) != '_' ) {
					$oldUserComplete->set( $k, $user->get( $k ) );
				}
			}

			$direction						=	stripslashes( cbGetParam( $postdata, 'value' ) );
			$value							=	(int) $user->get( $fieldName );

			if ( $direction == 'plus' ) {
				$increment					=	(int) $field->params->get( 'points_inc_plus', 1 );
				$value						+=	( $increment && ( $increment > 0 ) ? $increment : 0 );
			} elseif ( $direction == 'minus' ) {
				$increment					=	(int) $field->params->get( 'points_inc_minus', 1 );
				$value						-=	( $increment && ( $increment > 0 ) ? $increment : 0 );
				$increment					=	( $increment ? -$increment : 0 );
			} else {
				$increment					=	0;
			}

			$postdata[$fieldName]			=	$value;

			if ( $this->validate( $field, $user, $fieldName, $value, $postdata, $reason ) && $increment && ( (int) $user->get( $fieldName ) != $value ) ) {
				$query						=	'INSERT INTO ' . $_CB_database->NameQuote( '#__comprofiler_ratings' )
											.	"\n ("
											.		$_CB_database->NameQuote( 'user_id' )
											.		', ' . $_CB_database->NameQuote( 'type' )
											.		', ' . $_CB_database->NameQuote( 'item' )
											.		', ' . $_CB_database->NameQuote( 'target' )
											.		', ' . $_CB_database->NameQuote( 'rating' )
											.		', ' . $_CB_database->NameQuote( 'ip_address' )
											.		', ' . $_CB_database->NameQuote( 'date' )
											.	')'
											.	"\n VALUES ("
											.		$myId
											.		', ' . $_CB_database->Quote( 'field' )
											.		', ' . $fieldId
											.		', ' . $userId
											.		', ' . (float) $increment
											.		', ' . $_CB_database->Quote( $ipAddress )
											.		', ' . $_CB_database->Quote( $_CB_framework->getUTCDate() )
											.	')';
				$_CB_database->setQuery( $query );
				$_CB_database->query();

				$user->set( $fieldName, (int) $value );

				$_PLUGINS->trigger( 'onBeforeUserUpdate', array( &$user, &$user, &$oldUserComplete, &$oldUserComplete ) );

				$query						=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
											.	"\n SET " . $_CB_database->NameQuote( $fieldName ) . " = " . (int) $user->get( $fieldName )
											.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . $userId;
				$_CB_database->setQuery( $query );

				if ( $_CB_database->query() ) {
					$_PLUGINS->trigger( 'onAfterUserUpdate', array( &$user, &$user, $oldUserComplete ) );
				}
			}
		}

		return $this->getPointsHTML( $field, $user, $reason, true );
	}

	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		$return					=	null;

		switch ( $output ) {
			case 'html':
				$return			=	$this->formatFieldValueLayout( $this->getPointsHTML( $field, $user, $reason ), $reason, $field, $user );
				break;
			case 'htmledit':
				if ( ( $reason == 'search' ) || $this->getIncrementAccess( $field, $user ) ) {
					$return		=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				}
				break;
			default:
				$return			=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
				break;
		}

		return $return;
	}
}

class CBfield_terms extends CBfield_checkbox
{
	/**
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'list' for user-lists
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types )
	{
		global $_CB_framework;

		$return							=	null;

		if ( ( $output == 'htmledit' ) && ( $reason != 'search' ) ) {
			if ( Application::MyUser()->getUserId() != $user->get( 'id' ) ) {
				// Terms and Conditions should never be required to be accepted by a user other than the profile owner:
				$field->set( 'required', 0 );
			}

			$value						=	$user->get( $field->name );
			$termsOutput				=	$field->params->get( 'terms_output', 'text' );
			$termsType					=	CBTxt::T( $field->params->get( 'terms_type', null ) );
			$termsDisplay				=	$field->params->get( 'terms_display', 'modal' );
			$termsURL					=	$field->params->get( 'terms_url', null );
			$termsText					=	CBTxt::T( $field->params->get( 'terms_text', null ) );
			$termsWidth					=	$field->params->get( 'terms_width', 400 );
			$termsHeight				=	$field->params->get( 'terms_height', 200 );

			if ( ( ( $termsOutput == 'url' ) && ( ! $termsURL ) ) || ( ( $termsOutput == 'text' ) && ( ! $termsText ) ) ) {
				return parent::getField( $field, $user, $output, $reason, $list_compare_types );
			}

			if ( ! $termsType ) {
				$termsType				=	CBTxt::T( 'TERMS_AND_CONDITIONS', 'Terms and Conditions' );
			}

			if ( ! $termsWidth ) {
				$termsWidth				=	400;
			}

			if ( ! $termsHeight ) {
				$termsHeight			=	200;
			}

			if ( $termsDisplay == 'iframe' ) {
				if ( $termsOutput == 'url' ) {
					$return				.=	'<iframe class="cbTermsFrameURL" height="' . htmlspecialchars( $termsHeight ) . '" width="' . htmlspecialchars( $termsWidth ) . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
				} else {
					if ( is_numeric( $termsHeight ) ) {
						$termsHeight	.=	'px';
					}

					if ( is_numeric( $termsWidth ) ) {
						$termsWidth		.=	'px';
					}

					$return				.=	'<div class="cbTermsFrameText" style="height:' . htmlspecialchars( $termsHeight ) . ';width:' . htmlspecialchars( $termsWidth ) . ';overflow:auto;">' . $termsText . '</div>';
				}

				$label					=	CBTxt::Th( 'TERMS_FIELD_I_AGREE_ON_THE_ABOVE_CONDITIONS', 'I Agree to the above [type].', array( '[type]' => $termsType ) );
			} else {
				$attributes				=	' class="cbTermsLink"';

				if ( ( $termsOutput == 'text' ) && ( $termsDisplay == 'window' ) ) {
					$termsDisplay		=	'modal';
				}

				if ( $termsDisplay == 'modal' ) {
					// Tooltip height percentage would be based off window height (including scrolling); lets change it to be based off the viewport height:
					$termsHeight		=	( substr( $termsHeight, -1 ) == '%' ? (int) substr( $termsHeight, 0, -1 ) . 'vh' : $termsHeight );

					if ( $termsOutput == 'url' ) {
						$tooltip		=	'<iframe class="cbTermsModalURL" height="100%" width="100%" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
					} else {
						$tooltip		=	'<div class="cbTermsModalText" style="height:100%;width:100%;overflow:auto;">' . $termsText . '</div>';
					}

					$url				=	'javascript:void(0);';
					$attributes			.=	' ' . cbTooltip( $_CB_framework->getUi(), $tooltip, $termsType, array( $termsWidth, $termsHeight ), null, null, null, 'data-hascbtooltip="true" data-cbtooltip-modal="true"' );
				} else {
					$url				=	htmlspecialchars( $termsURL );
					$attributes			.=	' target="_blank"';
				}

				$label					=	CBTxt::Th( 'TERMS_FIELD_ACCEPT_URL_CONDITIONS', 'Accept <!--suppress HtmlUnknownTarget --><a href="[url]"[attributes]>[type]</a>', array( '[url]' => $url, '[attributes]' => $attributes, '[type]' => $termsType ) );
			}

			$inputName					=	$field->name;
			$translatedTitle			=	$this->getFieldTitle( $field, $user, 'html', $reason );
			$htmlDescription			=	$this->getFieldDescription( $field, $user, 'htmledit', $reason );
			$trimmedDescription			=	trim( strip_tags( $htmlDescription ) );

			$attributes					=	null;

			if ( $this->_isRequired( $field, $user, $reason ) ) {
				$attributes				.=	' class="required"';
			}

			$attributes					.=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, 'data-hascbtooltip="true"' ) : null );

			$return						.=	'<span class="cbSnglCtrlLbl">'
										.		'<label for="' . htmlspecialchars( $inputName ) . '" class="checkbox-inline">'
										.			'<input type="checkbox" id="' . htmlspecialchars( $inputName ) . '" name="' . htmlspecialchars( $inputName ) . '" value="1"' . ( $value == 1 ? ' checked="checked"' : null ) . $attributes . ' />'
										.			$label
										.		'</label>'
										.	'</span>'
										.	$this->_fieldIconsHtml( $field, $user, $output, $reason, null, $field->type, $value, 'input', null, true, $this->_isRequired( $field, $user, $reason ) && ! $this->_isReadOnly( $field, $user, $reason ) );
		} else {
			$return						.=	parent::getField( $field, $user, $output, $reason, $list_compare_types );
		}

		return $return;
	}

	/**
	 * Validator:
	 * Validates $value for $field->required and other rules
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user        RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  string      $columnName  Column to validate
	 * @param  string      $value       (RETURNED:) Value to validate, Returned Modified if needed !
	 * @param  array       $postdata    Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason      'edit' for save user edit, 'register' for save registration
	 * @return boolean                  True if validate, $this->_setErrorMSG if False
	 */
	public function validate( &$field, &$user, /** @noinspection PhpUnusedParameterInspection */ $columnName, &$value, /** @noinspection PhpUnusedParameterInspection */ &$postdata, $reason )
	{
		if ( Application::MyUser()->getUserId() != $user->get( 'id' ) ) {
			// Terms and Conditions should never be required to be accepted by a user other than the profile owner:
			$field->set( 'required', 0 );
		}

		return parent::validate( $field, $user, $columnName, $value, $postdata, $reason );
	}
}

/**
* Tab Class for User Stats display
*/
class getStatsTab extends cbTabHandler
{
}

/**
* Tab Class for Canvas display
*/
class getCanvasTab extends cbTabHandler
{
}

/**
* Tab Class for User Profile Page title display
*/
class getPageTitleTab  extends cbTabHandler
{
	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @return mixed                 Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab,$user,$ui )
	{
		global $ueConfig;

		$params	=	$this->params;
		$title	=	cbReplaceVars( $params->get( 'title', '_UE_PROFILE_TITLE_TEXT' ), $user );
		$name	=	getNameFormat( $user->name, $user->username, $ueConfig['name_format'] );

		$return	=	( sprintf( $title, $name ) ? '<div class="page-header cbProfileTitle"><h3>' . sprintf( $title, $name ) . '</h3></div>' : null )
				.	$this->_writeTabDescription( $tab, $user );

		return $return;
	}
}

/**
* Tab Class for User Profile Portrait/Avatar display
*/
class getPortraitTab extends cbTabHandler
{
	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @return mixed                 Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		return $this->_writeTabDescription( $tab, $user, 'cbPortraitDescription' );
	}
}

/**
* Tab Class for User Profile EDIT Contacts special fields display
*/
class getContactTab extends cbTabHandler {
	/**
	 * Generates the HTML to display the user edit tab
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @return mixed                 either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getEditTab( $tab, $user, $ui )
	{
		return $this->_writeTabDescription( $tab, $user );
	}
}
