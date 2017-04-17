<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 2:27 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Database\DatabaseUpgrade;
use CBLib\Language\CBTxt;
use CBLib\Registry\Registry;
use CBLib\Xml\SimpleXMLElement;
use CB\Database\Table\FieldTable;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbFieldHandler Class implementation
 * Field Class for handling the CB field api
 */
class cbFieldHandler extends cbPluginHandler
{
	/**
	 * Plugin of this field
	 * @var PluginTable
	 */
	private $_plugin	=	null;
	/**
	 * XML of the Plugin of this field
	 * @var SimpleXMLElement
	 */
	private $_xml		=	null;
	/**
	 * XML of this field
	 * @var SimpleXMLElement
	 */
	private $_fieldXml	=	null;

	/**
	 * Constructor named old-fashion for backwards compatibility reason
	 * until all classes extending cbFieldHandler call parent::__construct() instead of $this->cbFieldHandler()
	 * @deprecated 2.0 use parent::__construct() instead.
	 */
	public function cbFieldHandler( )
	{
		parent::__construct();
	}

	/**
	 * Overridable methods:
	 */

	/**
	 * Initializer:
	 * Puts the default value of $field into $user (for registration or new user in backend)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 */
	public function initFieldToDefault( &$field, &$user, $reason )
	{
		foreach ( $field->getTableColumns() as $col ) {
			if ( $reason == 'search' ) {
				$user->$col							=	null;
			} else {
				$user->$col							=	$field->default;
			}
		}
	}

