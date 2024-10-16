<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Content;

use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Content event.
 * Example:
 *  new ContentPrepareEvent('onEventName', ['context' => 'com_example.example', 'subject' => $contentObject, 'params' => $params, 'page' => $pageNum]);
 *
 * @since  5.0.0
 */
class ContentPrepareEvent extends ContentEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject', 'params', 'page'];

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  5.0.0
     */
    protected function onSetSubject(object $value): object
    {
        return $value;
    }

    /**
     * Setter for the params argument.
     *
     * @param   Registry  $value  The value to set
     *
     * @return  Registry
     *
     * @since  5.0.0
     */
    protected function onSetParams($value): Registry
    {
        // This is for b/c compatibility, because some extensions pass a mixed types
        if (!$value instanceof Registry) {
            $value = new Registry($value);

            // @TODO: In 6.0 throw an exception
            @trigger_error(
                \sprintf('The "params" attribute for the event "%s" must be type of Registry. In 6.0 it will throw an exception', $this->getName()),
                E_USER_DEPRECATED
            );
        }

        return $value;
    }

    /**
     * Setter for the page argument.
     *
     * @param   ?int  $value  The value to set
     *
     * @return  ?int
     *
     * @since  5.0.0
     */
    protected function onSetPage(?int $value): ?int
    {
        return $value;
    }

    /**
     * Getter for the item argument.
     *
     * @return  object
     *
     * @since  5.0.0
     */
    public function getItem(): object
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the item argument.
     *
     * @return  Registry
     *
     * @since  5.0.0
     */
    public function getParams(): Registry
    {
        return $this->arguments['params'];
    }

    /**
     * Getter for the page argument.
     *
     * @return  ?int
     *
     * @since  5.0.0
     */
    public function getPage(): ?int
    {
        return $this->arguments['page'];
    }
}
