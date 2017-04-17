<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 07.06.13 21:17 $
* @package CBLib\AhaWow
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Input;

use CBLib\Registry\GetterInterface;
use CBLib\Registry\ParametersStore;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Input Class implementation
 * 
 */
class Input extends ParametersStore implements InputInterface
{
	/**
	 * Default type for get() method (null = raw, or GetterInterface::COMMAND
	 * @var string|null
	 */
	protected $defaultGetType	=	GetterInterface::COMMAND;

	/**
	 * If $source is provided, it becomes the input by reference,
	 * means any changes to $source are reflected to $this
	 *
	 * @param  array    $source   Source data, unescaped
	 * @param  boolean  $srcGpc   Source is GPC (Get Post Cookies)
	 */
	public function __construct( $source = array(), $srcGpc = false )
	{
		$this->params	=	$source;
		$this->srcGpc	=   $srcGpc && get_magic_quotes_gpc();
	}

	/**
	 * Get sub-Input
	 *
	 * @param   string          $key  Name of index or input-name-encoded array selection, e.g. a.b.c
	 * @return  InputInterface        Sub-Registry or empty array() added to tree if not existing
	 */
	public function subTree( $key )
	{
		$subTree				=	parent::subTree( $key );

		if ( $subTree instanceof self ) {
			$subTree->srcGpc	=	$this->srcGpc;
		}

		return $subTree;
	}

	/**
	 * Gets the request method.
	 *
	 * @return  string   The request method.
	 */
	public function getRequestMethod( )
	{
		global $_SERVER;

		return strtoupper( $_SERVER['REQUEST_METHOD'] );
	}

	/**
	 * Gets an array of IP addresses taking in account the proxys on the way.
	 * An array is needed because FORWARDED_FOR can be facked as well.
	 *
	 * @return array of IP addresses, first one being host, and last one last proxy (except fackings)
	 */
	public function getRequestIPArray( )
	{
		$SERVER						=	$this->getNamespaceRegistry( 'server' );
		$ipAddressesArray			=	array();

		if ( isset( $SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			if ( strpos( $SERVER['HTTP_X_FORWARDED_FOR'], ',' ) ) {
				$ipAddressesArray	+=	explode( ',', $SERVER['HTTP_X_FORWARDED_FOR'] );
			} else {
				$ipAddressesArray[]	=	$SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		$ipAddressesArray[]			 =	$SERVER['REMOTE_ADDR'];

		return $ipAddressesArray;
	}

	/**
	 * Gets a comma-separated list of IP addresses taking in account the proxys on the way.
	 * An array is needed because FORWARDED_FOR can be facked as well.
	 *
	 * @return string of IP addresses, first one being host, and last one last proxy (except fackings)
	 */
	public function getRequestIPlist( )
	{
		return addslashes( implode( ',', $this->getRequestIPArray() ) );
	}
}
