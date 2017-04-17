<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 7:49 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Input\InjectionsFilter;

defined('CBLIB') or die();

/**
 * CBInputFilter Class implementation
 *
 * @deprecated 2.0: use \CBLib\Input\Input::get() instead, or $value = \CBLib\Input\Get::clean( $value, \CBLib\Registry\GetterInterface::HTML )
 * @see \CBLib\Input\Input::get()
 * @see \CBLib\Input\Get::clean(()
 * @see \CBLib\Input\InjectionsFilter
 */
class CBInputFilter
{
	public $tagsArray;			// default = empty array
	public $attrArray;			// default = empty array

	public $tagsMethod;			// default = 0
	public $attrMethod;			// default = 0

	public $xssAuto;			// default = 1
	public $tagBlacklist	=	array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');
	public $attrBlacklist	=	array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');  // also will strip ALL event handlers

	/**
	 * @var InjectionsFilter
	 */
	protected $filter;

	/**
	 * Constructor. Only first parameter is required.
	 * @deprecated 2.0: use \CBLib\Input\Input::get() instead, or $value = \CBLib\Input\Get::clean( $value, \CBLib\Registry\GetterInterface::HTML )
	 *
	 * @param  array  $tagsArray   List of user-defined tags
	 * @param  array  $attrArray   List of user-defined attributes
	 * @param  int    $tagsMethod  0= allow just user-defined,    1= allow all but user-defined
	 * @param  int    $attrMethod  0= allow just user-defined,    1= allow all but user-defined
	 * @param  int    $xssAuto     0= only auto clean essentials, 1= allow clean blacklisted tags/attr
	 */
	public function __construct( $tagsArray = null, $attrArray = null, $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1 )
	{
		if ( $tagsArray === null ) {
			$tagsArray		=	array();
		}
		if ( $attrArray === null ) {
			$attrArray		=	array();
		}
		// make sure user defined arrays are in lowercase
		$this->tagsArray	=	array_map( 'strtolower', (array) $tagsArray );
		$this->attrArray	=	array_map( 'strtolower', (array) $attrArray );
		$this->tagsMethod	=	$tagsMethod;
		$this->attrMethod	=	$attrMethod;
		$this->xssAuto		=	$xssAuto;
	}

	/**
	 * Method to be called by another php script. Processes for XSS and specified bad code.
	 * @deprecated 2.0: use \CBLib\Input\Input::get() instead, or $value = \CBLib\Input\Get::clean( $value, \CBLib\Registry\GetterInterface::HTML )
	 *
	 * @param  mixed  $source  Input string/array-of-string to be 'cleaned'
	 * @return mixed  $source  'Cleaned' version of input parameter
	 */
	public function process( $source )
	{
		return InjectionsFilter::getInstance( $this->tagsArray, $this->attrArray, $this->tagsMethod, $this->attrMethod, $this->xssAuto )
			->process( $source );
	}
}