	/**
	 * Formatter:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output               'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting           'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string      $reason               'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getFieldRow( &$field, &$user, $output, $formatting, $reason, $list_compare_types )
	{
		global $ueConfig;

		$results									=	null;
		$oValue										=	$this->getField( $field, $user, $output, $reason, $list_compare_types );

		if ( $reason == 'edit' ) {
			$displayMode							=	$field->get( 'edit', 1 );
		} elseif ( $reason == 'register' ) {
			$displayMode							=	$field->get( 'registration', 1 );
		} elseif ( $reason == 'search' ) {
			$displayMode							=	1;
		} else {
			$displayMode							=	$field->get( 'profile', 1 );
		}

		$displayTitle								=	( in_array( $displayMode, array( 3, 4 ) ) ? false : true );

		if ( ( ! ( $oValue != null || trim($oValue) != '' ) )
			&& ( $output == 'html' )
			&& isset( $ueConfig['showEmptyFields'] ) && ( $ueConfig['showEmptyFields'] == 1 )
			&& ( $reason != 'search' )
			&& $displayTitle
		)
		{
			$oValue									=	cbReplaceVars( $ueConfig['emptyFieldsText'], $user );
		}

		if ( $oValue != null || trim($oValue) != '' ) {
			if ( cbStartOfStringMatch( $output, 'html' ) ) {
				$results							=	$this->renderFieldHtml( $field, $user, $oValue, $output, $formatting, $reason, array() );
			} else {
				$results							=	$oValue;
			}
		}

		return $results;
	}

	/**
	 * Renders a field row with title and description into $output html formating
	 *
	 * @param  FieldTable  $field       Using: name, type, title, description, fieldid, profile, displaytitle
	 * @param  UserTable   $user        User being rendered
	 * @param  string      $oValue      HTML of the field value to render
	 * @param  string      $output      'html', 'htmledit', NOT SUPPORTED IN THIS FUNCTION: 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist'
	 * @param  string      $formatting  'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string      $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  string[]    $rowClasses  CSS classes for the row
	 * @return string                   HTML rendering
	 */
	protected function renderFieldHtml( $field, $user, $oValue, $output, $formatting, $reason, $rowClasses )
	{
		global $_CB_OneTwoRowsStyleToggle;

		$results							=	null;

		$translatedTitle					=	$this->getFieldTitle( $field, $user, $output, $reason );

		if ( $reason == 'edit' ) {
			$displayMode					=	$field->get( 'edit', 1 );
		} elseif ( $reason == 'register' ) {
			$displayMode					=	$field->get( 'registration', 1 );
		} elseif ( $reason == 'search' ) {
			$displayMode					=	1;
		} else {
			$displayMode					=	$field->get( 'profile', 1 );
		}

		$twoLines							=	( in_array( $displayMode, array( 2, 4 ) ) ? true : false );
		$translatedTitle					=	( in_array( $displayMode, array( 3, 4 ) ) ? '' : $translatedTitle );
		$labelFor							=	( ( ( $output == 'htmledit' ) && $field->name ) ? htmlspecialchars( $field->name ) : 'cbfv_' . $field->fieldid );

		if ( $field->cssclass ) {
			$rowClasses[]					=	$field->cssclass;
		}

		if ( preg_match( '/^<(select|input|textarea|button)/i', trim( $oValue ), $matches ) ) {
			$tag							=	$matches[1];
		} else {
			$tag							=	null;
		}

		switch ( $formatting ) {
			case 'table':
				// ?
				break;

			case 'tr':
				if ( ( $field->name == 'avatar' ) && ( $reason == 'profile' ) ) {
					$rowClasses[]				=	'cbavatar_tr';			// ugly temporary fix
				} else {
					$rowClasses[] 				=	'sectiontableentry' . $_CB_OneTwoRowsStyleToggle;

					$_CB_OneTwoRowsStyleToggle	=	( $_CB_OneTwoRowsStyleToggle == 1 ? 2 : 1 );
				}

				$rowClasses[]					=	'cb_table_line';
				$rowClasses[]					=	'cbft_' . $field->type;

				if ( $tag ) {
					$rowClasses[]				=	'cbtt_' . $tag;
				}

				$results		.=	'<tr id="cbfr_' . $field->fieldid . '" class="' . implode( ' ', $rowClasses ) . ( $twoLines ? ( trim( $translatedTitle ) === '' ? ' cb_table_line_field' : ' cb_table_line_title' ) : null ) . '">';

				if ( ( trim( $translatedTitle ) === '' ) && $twoLines ) {
					$results	.=		'<td id="cbfv_' . $field->fieldid . '" class="fieldCell" colspan="2" style="width: 100%;">'
								.			$oValue
								.		'</td>';
				} else {
					$results	.=		'<td class="titleCell"' . ( $twoLines ? ' colspan="2"' : null ) . ' style="width: ' . ( $twoLines ? 100 : 25 ) . '%;">'
								.			( trim( $translatedTitle ) === '' ? null : '<label for="' . $labelFor . '" id="cblab' . $labelFor . '">' . $translatedTitle . '</label>' )
								.		'</td>';

					if ( $twoLines ) {
						$results .=	'</tr>'
								.	'<tr id="cbfrd_' . $field->fieldid . '" class="' . implode( ' ', $rowClasses ) . ' cb_table_line_field">';
					}

					$results	.=		'<td id="cbfv_' . $field->fieldid . '" class="fieldCell"' . ( $twoLines ? ' colspan="2"' : null ) . ' style="width: ' . ( $twoLines ? 100 : 75 ) . '%;">'
								.			$oValue
								.		'</td>';
				}

				$results		.=	'</tr>';
				break;

			case 'td':
				$rowClasses[]				=	'fieldCell';
				$rowClasses[]				=	'cbft_' . $field->type;

				if ( $tag ) {
					$rowClasses[]			=	'cbtt_' . $tag;
				}

				$results					.=		'<td id="cbfv_' . $field->fieldid . '" class="' . implode( ' ', $rowClasses ) . '">'
											.			$oValue
											.		'</td>';
				break;

			case 'div':
				$rowClasses[]				=	'sectiontableentry' . $_CB_OneTwoRowsStyleToggle;

				$_CB_OneTwoRowsStyleToggle	=	( $_CB_OneTwoRowsStyleToggle == 1 ? 2 : 1 );

				$rowClasses[]				=	'cbft_' . $field->type;

				if ( $tag ) {
					$rowClasses[]			=	'cbtt_' . $tag;
				}

				$rowClasses[]				=	'form-group cb_form_line';
				$rowClasses[]				=	'clearfix';

				if ( $twoLines ) {
					$rowClasses[]			=	'cbtwolinesfield';
				}

				$results					.=	'<div class="' . implode( ' ', $rowClasses ) . '" id="cbfr_' . $field->fieldid . '">';

				if ( trim( $translatedTitle ) !== '' ) {
					$results				.=		'<label for="' . $labelFor . '" id="cblab' . $labelFor . '" class="control-label col-sm-' . ( $twoLines ? 12 : 3 ) . '">'
											.			$translatedTitle
											.		'</label>';

					$colClass				=	'col-sm-' . ( $twoLines ? 12 : 9 );
				} else {
					$colClass 				=	( $twoLines ? 'col-sm-12' : 'col-sm-9 col-sm-offset-3' );
				}

				$results					.=		'<div class="cb_field ' . $colClass . '">'
											.			'<div id="cbfv_' . $field->fieldid . '">'
											.				$oValue
											.			'</div>'
											.		'</div>'
											.	'</div>';
				break;

			case 'span':
				$rowClasses[]				=	'cb_field';
				$rowClasses[]				=	'cbft_' . $field->type;

				if ( $tag ) {
					$rowClasses[]			=	'cbtt_' . $tag;
				}

				$results					.=		'<span id="cbfr_' . $field->fieldid . '" class="' . implode( ' ', $rowClasses ) . '">'
											.			'<span id="cbfv_' . $field->fieldid . '">'
											.				$oValue
											.			'</span>'
											.		'</span>';
				break;

			case 'ul':
			case 'ol':
				break;

			case 'li':
				$rowClasses[]				=	'cb_field';
				$rowClasses[]				=	'cbft_' . $field->type;

				if ( $tag ) {
					$rowClasses[]			=	'cbtt_' . $tag;
				}

				$results					.=		'<li id="cbfr_' . $field->fieldid . '" class="' . implode( ' ', $rowClasses ) . '">';

				if ( trim( $translatedTitle ) != '' ) {
					$results				.=			'<span class="cb_title">'
											.				$translatedTitle
											.			'</span>';
				}

				$results					.=			'<span id="cbfv_' . $field->fieldid . '">'
											.				$oValue
											.			'</span>'
											.		'</li>';
				break;

			case 'none':
				$results					=	$oValue;
				break;

			default:
				$results					=	'*' . $oValue . '*';
				break;
		}

		return $results;
	}

	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output               'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason               'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types )
	{
		$valuesArray							=	array();

		foreach ( $field->getTableColumns() as $col ) {
			$valuesArray[]						=	$user->get( $col );
		}

		$value									=	implode( ', ', $valuesArray );

		switch ( $output ) {
			case 'html':
			case 'rss':
				return $this->formatFieldValueLayout( $this->_formatFieldOutput( $field->name, $value, $output, true ), $reason, $field, $user );

			case 'htmledit':
				if ( $reason == 'search' ) {
					return	$this->_fieldSearchModeHtml( $field, $user, $this->_fieldEditToHtml( $field, $user, $reason, 'input', $field->type, $value, '' ), 'text', $list_compare_types );
				} else {
					return $this->_fieldEditToHtml( $field, $user, $reason, 'input', $field->type, $value, $this->getDataAttributes( $field, $user, $output, $reason ) );
				}

			default:
				return $this->_formatFieldOutput( $field->name, $value, $output, false );
		}
	}

	/**
	 * Labeller for title:
	 * Returns a field title
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'text' or: 'html', 'htmledit', (later 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist')
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string
	 */
	public function getFieldTitle( &$field, &$user, $output, /** @noinspection PhpUnusedParameterInspection */ $reason )
	{
		if ( $output === 'text' ) {
			return strip_tags( cbReplaceVars( $field->title, $user ) );
		}

		return cbReplaceVars( $field->title, $user );
	}

	/**
	 * Labeller for placeholder:
	 * Returns a field placeholder
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'text' or: 'html', 'htmledit', (later 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist')
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string
	 */
	public function getFieldPlaceholder( &$field, &$user, $output, /** @noinspection PhpUnusedParameterInspection */ $reason )
	{
		$placeholder	=	$field->params->get( 'fieldPlaceholder', null );

		if ( ! $placeholder ) {
			return null;
		}

		if ( $output === 'text' ) {
			return strip_tags( cbReplaceVars( $placeholder, $user ) );
		}

		return cbReplaceVars( $placeholder, $user );
	}

	/**
	 * Labeller for description:
	 * Returns a field title
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output  'text' or: 'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string
	 */
	public function getFieldDescription( &$field, &$user, $output, /** @noinspection PhpUnusedParameterInspection */ $reason )
	{
		if ( $output === 'text' ) {
			return trim( strip_tags( cbReplaceVars( $field->description, $user ) ) );
		}

		if ( $output === 'htmledit' ) {
			return trim( cbReplaceVars( $field->description, $user ) );
		}

		return null;
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
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason )
	{
		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		foreach ( $field->getTableColumns() as $col ) {
			$value						=	cbGetParam( $postdata, $col );

			if ( ( $value !== null ) && ! is_array( $value ) ) {
				$value					=	stripslashes( $value );

				if ( $this->validate( $field, $user, $col, $value, $postdata, $reason ) ) {

					if ( isset( $user->$col ) && ( (string) $user->$col ) !== (string) $value ) {
						$this->_logFieldUpdate( $field, $user, $reason, $user->$col, $value );
					}
				}
				$user->$col				=	$value;
			}
		}
	}

	/**
	 * Non-Mutator:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user) when a field does not save e.g. to read-only setting in front-end
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function prepareFieldDataNotSaved( &$field, &$user, &$postdata, $reason )
	{
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
	public function commitFieldDataSave( &$field, &$user, &$postdata, $reason )
	{
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
	public function rollbackFieldDataSave( &$field, &$user, &$postdata, $reason )
	{
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
		global $_CB_framework, $ueConfig;

		if ( ( $_CB_framework->getUi() == 1 ) || ( ( $_CB_framework->getUi() == 2 ) && ( $ueConfig['adminrequiredfields'] == 1 ) ) ) {

			// Required field:
			if ( ( $field->required == 1 ) && ( $value == '' ) ) {
				$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_REQUIRED_ERROR', 'This field is required!' ) );

				return false;
			}

			$len						=	cbIsoUtf_strlen( $value );

			// Minimum field length:
			$fieldMinLength				=	$this->getMinLength( $field );

			if ( ( $len > 0 ) && ( $len < $fieldMinLength ) ) {
				$this->_setValidationError(
					$field, $user, $reason,
					CBTxt::T( 'UE_VALIDATE_ERROR_MIN_CHARS_PLEASE', 'Please enter a valid [FIELDNAME]: at least ||%%NUMBERCHARSREQUIRED%% character|%%NUMBERCHARSREQUIRED%% characters||: you entered ||%%NUMBERCHARSENTERED%% character.|%%NUMBERCHARSENTERED%% characters.',
						array( '[FIELDNAME]'			=> $this->getFieldTitle( $field, $user, 'text', $reason ),
							   '%%NUMBERCHARSREQUIRED%%'	=> $fieldMinLength,
							   '%%NUMBERCHARSENTERED%%'	=> $len ) )
				);

				return false;
			}

			// Maximum field length:
			$fieldMaxLength				=	$this->getMaxLength( $field );
			if ( $fieldMaxLength && ( $len > $fieldMaxLength ) ) {
				$this->_setValidationError( $field, $user, $reason,
					CBTxt::T( 'UE_VALIDATE_ERROR_MAX_CHARS_PLEASE', 'Please enter a valid [FIELDNAME]: maximum ||%%NUMBERCHARSREQUIRED%% character|%%NUMBERCHARSREQUIRED%% characters||: you entered ||%%NUMBERCHARSENTERED%% character.|%%NUMBERCHARSENTERED%% characters.',
						array( '[FIELDNAME]'			=> $this->getFieldTitle( $field, $user, 'text', $reason ),
							   '%%NUMBERCHARSREQUIRED%%'	=> $fieldMaxLength,
							   '%%NUMBERCHARSENTERED%%'	=> $len ) )
				);

				return false;
			}

			// Bad words:
			if ( ( $reason == 'register' ) && ( in_array( $field->type, array( 'emailaddress', 'primaryemailaddress', 'textarea', 'text', 'webaddress', 'predefined' ) ) ) ) {
				$defaultForbidden		=	'http:,https:,mailto:,//.[url],<a,</a>,&#';
			} else {
				$defaultForbidden		=	'';
			}
			$forbiddenContent			=	$field->params->get( 'fieldValidateForbiddenList_' . $reason, $defaultForbidden );
			if ( $forbiddenContent != '' ) {
				$forbiddenContent		=	explode( ',', $forbiddenContent );
				if ( in_array( '', $forbiddenContent, true ) ) {
					// treats case of ',,' or ',,,' to also forbid ',' if in string.
					$forbiddenContent[] =	',';
				}
				for ( $i = 0, $n = count( $forbiddenContent ); $i < $n; $i++ ) {
					$forbiddenContent[$i]	=	preg_quote( $forbiddenContent[$i], '/' );
				}
				$replaced				=	preg_replace( '/' . implode( '|', $forbiddenContent ) . '/i', '', $value );
				if ( $replaced != $value ) {
					$this->_setValidationError( $field, $user, $reason, CBTxt::T( 'UE_INPUT_VALUE_NOT_ALLOWED', 'This input value is not authorized.' ) );

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Finder:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals          RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata            Typically $_POST (but not necessarily), filtering required.
	 * @param  int         $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $reason              'edit' for save user edit, 'register' for save registration
	 * @return cbSqlQueryPart[]
	 */
	public function bindSearchCriteria( &$field, &$searchVals, &$postdata, $list_compare_types, /** @noinspection PhpUnusedParameterInspection */ $reason )
	{
		$query							=	array();

		$searchMode						=	$this->_bindSearchMode( $field, $searchVals, $postdata, 'text', $list_compare_types );

		if ( $searchMode ) {
			foreach ( $field->getTableColumns() as $col ) {
				$value					=	cbGetParam( $postdata, $col );
				if ( ( ( ( $value !== null ) && ( $value !== '' ) ) || ( ( $list_compare_types == 1 ) && in_array( $searchMode, array( 'is', 'isnot' ) ) ) ) && ! is_array( $value ) ) {
					$value				=	stripslashes( $value );
					$searchVals->$col	=	$value;
					// $this->validate( $field, $user, $col, $value, $postdata, $reason );
					$sql				=	new cbSqlQueryPart();
					$sql->tag			=	'column';
					$sql->name			=	$col;
					$sql->table			=	$field->table;
					$sql->type			=	'sql:field';
					$sql->operator		=	'=';
					$sql->value			=	$value;
					$sql->valuetype		=	'const:string';
					$sql->searchmode	=	$searchMode;
					$query[]			=	$sql;
				}
			}
		}

		return $query;
	}

	/**
	 * Returns a field in specified format
	 *
	 * @param  string   $name              Sanitized/Safe !!!
	 * @param  string   $value
	 * @param  string   $output            NO 'htmledit' BUT: 'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist'
	 * @param  boolean  $htmlspecialchars  TRUE: escape for display, FALSE: not escaped will display raw.
	 * @return mixed
	 */
	protected function _formatFieldOutput( $name, $value, $output, $htmlspecialchars = true )
	{

		switch ( $output ) {
			case 'html':
			case 'rss':
			case 'htmledit':
				if ( $htmlspecialchars ) {
					return htmlspecialchars( $value );
				}

				return $value;

			case 'xml':
				if ( $htmlspecialchars ) {
					return '<' . htmlspecialchars( $name ) . '>' . htmlspecialchars( htmlspecialchars( $value ) ) . '</' .htmlspecialchars( $name ) . '>';
				}

				return '<' . htmlspecialchars( $name ) . '>' . htmlspecialchars( $value ) . '</' . htmlspecialchars( $name ) . '>';

			case 'json':
				return "'" . addslashes( $name ) . "' : '" . addslashes( $value ) . "'";

			case 'php':
				return array( $name => $value );

			case 'fieldslist':
				return $name;

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'csvheader':
				$value		=	$name;
			// on purpose fall-through:
			case 'csv':
				if ( ! preg_match( '/",\n\r\t/', $value ) ) {
					return $value;
				}

				return  '"' . str_replace( '"', '""', $value ) . '"';

			default:
				trigger_error( '_formatFieldOutput called with ' . htmlspecialchars( $output ), E_USER_WARNING );
				return $value;
		}
	}

	/**
	 * Returns a field in specified format
	 *
	 * @param  string  $name    Sanitized/Safe !!!
	 * @param  string  $value   Value to format
	 * @param  string  $output  NO 'htmledit' BUT: 'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist'
	 * @return mixed
	 */
	protected function _formatFieldOutputIntBoolFloat( $name, $value, $output )
	{

		switch ( $output ) {
			case 'html':
			case 'rss':
				return $value;

			case 'htmledit':
				trigger_error( '_formatFieldOutput called with htmledit', E_USER_WARNING );
				return null;

			case 'xml':
				return '<' . $name . '>' . $value . '</' . $name . '>';

			case 'json':
				return "'" . $name . "' : " . $value;

			case 'php':
				return array( $name => $value );

			case 'csvheader':
			case 'fieldslist':
				return $name;

			case 'csv':
				return $value;

			default:
				trigger_error( '_formatFieldOutput called with ' . htmlspecialchars( $output ), E_USER_WARNING );
				return $value;
		}
	}

	/**
	 * Reformats a PHP array into $output format
	 *
	 * @param  FieldTable         $field
	 * @param  array              $values
	 * @param  string             $output    'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string             $listType
	 * @param  string             $class
	 * @param  boolean            $htmlspecialchars  TRUE: escape for display, FALSE: not escaped will display raw.
	 * @return string|array|null
	 */
	protected function _arrayToFormat( &$field, $values, $output, $listType = ', ', $class = '', $htmlspecialchars = true )
	{
		switch ( $output ) {
			case 'html':
			case 'rss':
				if ( $htmlspecialchars ) {
					foreach ( $values as $k => $v ) {
						$values[$k]		=	htmlspecialchars( $v );
					}
				}

				switch ( $listType ) {
					case 'ul':
					case 'ol':
						if ( count( $values ) > 0 ) {
							if ( $class != '' ) {
								$class	=	' class="' . htmlspecialchars( $class ) . '"';
							}
							return '<' . $listType . $class . '><li>' . implode( '</li><li>', $values ) . '</li></' . $listType . '>';
						}

						return null;

					case ', ':
					default:
						return implode( $listType, $values );
				}
				break;

			case 'htmledit':
				break;

			case 'xml':
				foreach ( $values as $k => $v ) {
					$values[$k]	=	'<value>' . htmlspecialchars( $v ) . '</value>';
				}

				return '<' . htmlspecialchars( $field->name ) . '>' . implode( '', $values ) . '</' . htmlspecialchars( $field->name ) . '>';

			case 'json':
				foreach ( $values as $k => $v ) {
					$values[$k]	=	"'" . addslashes( $v ) . "'";
				}

				return "'" . addslashes( $field->name ) . "' : { " .  implode( ', ', $values ) . " }";

			case 'php':
				return array( $field->name => $values );

			case 'csv':
				$valsString		=	implode( ',', $values );

				return $this->_formatFieldOutput( $field->name, $valsString, $output, false );

			case 'csvheader':
			case 'fieldslist':
			default:
				break;
		}
		trigger_error( '_arrayToFormat called with non-implemented output type: ' . htmlspecialchars( $output ), E_USER_WARNING );
		return null;
	}

	/**
	 * Reformats a PHP array of links into $output format
	 *
	 * @param  FieldTable         $field
	 * @param  array              $links
	 * @param  string             $output    'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @return string|array|null
	 */
	protected function _linksArrayToFormat( $field, $links, $output )
	{
		$values						=	array();

		switch ( $output ) {
			case 'xml':
				foreach ( $links as $link ) {
					if ( isset( $link['url' ] ) ) {
						$values[]	=	'<link>'
									.		'<url>' . cbSef( $link['url'] ) . '</url>'
									.		'<title>' . htmlspecialchars( $link['title'] ) . '</title>'
									.		'<tooltip>' . htmlspecialchars( CBTxt::T( $link['tooltip'] ) ) . '</tooltip>'
									.	'</link>';
					}
				}

				return '<' . htmlspecialchars( $field->name ) . '>' . implode( '', $values ) . '</' . htmlspecialchars( $field->name ) . '>';
				break;
			case 'json':
				foreach ( $links as $link ) {
					if ( isset( $link['url' ] ) ) {
						$values[]	=	array(	'url' => cbSef( $link['url'] ),
												'link' => $link['title'],
												'tooltip' => CBTxt::T( $link['tooltip'] )
											);
					}
				}

				return "'" . addslashes( $field->name ) . "' : " .  json_encode( $values, JSON_FORCE_OBJECT );
				break;
			case 'csv':
				foreach ( $links as $link ) {
					if ( isset( $link['url' ] ) ) {
						$values[]	=	cbSef( $link['url'] );
					}
				}

				return $this->_formatFieldOutput( $field->name, implode( ',', $values ), $output, false );
				break;
			default:
				foreach ( $links as $link ) {
					if ( isset( $link['url' ] ) ) {
						$values[]	=	'<a href="' . cbSef( $link['url'] ) . '" title="' . htmlspecialchars( CBTxt::T( $link['tooltip'] ) ) . '">' . $link['title'] . '</a>';
					}
				}

				return $this->_arrayToFormat( $field, $values, $output, ' ', '', false );
				break;
		}
	}

	/**
	 * @param  string            $value
	 * @param  string            $reason
	 * @param  null|FieldTable   $field
	 * @param  null|UserTable    $user
	 * @param  boolean           $htmlspecialchars
	 * @param  array             $extra
	 * @return string
	 */
	protected function formatFieldValueLayout( $value, $reason = 'profile', $field = null, $user = null, $htmlspecialchars = true, $extra = array() )
	{
		if ( in_array( $reason, array( 'profile', 'list', 'edit', 'register' ) ) && ( $value !== null ) && ( $value !== '' ) && ( $field !== null ) && ( ! $field->get( '_hideLayout', 0 ) ) ) {
			switch( $reason ) {
				case 'register':
					$layout	=	CBTxt::T( $field->params->get( 'fieldLayoutRegister', null ) );
					break;

				case 'edit':
					$layout	=	CBTxt::T( $field->params->get( 'fieldLayoutEdit', null ) );
					break;

				case 'list':
					$layout	=	CBTxt::T( $field->params->get( 'fieldLayoutList', null ) );
					break;

				case 'profile':
				default:
					$layout	=	CBTxt::T( $field->params->get( 'fieldLayout', null ) );
					break;
			}

			// Remove userdata and userfield usage of self from layout to avoid infinite loop:
			$layout			=	trim( preg_replace( '/\[cb:(userdata +field|userfield +field)="' . preg_quote( $field->get( 'name' ) ) . '"[^]]+\]/i', '', $layout ) );

			if ( $layout ) {
				$value		=	str_replace( '[value]', $value, $layout );

				if ( $field->params->get( 'fieldLayoutContentPlugins', 0 ) ) {
					$value	=	Application::Cms()->prepareHtmlContentPlugins( $value );
				}

				if ( $user !== null ) {
					$value	=	cbReplaceVars( $value, $user, $htmlspecialchars, true, $extra );
				}
			}
		}

		return $value;
	}

	/**
	 * Private methods for front-end:
	 */

	/**
	 * converts to HTML
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason             'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  string      $tag                <tag
	 * @param  string      $type               type="$type"
	 * @param  string      $value              value="$value"
	 * @param  string      $additional         'xxxx="xxx" yy="y"'  WARNING: No classes in here, use $classes
	 * @param  string      $allValues
	 * @param  boolean     $displayFieldIcons
	 * @param  array       $classes            CSS classes
	 * @return string                          HTML: <tag type="$type" value="$value" xxxx="xxx" yy="y" />
	 */
	protected function _fieldEditToHtml( &$field, &$user, $reason, $tag, $type, $value, $additional, $allValues = null, $displayFieldIcons = true, $classes = null )
	{
		global $_CB_framework, $_PLUGINS;

		$readOnly				=	$this->_isReadOnly( $field, $user, $reason );
		$oReq					=	$this->_isRequired( $field, $user, $reason );

		if ( $readOnly ) {
			$additional			.=	' disabled="disabled"';
			$oReq				=	0;
		}

		if ( $oReq ) {
			$classes[]			=	'required';
		}

		$inputName				=	$field->name;

		$translatedTitle		=	$this->getFieldTitle( $field, $user, 'html', $reason );
		$translatedPlaceholder	=	$this->getFieldPlaceholder( $field, $user, 'text', $reason );
		$htmlDescription		=	$this->getFieldDescription( $field, $user, 'htmledit', $reason );
		$trimmedDescription		=	trim( strip_tags( $htmlDescription ) );

		$htmlInput				=	null;
		switch ( $type ) {
			case 'radio':
				if ( $classes ) {
					$additional	.=	' class="' . implode( ' ', $classes ) . '"';
				}
				$tooltip		=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, 'data-hascbtooltip="true"' ) : null );
				$htmlInput		=	moscomprofilerHTML::radioListTable( $allValues, $inputName, $additional, 'value', 'text', $value, $field->cols, $field->rows, $field->size, $oReq, null, $tooltip );
				break;

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'multiselect':
				$additional		.=	' multiple="multiple"';
				$inputName		.=	'[]';
			// no break on purpose for fall-through:
			case 'select':
				$classes[]		=	'form-control';
				$additional		.=	' class="' . implode( ' ', $classes ) . '"';
				if ( $field->size > 0 ) {
					$additional	.=	' size="' . $field->size . '"';
				}
				$tooltip		=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, $additional ) : $additional );
				$htmlInput		=	moscomprofilerHTML::selectList( $allValues, $inputName, $tooltip, 'value', 'text', $this->_explodeCBvaluesToObj( $value ), $oReq );
				break;

			case 'multicheckbox':
				if ( $classes ) {
					$additional	.=	' class="' . implode( ' ', $classes ) . '"';
				}
				$tooltip		=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, 'data-hascbtooltip="true"' ) : null );
				$htmlInput		=	moscomprofilerHTML::checkboxListTable( $allValues, $inputName . '[]', $additional, 'value', 'text', $this->_explodeCBvaluesToObj( $value ), $field->cols, $field->rows, $field->size, $oReq, null, $tooltip );
				break;

			case 'checkbox':
				if ( $classes ) {
					$additional	.=	' class="' . implode( ' ', $classes ) . '"';
				}
				$tooltip		=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, $additional ) : $additional );
				$htmlInput		=	'<span class="cbSingleCntrl">'
								.		'<label for="' . htmlspecialchars( $inputName ) . '" class="checkbox-inline">'
								.			'<input type="checkbox" id="' . htmlspecialchars( $inputName ) . '" name="' . htmlspecialchars( $inputName ) . '" value="' . htmlspecialchars( $value ) . '"' . $tooltip . ' />'
								.		'</label>'
								.	'</span>';
				break;

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'password':
				$additional		.=	' autocomplete="off"';
			// on purpose no break here !
			case 'text':
			case 'primaryemailaddress':
			case 'emailaddress':
			case 'webaddress':
			case 'predefined':
				if ( $type != 'password' ) {
					$type		=	'text'; // Prevent predefined, emailaddress, etc.. from using invalid html type
				}
				if ( $field->size > 0 ) {
					$additional	.=	' size="' . $field->size . '"';
				} else {
					$additional	.=	' size="25"';
				}
				$fieldMaxLength	=	$this->getMaxLength( $field );
				if ( $fieldMaxLength > 0 ) {
					$additional	.=	' maxlength="' . $fieldMaxLength . '"';
				}
				if ( $translatedPlaceholder ) {
					$additional	.=	' placeholder="' . htmlspecialchars( $translatedPlaceholder ) . '"';
				}
				$classes[]		=	'form-control';
				break;

			case 'textarea':
				$tag			=	'textarea';
				$type			=	null;
				if ( $field->cols > 0 ) {
					$additional	.=	' cols="' . $field->cols . '"';
				}
				if ( $field->rows > 0 ) {
					$additional	.=	' rows="' . $field->rows . '"';
				}
				$fieldMaxLength	=	$this->getMaxLength( $field );
				if ( $fieldMaxLength > 0 ) {
					$additional	.=	' maxlength="' . $fieldMaxLength . '"';
				}
				if ( $translatedPlaceholder ) {
					$additional	.=	' placeholder="' . htmlspecialchars( $translatedPlaceholder ) . '"';
				}
				$classes[]		=	'form-control';
				break;

			case 'file':
				$classes[]		=	'form-control';
				if ( $field->size > 0 ) {
					$additional	.=	' size="' . $field->size . '"';
				}
				break;

			case 'html':
				return $value;
				break;

			default:
				break;
		}
		if ( $classes ) {
			$additional	.=	' class="' . implode( ' ', $classes ) . '"';
		}

		// if ( $_PLUGINS->triggerListenersExist( 'onInputFieldHtmlRender' ) ) {
		//	return implode( '', $_PLUGINS->trigger( 'onInputFieldHtmlRender', array( &$this, &$field, &$user, $reason, $tag, $type, $inputName, $value, $additional, $htmlDescription, $allValues, $displayFieldIcons, $oReq ) ) );
		// }
		if ( $htmlInput === null ) {
			$tooltip	=	( $trimmedDescription ? cbTooltip( $_CB_framework->getUi(), $htmlDescription, $translatedTitle, null, null, null, null, $additional ) : $additional );
			$htmlInput	=	'<' . $tag
						.	( $type ? ' type="' . $type . '"' : '' )
						.	' name="' . $inputName . '" id="' . $inputName . '"'
						.	( $tag == 'textarea' ? '' : ' value="' . htmlspecialchars( $value ) . '"' )
						.	$tooltip
						.	( $tag == 'textarea' ? '>' .  htmlspecialchars( $value ) . '</textarea>' : ' />' );
		}
		$htmlIcons		=	$this->_fieldIconsHtml( $field, $user, 'htmledit', $reason, $tag, $type, $value, $additional, $allValues, $displayFieldIcons, $oReq );

		if ( $reason != 'search' ) {
			$htmlInput	=	$this->formatFieldValueLayout( $htmlInput, $reason, $field, $user );
		}

		if ( $_PLUGINS->triggerListenersExist( 'onInputFieldHtmlRender' ) ) {
			return implode( '', $_PLUGINS->trigger( 'onInputFieldHtmlRender', array( $htmlInput, $htmlIcons, $this, $field, $user, $reason, $tag, $type, $inputName, $value, $additional, $htmlDescription, $allValues, $displayFieldIcons, $oReq ) ) );
		}

		return $htmlInput . $htmlIcons;
	}

	/**
	 * Displays field icons
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output
	 * @param  string      $reason            'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  string      $tag               <tag
	 * @param  string      $type              type="$type"
	 * @param  string      $value             value="$value"
	 * @param  string      $additional        'xxxx="xxx" yy="y"'
	 * @param  string      $allValues
	 * @param  boolean     $displayFieldIcons
	 * @param  boolean     $required
	 * @return string                          HTML
	 */
	protected function _fieldIconsHtml( &$field, &$user, $output, $reason, $tag, $type, $value, $additional, $allValues, $displayFieldIcons, $required )
	{
		global $_CB_framework, $_PLUGINS;
		global $_CB_fieldIconDisplayed;		// this is for backwards compatibility with CB 1.2.1 API only, with isset below. New method is to act on $displayFieldIcons referenced parameter in the event.

		$return					=	null;
		$results				=	$_PLUGINS->trigger( 'onFieldIcons', array( &$this, &$field, &$user, $output, $reason, $tag, $type, $value, $additional, $allValues, &$displayFieldIcons, $required ) );
		if ( count( $results ) > 0 ) {
			$return				.=	implode( '', $results );
		}
		if ( $displayFieldIcons && ( $reason != 'search' ) && ! isset( $_CB_fieldIconDisplayed[$field->fieldid] ) ) {
			return getFieldIcons( $_CB_framework->getUi(), $required, $field->profile, $this->getFieldDescription( $field, $user, $output, $reason ), $this->getFieldTitle( $field, $user, $output, $reason ), false, $field->params->get( 'fieldLayoutIcons', null ) )
			. $return;
		}
		return $return;
	}

	/**
	 * Checks if the field is required or not
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason
	 * @return int
	 */
	protected function _isRequired( $field, /** @noinspection PhpUnusedParameterInspection */ $user, $reason )
	{
		global $_CB_framework, $ueConfig;

		if ( $reason == 'search' ) {
			$adminReq			=	0;
		} else {
			$adminReq				=	$field->required;

			if (	( $_CB_framework->getUi() == 2 )
				&&	( $ueConfig['adminrequiredfields']==0 )
				&&	! in_array( $field->name, array( 'username', 'email', 'name', 'firstname', 'lastname' ) ) )
			{
				$adminReq			=	0;
			}
		}

		return $adminReq;
	}

	/**
	 * Checks if the field is read-only or not
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason
	 * @return int
	 */
	protected function _isReadOnly( $field, /** @noinspection PhpUnusedParameterInspection */ $user, $reason )
	{
		global $_CB_framework;

		$readOnly				=	$field->readonly;
		if ( ( $_CB_framework->getUi() == 2 ) || ( in_array( $reason, array( 'register', 'search' ) ) ) ) {
			$readOnly			=	0;
		}

		return $readOnly;
	}

	/**
	 * Explodes a CB multi-value into an array of objects with ->value and ->text attributes
	 *
	 * @param  string  $value  Values with |*| separations
	 * @return stdClass[]      Values as array
	 */
	private function _explodeCBvaluesToObj( $value )
	{
		if ( ! is_array( $value ) ) {
			if ( ( $value === '' ) || is_null( $value ) ) {
				$value			=	array();
			} else {
				$value			=	explode( '|*|', $value );
			}
		}

		$objArr					=	array();
		foreach( $value as $k => $kv ) {
			$objArr[$k]			=	new stdClass();
			$objArr[$k]->value	=	$kv;
			$objArr[$k]->text	=	$kv;
		}

		return $objArr;
	}

	/**
	 * Explodes a CB multi-value into an array
	 *
	 * @param  string  $value  Values with |*| separations
	 * @return array           Values as array
	 */
	protected function _explodeCBvalues( $value )
	{
		return explode( '|*|', $value );
	}

	/**
	 * Implodes an array into a CB multi-value string
	 *
	 * @param  array   $value  Values as array
	 * @return string          Values with |*| separations
	 */
	protected function _implodeCBvalues( $value )
	{
		return implode( '|*|', $value );
	}

	/**
	 * Outputs a Fields-search field for a range
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output
	 * @param  string      $reason
	 * @param  string      $value
	 * @param  string      $minHtml
	 * @param  string      $maxHtml
	 * @param  int         $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return string
	 */
	protected function _fieldSearchRangeModeHtml( &$field, &$user, $output, $reason, $value, $minHtml, $maxHtml, $list_compare_types )
	{
		$fromHTML	=	'</span> <span class="cbSearchFromVal">'
			.	$minHtml
			.	'</span>'
			.	'</div>'
			.	'<div class="cbSearchFromToLine">'
			.	'<span class="cbSearchFromTo cbSearchTo">';

		$toHTML		=	'</span> <span class="cbSearchToVal">'
			.	$maxHtml
			.	'</span>';

		$html	=	'<div class="cbSearchFromToLine">'
			.	'<span class="cbSearchFromTo cbSearchFrom">'
			.	CBTxt::Th( 'UE_SEARCH_RANGE_BETWEEN_MIN_AND_MAX', 'Between [MINIMUMVALUEINPUTFIELD] and [MAXIMUMVALUEINPUTFIELD]', array( '[MINIMUMVALUEINPUTFIELD]' => $fromHTML, '[MAXIMUMVALUEINPUTFIELD]' => $toHTML ) )
			.	$this->_fieldIconsHtml( $field, $user, $output, $reason, null, $field->type, $value, 'input', null, true, false )
			.	'</div>'
		;

		return $this->_fieldSearchModeHtml( $field, $user, $html, 'isisnot', $list_compare_types );
	}

	/**
	 * Notifies plugins to log an update to the field (but to wait for the profile saving events to store that log)
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason     'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  mixed       $oldValues
	 * @param  mixed       $newValues
	 */
	protected function _logFieldUpdate( &$field, &$user, $reason, $oldValues, $newValues )
	{
		global $_PLUGINS;
		$_PLUGINS->trigger( 'onLogChange', array( 'update', 'user', 'field', &$user, &$this->_plugin, &$field, $oldValues, $newValues, $reason ) );
	}

	/**
	 * Outputs search format including $html being html with input fields
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $html
	 * @param  string      $type                'text', 'choice', 'isisnot', 'none'
	 * @param  int         $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string      $class               Extra-class (e.g. for jQuery)
	 * @return string
	 */
	protected function _fieldSearchModeHtml( &$field, &$user, $html, $type, $list_compare_types, $class = '' )
	{
		switch ($list_compare_types ) {
			case 1:
				// Advanced: all possibilities:
				$col						=	$field->name . '__srmch';
				$selected					=	$user->get( $col );
				switch ( $type ) {
					case 'text':
						$choices		=	array(	'is'			=>	CBTxt::T( 'UE_MATCH_IS_EXACTLY', 'is exactly' ),
													   'phrase'		=>	CBTxt::T( 'UE_MATCH_PHRASE', 'contains phrase' ),
													   'all'			=>	CBTxt::T( 'UE_MATCH_ALL', 'contains all of' ),
													   'any'			=>	CBTxt::T( 'UE_MATCH_ANY', 'contains any of' ),
													   '-'				=>	CBTxt::T( 'UE_MATCH_EXCLUSIONS', 'Exclusions:'),
													   'isnot'			=>	CBTxt::T( 'UE_MATCH_IS_EXACTLY_NOT', 'is exactly not'),
													   'phrasenot'		=>	CBTxt::T( 'UE_MATCH_PHRASE_NOT', 'doesn\'t contain phrase' ),
													   'allnot'		=>	CBTxt::T( 'UE_MATCH_ALL_NOT', 'doesn\'t contain all of' ),
													   'anynot'		=>	CBTxt::T( 'UE_MATCH_ANY_NOT', 'doesn\'t contain any of' )
						);
						break;

					case 'singlechoice':
						$choices		=	array(	'is'			=>	CBTxt::T( 'UE_MATCH_IS', 'is' ),
							// 'is'			=>	CBTxt::T( 'UE_MATCH_IS_EXACTLY', 'is exactly' ),
							// 'phrase'		=>	CBTxt::T( 'UE_MATCH_PHRASE', 'contains phrase' ),
							// 'all'		=>	CBTxt::T( 'UE_MATCH_ALL', 'contains all of' ),
													   'anyis'			=>	CBTxt::T( 'UE_MATCH_IS_ONE_OF', 'is one of' ),
													   '-'				=>	CBTxt::T( 'UE_MATCH_EXCLUSIONS', 'Exclusions:'),
													   'isnot'			=>	CBTxt::T( 'UE_MATCH_IS_NOT', 'is not' ),
							// 'phrasenot'	=>	CBTxt::T( 'UE_MATCH_PHRASE_NOT', 'doesn\'t contain phrase' ),
							// 'allnot'		=>	CBTxt::T( 'UE_MATCH_ALL_NOT', 'doesn\'t contain all of' ),,
													   'anyisnot'		=>	CBTxt::T( 'UE_MATCH_IS_NOT_ONE_OF', 'is not one of' )
						);
						break;

					case 'multiplechoice':
						$choices		=	array(	'is'			=>	CBTxt::T( 'UE_MATCH_ARE_EXACTLY', 'are exactly' ),
							// 'phrase'		=>	CBTxt::T( 'UE_MATCH_PHRASE', 'contains phrase' ),
													   'all'			=>	CBTxt::T( 'UE_MATCH_INCLUDE_ALL_OF', 'include all of' ),
													   'any'			=>	CBTxt::T( 'UE_MATCH_INCLUDE_ANY_OF', 'include any of' ),
													   '-'				=>	CBTxt::T( 'Exclusions:'),
													   'isnot'			=>	CBTxt::T( 'UE_MATCH_ARE_EXACTLY_NOT', 'are exactly not' ),
							// 'phrasenot'	=>	CBTxt::T( 'UE_MATCH_PHRASE_NOT', 'doesn\'t contain phrase' ),
													   'allnot'		=>	CBTxt::T( 'UE_MATCH_INCLUDE_ALL_OF_NOT', 'don\'t include all of' ),
													   'anynot'		=>	CBTxt::T( 'UE_MATCH_INCLUDE_ANY_OF_NOT', 'don\'t include any of' )
						);
						break;

					case 'isisnot':
						$choices		=	array(	'is'			=>	CBTxt::T( 'UE_MATCH_IS', 'is' ),
													   '-'				=>	CBTxt::T( 'UE_MATCH_EXCLUSIONS_COLUMN', 'Exclusions:'),
													   'isnot'			=>	CBTxt::T( 'UE_MATCH_IS_NOT', 'is not' )
						);
						break;


					case 'none':
					default:
						$choices		=	null;
						break;
				}

				if ( $choices !== null ) {
					$drop				=	array();
					$drop[]				=	moscomprofilerHTML::makeOption( '', CBTxt::T( 'UE_NO_PREFERENCE', 'No preference' ) );
					$group				=	false;

					foreach ( $choices as $k => $v ) {
						if ( $k == '-' ) {
							$drop[]		=	moscomprofilerHTML::makeOptGroup( $v );
							$group		=	true;
						} else {
							$drop[]		=	moscomprofilerHTML::makeOption( $k, $v );
						}
					}
					if ( $group ) {
						$drop[]			=	moscomprofilerHTML::makeOptGroup( null );
					}
					$additional			=	' class="form-control"';
					$list				=	moscomprofilerHTML::selectList( $drop, $field->name . '__srmch', $additional, 'value', 'text', $selected, 1 );
				} else {
					$list				=	null;
				}

				$return					=	'<div class="cbSearchContainer cbSearchAdvanced' . ( $type ? ' cbSearchAdvanced' . ucfirst( $type ) : '' ) . '">'
										.		( $list ?	'<div class="cbSearchKind' . ( $type ? ' cbSearchKind' . ucfirst( $type ) : '' ) . '">' . $list . '</div>'	:	'' )
										.		'<div class="cbSearchCriteria' . ( $type ? ' cbSearchCriteria' . ucfirst( $type ) : '' ) . ( $class ? ' ' . $class : '' ) . '">' . $html . '</div>'
										.	'</div>';
				break;

			case 2:		// Simple "contains" and ranges:
			case 0:
			default:
				// Simple: Only 'is' and ranges:
				$return					=	'<div class="cbSearchContainer cbSearchSimple">'
					.	'<div class="cbSearchCriteria' . ( $class ? ' ' . $class : '' ) . '">' . $html . '</div>'
					. '</div>'
				;
				break;
		}

		return $return;
	}

	/**
	 * Binds search mode
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals
	 * @param  array       $postdata
	 * @param  string      $type                'text', 'choice', 'isisnot', 'none'
	 * @param  int         $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return array|string|null
	 */
	protected function _bindSearchMode( $field, &$searchVals, $postdata, $type, $list_compare_types )
	{
		switch ($list_compare_types) {
			case 1:
				$fieldNam					=	$field->name . '__srmch';
				$value						=	cbGetParam( $postdata, $fieldNam );
				if ( ( $value !== null ) && ( $value !== '' ) ) {
					$searchVals->$fieldNam	=	stripslashes( $value );
				}
				break;

			case 2:
				if ( cbGetParam( $postdata, $field->name ) != null ) {
					switch ( $type ) {
						case 'text':
						case 'multiplechoice':
							$value			=	'any';
							break;
						case 'singlechoice':
						case 'isisnot':
						case 'none':
							$value			=	'is';
							break;

						default:
							$value			=	null;
							break;
					}
				} else {
					$value					=	null;
				}
				break;

			case 0:
			default:
				if ( cbGetParam( $postdata, $field->name ) != null ) {
					$value					=	'is';
				} else {
					$value					=	null;
				}
				break;
		}

		return $value;
	}

	/**
	 * Binds search range mode
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $searchVals
	 * @param  array       $postdata
	 * @param  string      $minName
	 * @param  string      $maxName
	 * @param  int         $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return array|string|null
	 */
	protected function _bindSearchRangeMode( &$field, &$searchVals, &$postdata, $minName, $maxName, $list_compare_types )
	{
		switch ($list_compare_types) {
			case 1:
				$value						=	$this->_bindSearchMode( $field, $searchVals, $postdata, 'isisnot', $list_compare_types );
				break;

			case 2:
			case 0:
			default:
				if ( ( cbGetParam( $postdata, $minName ) != null ) || ( cbGetParam( $postdata, $maxName ) != null ) ) {
					$value					=	'is';
				} else {
					$value					=	null;
				}
				break;
		}

		return $value;
	}

	/**
	 * Prepares field meta-data for saving to database (safe transfer from $postdata to $user)
	 * Override but call parent
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 */
	protected function _prepareFieldMetaSave( &$field, &$user, &$postdata, $reason )
	{
	}

	/**
	 * Adds javascript for a field check by ajax
	 *
	 * @deprecated 2.0.0 use cbValidator::getRuleHtmlAttributes instead
	 *
	 * @param  FieldTable     $field
	 * @param  UserTable      $user
	 * @param  string         $reason
	 * @param  string[]|null  $validateParams
	 * @return null|string                     Returns string of extra classes (without spaces around)
	 */
	protected function ajaxCheckField( &$field, &$user, $reason, $validateParams = null )
	{
		if ( $validateParams !== null ) {
			$validateParams[]	=	cbValidator::getRuleHtmlAttributes( 'cbfield', array( 'user' => (int) $user->id, 'field' => htmlspecialchars( $field->name ), 'reason' => htmlspecialchars( $reason ) ) );

			return $this->getDataAttributes( $field, $user, 'htmledit', $reason, $validateParams );
		}

		return null;
	}

	/**
	 * Returns the minimum field length as set
	 * (public for B/C)
	 *
	 * @param  FieldTable  $field
	 * @return int
	 */
	public function getMinLength( $field )
	{
		return (int) $field->params->get( 'fieldMinLength', 0 );
	}

	/**
	 * Returns the maximum field length as set
	 * (public for B/C)
	 *
	 * @param  FieldTable  $field
	 * @return int
	 */
	public function getMaxLength( $field )
	{
		return (int) $field->maxlength;
	}

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
	protected function getDataAttributes( $field, $user, $output, $reason, $attributeArray = array() )
	{
		if ( ! is_array( $attributeArray ) ) {
			$attributeArray				=	array();
		}

		$fieldMinLength					=	$this->getMinLength( $field );

		if ( $fieldMinLength > 0 ) {
			$attributeArray[]			=	cbValidator::getRuleHtmlAttributes( 'minlength', (int) $fieldMinLength );
		}

		$fieldMaxLength					=	$this->getMaxLength( $field );

		if ( $fieldMaxLength > 0 ) {
			$attributeArray[]			=	cbValidator::getRuleHtmlAttributes( 'maxlength', (int) $fieldMaxLength );
		}

		if ( isset( $field->_identicalTo ) ) {
			$attributeArray[]			=	cbValidator::getRuleHtmlAttributes( 'equalto', '#' . $field->_identicalTo );
		}

		if ( count( $attributeArray ) > 0 ) {
			$attributes					=	' ' . implode( ' ', $attributeArray );
		} else {
			$attributes					=	null;
		}

		return $attributes;
	}

	/**
	 * Direct access to field for custom operations, like for Ajax
	 *
	 * WARNING: direct unchecked access, except if $user is set, then check well for the $reason ...
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable    $user
	 * @param  array                 $postdata
	 * @param  string                $reason     'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string                            Expected output.
	 */
	public function fieldClass( /** @noinspection PhpUnusedParameterInspection */ &$field, &$user, &$postdata, $reason )
	{
		global $_CB_framework;

		// simple spoof check security
		if ( ( ! cbSpoofCheck( 'fieldclass', 'POST', 2 ) ) || ( ( $reason == 'register' ) && ( $_CB_framework->getUi() == 1 ) && ! cbRegAntiSpamCheck( 2 ) ) ) {
			echo '<div class="alert alert-danger">' . CBTxt::Th( 'UE_SESSION_EXPIRED', 'Session expired or cookies are not enabled in your browser. Please press "reload page" in your browser, and enable cookies in your browser.' ) . "</div>";
			exit;
		}

		return false;
	}

	/**
	 * Private methods: BACKEND ONLY:
	 */

	/**
	 * Loads XML file (backend use only!)
	 *
	 * @param  FieldTable  $field
	 * @return boolean             TRUE if success, FALSE if failed
	 */
	private function _loadXML( $field )
	{
		global $_PLUGINS;

		if ( ! $field->pluginid ) {
			// this field pluginid is not up-to-date, try to find the plugin by the php registration method as last resort: load all user plugins for that:
			if ( ! $_PLUGINS->loadPluginGroup( 'user', null, 0 ) ) {
				return false;
			}

			$field->pluginid	=	$_PLUGINS->getUserFieldPluginId( $field->type );
		}

		if ( $this->_xml === null ) {
			if ( ! $_PLUGINS->loadPluginGroup( null, array( (int) $field->pluginid ), 0 ) ) {
				return false;
			}

			$this->_xml		=&	$_PLUGINS->loadPluginXML( 'editField', $field->type, $field->pluginid );

			if ( $this->_xml === null ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Loads field XML (backend use only!)
	 * TODO: Check if we can make this one private, can't for now as it is used in FieldTable class !
	 *
	 * @param  FieldTable        $field
	 * @return SimpleXMLElement          if success, NULL if failed
	 */
	public function & _loadFieldXML( $field )
	{
		if ( $this->_fieldXml === null ) {
			if ( $this->_loadXML( $field ) ) {
				$fieldTypesXML			=	$this->_xml->getElementByPath( 'fieldtypes' );

				if ( $fieldTypesXML ) {
					$this->_fieldXml	=	$fieldTypesXML->getChildByNameAttr( 'field', 'type', $field->type );
				}
			}
		}

		return $this->_fieldXml;
	}

	/**
	 * Loads parameters editor (backend use only!)
	 *
	 * @param  FieldTable                     $field
	 * @return cbParamsEditorController|null  NULL if not existant
	 */
	private function & _loadParamsEditor( $field )
	{
		global $_PLUGINS;

		if ( $this->_loadXML( $field ) ) {
			$plugin 		=	$_PLUGINS->getPluginObject( $field->pluginid );

			$params			=	new cbParamsEditorController( $field->params, $this->_xml, $this->_xml, $plugin );
			$params->setNamespaceRegistry( 'field', $field );

			$pluginParams	=	new Registry( $plugin->params );
			$params->setPluginParams( $pluginParams );
		} else {
			$params			=	null;
		}

		return $params;
	}

	/**
	 * Methods for CB backend only (do not override):
	 */
	/**
	 * Draws parameters editor of the field paramaters (backend use only!)
	 * TODO: Should be private, but is public because FIeldTable uses it !
	 *
	 * @param  FieldTable  $field
	 * @param  array       $options
	 * @return string                HTML if editor available, or NULL
	 */
	public function drawParamsEditor( FieldTable $field, $options )
	{
		$params		=&	$this->_loadParamsEditor( $field );
		if ( $params ) {
			$params->setOptions( $options );
			return $params->draw( 'params', 'fieldtypes', 'field', 'type', $field->type, 'params', true, 'depends', 'div' );
		}

		return null;
	}

	/**
	 * Returns full label of the type of the field (backend use only!)
	 * TODO: Should be private, but is public because FIeldTable uses it !
	 *
	 * @param  FieldTable  $field
	 * @param  boolean     $checkNotSys
	 * @return boolean                   TRUE if success, FALSE if failed
	 */
	public function getFieldTypeLabel( $field, $checkNotSys = true )
	{
		$fieldXML		=&	$this->_loadFieldXML( $field );

		if ( ! $fieldXML ) {
			return null;
		}

		if ( $checkNotSys && ( $fieldXML->attributes( 'unique' ) == 'true' ) ) {
			return null;
		}

		return $fieldXML->attributes( 'label' );

	}

	/**
	 * Returns main table name of $field
	 * TODO: Should be private, but is public because FIeldTable uses it !
	 *
	 * @param  FieldTable  $field
	 * @return string
	 */
	public function getMainTable( $field )
	{
		$fieldXML										=&	$this->_loadFieldXML( $field );

		if ( $fieldXML ) {
			$db											=	$fieldXML->getElementByPath( 'database' );
			if ( $db !== false ) {

				$sqlUpgrader							=	new DatabaseUpgrade();

				return $sqlUpgrader->getMainTableName( $db, $field->name, '#__comprofiler' );
			}
		}

		return '#__comprofiler';
	}

	/**
	 * Returns array of main table columns names of $field
	 * TODO: Should be private, but is public because FIeldTable uses it !
	 *
	 * @param  FieldTable  $field
	 * @return array
	 */
	public function getMainTableColumns( $field )
	{
		$fieldXML										=	$this->_loadFieldXML( $field );
		if ( $fieldXML ) {
			$db											=	$fieldXML->getElementByPath( 'database' );
			if ( $db !== false ) {
				$sqlUpgrader							=	new DatabaseUpgrade();

				$columnsNames							=	$sqlUpgrader->getMainTableColumnsNames( $db, $field->name );
				if ( $columnsNames !== false ) {
					return $columnsNames;
				}

			}
		}

		return array( $field->name );
	}

	/**
	 * Handles SQL XML for the type of the field (backend use only!)
	 * 	<database version="1">
	 *		<table name="#__comprofilerUser" class="\CB\Database\Table\UserTable">
	 *			<columns>
	 *				<column name="_rate" nametype="namesuffix" type="sql:decimal(16,8)" unsigned="true" null="true" default="NULL" auto_increment="100" />
	 *
	 * TODO: Should be private, but is public because FIeldTable uses it !
	 *
	 * @param  FieldTable      $field                Field to adapt
	 * @param  boolean|string  $change               FALSE: only check, TRUE: change database to match description (deleting non-matching columns if $strictlyColumns == true), 'drop': uninstalls columns/tables
	 * @param  boolean         $dryRun               FALSE (default): tables are changed, TRUE: Dryrunning
	 * @param  boolean         $preferredColumnType  Enforce preferred column type
	 * @return array of array of array
	 */
	public function adaptSQL( $field, $change = true, $dryRun = false, $preferredColumnType = false )
	{
		$sqlUpgrader		=	new DatabaseUpgrade();

		$sqlUpgrader->setDryRun( $dryRun );
		$old				=	$sqlUpgrader->setEnforcePreferredColumnType( $preferredColumnType );

		$result				=	$this->checkFixSQL( $sqlUpgrader, $field, $change );

		$sqlUpgrader->setEnforcePreferredColumnType( $old );

		return $result;
	}

	/**
	 * Check or fix field according to XML description if exsitant (or old method otherwise)
	 *
	 * @param  DatabaseUpgrade  $sqlUpgrader
	 * @param  FieldTable       $field
	 * @param  boolean          $change
	 * @return boolean
	 */
	public function checkFixSQL( $sqlUpgrader, $field, $change = true )
	{
		$fieldXML										=&	$this->_loadFieldXML( $field );

		if ( $fieldXML ) {
			$db											=	$fieldXML->getElementByPath( 'database' );

			if ( $db !== false ) {
				// <database><table><columns>.... structure:
				$success								=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $field->name, $change, null );
			} else {
				$data									=	$fieldXML->getElementByPath( 'data' );

				if ( $data !== false ) {
					// <data ....> structure:
					$xmlText							=	'<?xml version="1.0" encoding="UTF-8"?>'
														.	'<database version="1">'
														.		'<table name="' . $field->table . '" maintable="true" strict="false" drop="never" shared="true">'
														.			'<columns>'
														.			'</columns>'
														.		'</table>'
														.	'</database>';

					$dbXml								=	new SimpleXMLElement( $xmlText );
					$columns							=	$dbXml->getElementByPath( 'table/columns' );
					$columns->addChildWithAttr( 'column', '', null, $data->attributes() );
					$success							=	$sqlUpgrader->checkXmlDatabaseDescription( $dbXml, $field->name, $change, null );
				} else {
					$success							=	true;
				}
			}
		} else {
			// no XML file or no <fieldtype> in xml, must be an old plugin or one which is uninstalled or missing files:
			$cols										=	$field->getTableColumns();

			if ( count( $cols ) == 0 ) {
				// the comprofiler_files database is upgraded, but this (status) field does not require comprofiler entries:
				$success								=	true;
			} else {
				// database has been upgraded, take a guess and take first column name as name of the comprofiler table:
				// or database has not been upgraded: take name:
				$colNamePrefix							=	$cols[0];

				$xmlText								=	'<?xml version="1.0" encoding="UTF-8"?>'
														.	'<database version="1">'
														.		'<table name="#__comprofiler" class="\CB\Database\Table\ComprofilerTable" maintable="true" strict="false" drop="never" shared="true">'
														.			'<columns>'
														.				'<column name="" nametype="namesuffix" type="sql:text||sql:varchar(255)" null="true" default="NULL" />'
														.			'</columns>'
														.		'</table>'
														.	'</database>';

				$dbXml									=	new SimpleXMLElement( $xmlText );
				$success								=	$sqlUpgrader->checkXmlDatabaseDescription( $dbXml, $colNamePrefix, $change, null );
			}
		}
		if ( ! $success ) {
			// Temporary way to workaround _error protected, as this whole function should probably go to to new FieldModel:
			$field->set( '_error', $sqlUpgrader->getErrors() );
		}
		/*
		var_dump( $success );
		echo "<br>\nERRORS: " . $sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
		echo "<br>\nLOGS: " . $sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
		//exit;
		*/

		return $success;
	}

	/**
	 * Sets an error message $errorText for $field of $user
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string                         $reason      'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  string                         $errorText
	 */
	protected function _setValidationError( &$field, &$user, $reason, $errorText )
	{
		$this->_setErrorMSG( $this->getFieldTitle( $field, $user, 'text', $reason ) . ' : ' .  $errorText );
	}

	/**
	 * PRIVATE method: sets the text of the last error
	 * @access private
	 *
	 * @param  string   $msg   error message
	 * @return boolean         true
	 */
	public function _setErrorMSG( $msg )
	{
		global $_PLUGINS;

		return $_PLUGINS->_setErrorMSG( $msg );
	}
}
