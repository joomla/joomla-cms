<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for User save event.
 * Example:
 *  new BeforeSaveEvent('onEventName', ['subject' => $oldUserArray, 'isNew' => $isNew, 'data' => $data]);
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeSaveEvent extends AbstractSaveEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeBooleanAware;

    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'isNew', 'data'];

    /**
     * Setter for the data argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setData(array $value): array
    {
        return $value;
    }

    /**
     * Getter for the data.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getData(): array
    {
        return $this->arguments['data'] ?? [];
    }
}
