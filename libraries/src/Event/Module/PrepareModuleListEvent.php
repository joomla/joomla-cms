<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Module;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Module events.
 * Example:
 *  new AfterModuleListEvent('onEventName', ['modules' => $modules, 'loaded' => false]);
 *
 * @since  5.0.0
 */
class PrepareModuleListEvent extends ModuleListEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['modules', 'loaded', 'subject'];

    /**
     * Setter for the loaded argument.
     *
     * @param   ?bool  $value  The value to set
     *
     * @return  ?bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetLoaded(?bool $value): ?bool
    {
        return $value;
    }

    /**
     * Getter for the loaded argument.
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onGetLoaded(): bool
    {
        return $this->arguments['loaded'] ?? false;
    }

    /**
     * Getter for the loaded argument.
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getLoaded(): bool
    {
        return $this->onGetLoaded();
    }

    /**
     * Update the loaded value.
     *
     * @param   bool  $value  The value to set
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setLoaded(bool $value): static
    {
        $this->arguments['loaded'] = $this->onSetLoaded($value);

        return $this;
    }
}
