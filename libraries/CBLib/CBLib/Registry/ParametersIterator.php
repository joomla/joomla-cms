<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 7/28/14 4:20 PM $
* @package CBLib\Input
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Registry;

defined('CBLIB') or die();

/**
 * CBLib\Input\ParametersIterator Class implementation
 * 
 */
class ParametersIterator extends \ArrayIterator
{
	/**
	 * @var ParametersStore
	 */
	protected $params;

	/**
	 * Construct an ArrayIterator
	 *
	 * @link http://php.net/manual/en/arrayiterator.construct.php
	 * @see \ArrayObject::setFlags()
	 *
	 * @param  array            $array  The array or object to be iterated on.
	 * @param  int              $flags  Flags to control the behaviour of the ArrayObject object.
	 * @param  ParamsInterface  $params
	 */
	public function __construct( array $array = array(), $flags = 0, ParamsInterface $params )
	{
		parent::__construct( $array, $flags );

		$this->params	=	$params;
	}

	/**
	 * Get value for an offset
	 *
	 * @link http://php.net/manual/en/arrayiterator.offsetget.php
	 *
	 * @param  string  $index  The offset to get the value from
	 * @return mixed           The value at offset $index
	 */
	public function offsetGet( $index )
	{
		return $this->params->offsetGet( $index );
	}

	/**
	 * Return current array entry
	 *
	 * @link http://php.net/manual/en/arrayiterator.current.php
	 *
	 * @return mixed  The current array entry.
	 */
	public function current()
	{
		$value	=	parent::current();

		if ( is_array( $value ) ) {
			return $this->params->subTree( $this->key() );
		}

		return $value;
	}
}
