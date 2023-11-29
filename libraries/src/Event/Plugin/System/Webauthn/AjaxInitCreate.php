<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Plugin\System\Webauthn;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Webauthn\PublicKeyCredentialCreationOptions;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Concrete event class for the onAjaxWebauthnInitcreate event
 *
 * @since  4.2.0
 */
class AjaxInitCreate extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeObjectAware;

    /**
     * Constructor
     *
     * @param   string  $name       Event name
     * @param   array   $arguments  Event arguments
     *
     * @since 4.2.0
     */
    public function __construct(string $name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        $this->resultAcceptableClasses = [
            \stdClass::class,
            PublicKeyCredentialCreationOptions::class,
        ];
    }
}
