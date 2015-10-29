<?php
/**
 * Part of the Joomla Framework Compat Package
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * CallbackFilterIterator using the callback to determine which items are accepted or rejected.
 *
 * @link   http://php.net/manual/en/class.callbackfilteriterator.php
 * @since  1.2.0
 */
class CallbackFilterIterator extends \FilterIterator
{
	/**
	 * The callback to check value.
	 *
	 * @var    callable
	 *
	 * @since  1.2.0
	 */
	protected $callback = null;

	/**
	 * Creates a filtered iterator using the callback to determine
	 * which items are accepted or rejected.
	 *
	 * @param   \Iterator  $iterator  The iterator to be filtered.
	 * @param   callable   $callback  The callback, which should return TRUE to accept the current item
	 *                                or FALSE otherwise. May be any valid callable value.
	 *                                The callback should accept up to three arguments: the current item,
	 *                                the current key and the iterator, respectively.
	 *                                ``` php
	 *                                function my_callback($current, $key, $iterator)
	 *                                ```
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.2.0
	 */
	public function __construct(\Iterator $iterator, $callback)
	{
		if (!is_callable($callback))
		{
			throw new \InvalidArgumentException("Argument 2 of CallbackFilterIterator should be callable.");
		}

		$this->callback = $callback;

		parent::__construct($iterator);
	}

	/**
	 * This method calls the callback with the current value, current key and the inner iterator.
	 * The callback is expected to return TRUE if the current item is to be accepted, or FALSE otherwise.
	 *
	 * @link    http://www.php.net/manual/en/callbackfilteriterator.accept.php
	 *
	 * @return  boolean  True if the current element is acceptable, otherwise false.
	 *
	 * @since   1.2.0
	 */
	public function accept()
	{
		$inner = $this->getInnerIterator();

		return call_user_func_array(
			$this->callback,
			array(
				$inner->current(),
				$inner->key(),
				$inner
			)
		);
	}
}
