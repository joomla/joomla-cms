<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Intercept calls to PHP functions.
 *
 * @method  function_exists(string $function)
 * @method  mcrypt_list_algorithms()
 * @method  hash_algos()
 * @method  extension_loaded(string $ext)
 * @method  mcrypt_create_iv(int $bytes, int $source)
 * @method  openssl_get_cipher_methods()
 */
class FOFUtilsPhpfunc
{
	/**
	 *
	 * Magic call to intercept any function pass to it.
	 *
	 * @param string $func The function to call.
	 *
	 * @param array  $args Arguments passed to the function.
	 *
	 * @return mixed The result of the function call.
	 *
	 */
	public function __call($func, $args)
	{
		return call_user_func_array($func, $args);
	}
}
