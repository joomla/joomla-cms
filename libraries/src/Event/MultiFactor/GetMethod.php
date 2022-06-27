<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\MultiFactor;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;

/**
 * Concrete Event class for the onUserMultifactorGetMethod event
 *
 * @since 4.2.0
 */
class GetMethod extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeObjectAware;

    /**
     * Public constructor
     *
     * @since 4.2.0
     */
    public function __construct()
    {
        parent::__construct('onUserMultifactorGetMethod', []);

        $this->resultIsNullable        = true;
        $this->resultAcceptableClasses = [
            MethodDescriptor::class,
        ];
    }
}
