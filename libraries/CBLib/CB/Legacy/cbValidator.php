<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 6:46 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;

defined('CBLIB') or die();

/**
 * cbValidator Class implementation
 * Form validation support class
 */
class cbValidator
{
	/**
	 * Class rules for validation
	 * @var array
	 */
	static $classRules = array();
	/**
	 * Rules for validation
	 * @var array
	 */
	static $rules = array();

	/**
	 * Add validation rules for a CSS class
	 *
	 * @param  string  $class  Name of CSS class
	 * @param  array   $rules  Associative array of rules to apply to the class
	 * @return void
	 */
	static function addClassRule( $class, $rules )
	{
		self::$classRules[$class]	=	$rules;
	}

	/**
	 * Adds a validation rule
	 *
	 * @param  string  $rule        The validation rule
	 * @param  string  $validation  The JS validation code (should return true or false)
	 * @param  string  $message     The default invalid message
	 * @return void
	 */
	static function addRule( $rule, $validation, $message = null )
	{
		if ( ! $message ) {
			$message			=	CBTxt::T( 'VALIDATION_ERROR_FIELD_NEEDS_FIX', 'Please fix this field.' );
		}

		self::$rules[$rule]		=	array( $validation, $message );
	}

	/**
	 * Returns html attributes for a validation rule
	 *
	 * @param  string  $rule     The validation rule
	 * @param  mixed   $params   The parameters to be used by the validation rule
	 * @param  string  $message  The invalid message
	 * @return string
	 */
	static function getRuleHtmlAttributes( $rule, $params = true, $message = null )
	{
		if ( is_bool( $params ) ) {
			$params			=	( $params ? 'true' : 'false' );
		} elseif ( is_array( $params ) || is_object( $params ) ) {
			$params			=	json_encode( $params );
		} elseif ( $rule == 'pattern' ) {
			$params			=	addslashes( $params );
		}

		$attributes			=	' data-rule-' . htmlspecialchars( $rule ) . '="' . htmlspecialchars( $params ) . '"';

		if ( $message ) {
			$attributes		.=	' data-msg-' . htmlspecialchars( $rule ) . '="' . htmlspecialchars( $message ) . '"';
		}

		return $attributes;
	}

	/**
	 * Returns html attributes for a validation messages
	 *
	 * @param  string  $validMessage    The valid message
	 * @param  string  $invalidMessage  The invalid message
	 * @return string
	 */
	static function getMsgHtmlAttributes( $validMessage = null, $invalidMessage = null )
	{
		$attributes			=	null;

		if ( $validMessage ) {
			$attributes		.=	' data-msg-success="' . htmlspecialchars( $validMessage ) . '"';
		}

		if ( $invalidMessage ) {
			$attributes		.=	' data-msg="' . htmlspecialchars( $invalidMessage ) . '"';
		}

		return $attributes;
	}

	/**
	 * Returns html attributes for a validation submit button
	 *
	 * @param  string  $submitMessage   The message to change the button to on submit
	 * @return string
	 */
	static function getSubmitBtnHtmlAttributes( $submitMessage = null )
	{
		if ( ! $submitMessage ) {
			$submitMessage	=	CBTxt::T( 'FORM_SUBMIT_LOADING', 'Loading...' );
		}

		return ' data-submit-text="' . htmlspecialchars( $submitMessage ) . '"';
	}

