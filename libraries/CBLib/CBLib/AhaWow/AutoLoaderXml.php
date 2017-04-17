<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 1/11/14 7:38 PM $
* @package CBLib\AhaWow
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow;

use CBLib\Xml\SimpleXMLElement;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\AutoLoaderXml Class implementation
 * 
 */
class AutoLoaderXml {
	/**
	 * Generic class mappings using regex
	 * e.g. '/^(cbpaidController.*|cbpaid.*Handler)$/'						=>	'controllers/$1.php'
	 *
	 * @var array
	 */
	private $maps				=	array();

	/**
	 * contains dirname( __DIR__ ) . DIRECTORY_SEPARATOR
	 *
	 * @var string
	 */
	private $libraryDirSlash;

	/**
	 * Constructor
	 */
	public function __construct( )
	{
		$this->libraryDirSlash		=	dirname( __DIR__ ) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Registers a Mapping Regexp mapping
	 * @param  string  $routingRegexp  Matching Regular expression for the class name
	 * @param  string  $folderRegexp   Replacing Regexp for the corresponding folder (using regexp substitutions
	 * @return void
	 */
	public function registerMap( $routingRegexp, $folderRegexp )
	{
		$this->maps[$routingRegexp]	=	$folderRegexp;
	}

	/**
	 * Loads XML file
	 *
	 * @param  array                     $route  Route from RouterInterface::getMainRoutingArgs()
	 * @return SimpleXMLElement|boolean
	 */
	public function loadXML( $route )
	{
		//TODO more here

		// Final resort: Try finding XML entry for the current extension being executed:

		$xmlFileNameAndPath			=	$this->findXmlFile( $route );

		if ( $xmlFileNameAndPath )
		{
			$element				=	new SimpleXMLElement( trim( file_get_contents( $xmlFileNameAndPath ) ) );
			$element['xmlfilepath']	=	$xmlFileNameAndPath;
			return $element;
		}

		return false;
	}

	/**
	 * Finds suitable xml filename with path or null if not found
	 *
	 * @param  array  $route
	 * @return string|null
	 */
	protected function findXmlFile( $route )
	{
		foreach ( $route as & $v )
		{
			// Clean for safe file-path:
			$v			=	preg_replace( '/[^A-Za-z0-9_\.-]/', '', $v );
		}
		$mapKey			=	rtrim( implode( '/', $route ), '/' );

		while ( $mapKey )
		{
			if ( isset( $this->maps[$mapKey] ) && is_readable( $this->maps[$mapKey] ) )
			{
				return $this->maps[$mapKey];
			}

			$lastSlashPosition	=	strrpos( $mapKey, '/' );
			$mapKey		=	substr( $mapKey, 0, $lastSlashPosition !== false ? $lastSlashPosition : 0 );
		}

		return null;
	}
}
