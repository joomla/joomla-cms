<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Captcha;

use Joomla\CMS\Form\Field\CaptchaField;
use SimpleXMLElement;

/**
 * Interface defining a captcha plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
interface CaptchaPluginInterface
{
    /**
     * Gets the challenge HTML
     *
     * @param   string  $id     The id of the field
     * @param   string  $class  The class of the field
     *
     * @return  string  The HTML to be embedded in the form
     *
     * @since   __DEPLOY_VERSION__
     */
    public function display(string $id = '', string $class = ''): string;

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct.
     *
     * @param   string  $code  Answer provided by user
     *
     * @return  bool    If the answer is correct, false otherwise
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    public function checkAnswer(string $code = null): bool;

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   CaptchaField      $field    Captcha field instance
     * @param   SimpleXMLElement  $element  XML form definition
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setupField(CaptchaField $field, SimpleXMLElement $element): void;
}
