<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\DataShape;

use Joomla\Component\Users\Administrator\Table\TfaTable;

/**
 * @property  string  $name                Internal code of this TFA Method
 * @property  string  $display             User-facing name for this TFA Method
 * @property  string  $shortinfo           Short description of this TFA Method displayed to the user
 * @property  string  $image               URL to the logo image for this Method
 * @property  bool    $canDisable          Are we allowed to disable it?
 * @property  bool    $allowMultiple       Are we allowed to have multiple instances of it per user?
 * @property  string  $help_url            URL for help content
 * @property  bool    $allowEntryBatching  Allow authentication against all entries of this TFA Method.
 *
 * @since       __DEPLOY_VERSION__
 */
class MethodDescriptor extends DataShapeObject
{
	/**
	 * Internal code of this TFA Method
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $name = '';

	/**
	 * User-facing name for this TFA Method
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $display = '';

	/**
	 * Short description of this TFA Method displayed to the user
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $shortinfo = '';

	/**
	 * URL to the logo image for this Method
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $image = '';

	/**
	 * Are we allowed to disable it?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $canDisable = true;

	/**
	 * Are we allowed to have multiple instances of it per user?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $allowMultiple = false;

	/**
	 * URL for help content
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $help_url = '';

	/**
	 * Allow authentication against all entries of this TFA Method.
	 *
	 * Otherwise authentication takes place against a SPECIFIC entry at a time.
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $allowEntryBatching = false;

	/**
	 * Active authentication methods, used internally only
	 *
	 * @var   TfaTable[]
	 * @since __DEPLOY_VERSION__
	 * @internal
	 */
	protected $active = [];

	/**
	 * Adds an active TFA method
	 *
	 * @param   TfaTable  $record  The TFA method record to add
	 *
	 * @return void
	 * @since __DEPLOY_VERSION__
	 */
	public function addActiveMethod(TfaTable $record)
	{
		$this->active[$record->id] = $record;
	}
}
