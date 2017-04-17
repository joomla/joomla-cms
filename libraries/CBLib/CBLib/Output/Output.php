<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 07.06.13 23:17 $
* @package CBLib\AhaWow
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Output;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Output Class implementation
 * 
 */
class Output implements OutputInterface
{

	/**
	 * @var string
	 */
	private $output		=	'';

	/**
	 * Constructor
	 */
	private function __construct()
	{
		// This avoids using new Output and forces Output::create()
	}

	/**
	 * Creates a new output of $type
	 *
	 * @param  string  $type
	 * @param  array   $parameters
	 * @return static
	 */
	public static function createNew( $type, array $parameters )
	{
		return new static();
	}

	/**
	 * Appends output to this output object
	 *
	 * @param  string  $output
	 * @return void
	 */
	public function append( $output )
	{
		$this->output	.=	$output;
	}

	/**
	 * Magic function to convert object to string
	 *
	 * @return string
	 */
	public function __toString( )
	{
		return $this->output;
	}
}