	/**
	 * Loads the CB jQuery Validation into the header
	 *
	 * @param  string  $selector  The jQuery selector to bind validation to
	 * @return void
	 */
	static function loadValidation( $selector = '.cbValidation' )
	{
		global $_CB_framework;

		static $options				=	null;

		if ( ! $options ) {
			$liveSite				=	$_CB_framework->getCfg( 'live_site' ) . ( $_CB_framework->getUi() == 2 ? '/administrator' : null );
			$cbSpoofField			=	cbSpoofField();
			$cbSpoofString			=	cbSpoofString( null, 'fieldclass' );
			$regAntiSpamFieldName	=	cbGetRegAntiSpamFieldName();
			$regAntiSpamValues		=	cbGetRegAntiSpams();

			$messages				=	array(	'required' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_REQUIRED', 'This field is required.' ) ),
													'remote' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_NEEDS_FIX', 'Please fix this field.' ) ),
													'email' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_EMAIL', 'Please enter a valid email address.' ) ),
													'url' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_URL', 'Please enter a valid URL.' ) ),
													'date' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_DATE', 'Please enter a valid date.' ) ),
													'dateISO' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_DATE_ISO', 'Please enter a valid date (ISO).' ) ),
													'number' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_NUMBER', 'Please enter a valid number.' ) ),
													'digits' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_DIGITS_ONLY', 'Please enter only digits.' ) ),
													'creditcard' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_CREDIT_CARD_NUMBER', 'Please enter a valid credit card number.' ) ),
													'equalTo' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_SAME_VALUE_AGAIN', 'Please enter the same value again.' ) ),
													'accept' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_EXTENSION', 'Please enter a value with a valid extension.' ) ),
													'maxlength' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_MORE_THAN_CHARS', 'Please enter no more than {0} characters.' ) ),
													'minlength' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_LEAST_CHARS', 'Please enter at least {0} characters.' ) ),
													'rangelength' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_BETWEEN_AND_CHARS', 'Please enter a value between {0} and {1} characters long.' ) ),
													'range' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_BETWEEN_AND_NUMBER', 'Please enter a value between {0} and {1}.' ) ),
													'max' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_LESS_OR_EQUAL_TO', 'Please enter a value less than or equal to {0}.' ) ),
													'min' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_GREATER_OR_EQUAL_TO', 'Please enter a value greater than or equal to {0}.' ) ),
													'maxWords' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_MORE_THAN_WORDS', 'Please enter {0} words or less.' ) ),
													'minWords' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_LEAST_WORDS', 'Please enter at least {0} words.' ) ),
													'rangeWords' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_BETWEEN_AND_WORDS', 'Please enter between {0} and {1} words.' ) ),
													'extension' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_EXTENSION', 'Please enter a value with a valid extension.' ) ),
													'pattern' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_PATTERN', 'Invalid format.' ) ),
													'cbfield' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_NEEDS_FIX', 'Please fix this field.' ) ),
													'cbremote' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_NEEDS_FIX', 'Please fix this field.' ) ),
													'cbusername' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_USERNAME', 'Please enter a valid username with no space at beginning or end and must not contain the following characters: < > \ " \' % ; ( ) &' ) ),
													'cburl' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FIELD_URL', 'Please enter a valid URL.' ) ),
													'filesize' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FILZSIZE', 'File size must exceed the minimum of {0} {2}s, but not the maximum of {1} {2}s.' ) ),
													'filesizemin' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FILZSIZE_MIN', 'File size exceeds the minimum of {0} {2}s.' ) ),
													'filesizemax' => addslashes( CBTxt::T( 'VALIDATION_ERROR_FILZSIZE_MAX', 'File size exceeds the maximum of {1} {2}s.' ) )
			);

			$settings				=	array();

			$settings['cbfield']	=	array(	'url' => addslashes( $liveSite . '/index.php?option=com_comprofiler&view=fieldclass&function=[function]&user=[user]&field=[field]&reason=[reason]&format=raw' ),
												   'spooffield' => addslashes( $cbSpoofField ),
												   'spoofstring' => addslashes( $cbSpoofString ),
												   'spamfield' => addslashes( $regAntiSpamFieldName ),
												   'spamstring' => addslashes( $regAntiSpamValues[0] )
			);

			$options				=	array( 'messages' => $messages, 'settings' => $settings );
		}

		$js							=	null;

		static $selectors			=	array();

		if ( ! isset( $selectors[$selector] ) ) {
			$selectors[$selector]	=	true;

			$js						.=	"$( '" . addslashes( $selector ) . "' ).cbvalidate(" . json_encode( $options ) . ");";
		}

		static $rules				=	array();

		foreach ( self::$rules as $method => $rule ) {
			if ( ! isset( $rules[$method] ) ) {
				$rules[$method]		=	true;

				$js					.=	"$.validator.addMethod( '" . addslashes( $method ) . "', function( value, element, params ) {"
									.		$rule[0]
									.	"}, $.validator.format( '" . addslashes( $rule[1] ) . "' ) );";
			}
		}

		static $classRules			=	array();

		foreach ( self::$classRules as $class => $rules ) {
			if ( ! isset( $classRules[$class] ) ) {
				$classRules[$class]	=	true;

				$js					.=	"$.validator.addClassRules( '" . addslashes( $class ) . "', JSON.parse( '" . addcslashes( json_encode( $rules ), "'" ) . "' ) );";
			}
		}

		if ( $js ) {
			$_CB_framework->outputCbJQuery( $js, 'cbvalidate' );
		}
	}

	/**
	 * Outputs the validator Javascript into the header using the CB jQuery output methods
	 * @deprecated 2.0 Use loadValidation instead
	 *
	 * @param  string  $js        [optional] Additional Javascript to output
	 * @param  string  $selector  [optional] CSS Selector for the outer DOM element to apply validation for [default '#cbcheckedadminForm']
	 * @return void
	 */
	static function outputValidatorJs( $js = null, $selector = '#cbcheckedadminForm' )
	{
		global $_CB_framework;

		if ( ! $selector ) {
			$selector	=	'#cbcheckedadminForm';
		}

		if ( $js ) {
			$_CB_framework->outputCbJQuery( $js );
		}

		self::loadValidation( $selector );
	}
}
