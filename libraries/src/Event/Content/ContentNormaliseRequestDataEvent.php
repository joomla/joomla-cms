<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Content;

use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Content event
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentNormaliseRequestDataEvent extends AbstractContentEvent
{
    /**
     * The argument names (mandatory AND optional). In order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject', 'form'];

    /**
     * Setter for the form argument.
     *
     * @param   Form  $value  The value to set
     *
     * @return  Form
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setForm(Form $value): Form
    {
        return $value;
    }

    /**
     * Getter for the form.
     *
     * @return  Form
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getForm(): Form
    {
        return $this->arguments['form'];
    }

    /**
     * Getter for the data.
     *
     * @return  object|array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getData()
    {
        return $this->arguments['subject'];
    }
}
