<?php
/**
 * @package     Joomla
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomla;


interface JAuthorizeInterface
{
	public function check($actor, $target, $action);

	public function allow($actor, $target, $action);

	public function deny($actor, $target, $action);
}