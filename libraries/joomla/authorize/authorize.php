<?php
/**
 * @package     Joomla
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomla;


class JAuthorize implements JAuthorizeInterface
{
	private $implementation = null;

	protected static $authorizeMatrix;

	public function __construct( JAuthorizeInterface $implementation )
	{
		$this->implementation = $implementation;
	}

	public static function getInstance( JAuthorizeInterface $implementation = null)
	{

	}

	public function check($actor, $target, $action)
	{
		return $this->implementation->check($actor, $target, $action);
	}

	public function allow()
	{
		return $this->implementation->allow($actor, $target, $action);
	}

	public function deny()
	{
		return $this->implementation->deny($actor, $target, $action);
	}
}