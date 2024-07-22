<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Menu;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;
use Joomla\CMS\MVC\View\HtmlView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for menu events
 *
 * @since  5.0.0
 */
class BeforeRenderMenuItemsViewEvent extends AbstractImmutableEvent
{
    use ReshapeArgumentsAware;

    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject'];

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        // Reshape the arguments array to preserve b/c with legacy listeners
        if ($this->legacyArgumentsOrder) {
            $arguments = $this->reshapeArguments($arguments, $this->legacyArgumentsOrder);
        }

        parent::__construct($name, $arguments);

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   HtmlView  $value  The value to set
     *
     * @return  HtmlView
     *
     * @since  5.0.0
     */
    protected function onSetSubject(HtmlView $value): HtmlView
    {
        return $value;
    }

    /**
     * Getter for the view.
     *
     * @return  HtmlView
     *
     * @since  5.0.0
     */
    public function getView(): HtmlView
    {
        return $this->arguments['subject'];
    }
}
