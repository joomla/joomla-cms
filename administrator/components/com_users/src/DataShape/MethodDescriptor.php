<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\DataShape;

use Joomla\Component\Users\Administrator\Table\MfaTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property  string  $name                Internal code of this MFA Method
 * @property  string  $display             User-facing name for this MFA Method
 * @property  string  $shortinfo           Short description of this MFA Method displayed to the user
 * @property  string  $image               URL to the logo image for this Method
 * @property  bool    $canDisable          Are we allowed to disable it?
 * @property  bool    $allowMultiple       Are we allowed to have multiple instances of it per user?
 * @property  string  $help_url            URL for help content
 * @property  bool    $allowEntryBatching  Allow authentication against all entries of this MFA Method.
 *
 * @since       4.2.0
 */
class MethodDescriptor extends DataShapeObject
{
    /**
     * Internal code of this MFA Method
     *
     * @var   string
     * @since 4.2.0
     */
    protected $name = '';

    /**
     * User-facing name for this MFA Method
     *
     * @var   string
     * @since 4.2.0
     */
    protected $display = '';

    /**
     * Short description of this MFA Method displayed to the user
     *
     * @var   string
     * @since 4.2.0
     */
    protected $shortinfo = '';

    /**
     * URL to the logo image for this Method
     *
     * @var   string
     * @since 4.2.0
     */
    protected $image = '';

    /**
     * Are we allowed to disable it?
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $canDisable = true;

    /**
     * Are we allowed to have multiple instances of it per user?
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $allowMultiple = false;

    /**
     * URL for help content
     *
     * @var   string
     * @since 4.2.0
     */
    protected $help_url = '';

    /**
     * Allow authentication against all entries of this MFA Method.
     *
     * Otherwise authentication takes place against a SPECIFIC entry at a time.
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $allowEntryBatching = false;

    /**
     * Active authentication methods, used internally only
     *
     * @var   MfaTable[]
     * @since 4.2.0
     * @internal
     */
    protected $active = [];

    /**
     * Adds an active MFA method
     *
     * @param   MfaTable  $record  The MFA method record to add
     *
     * @return void
     * @since 4.2.0
     */
    public function addActiveMethod(MfaTable $record)
    {
        $this->active[$record->id] = $record;
    }
}
