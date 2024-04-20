<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Captcha;

use Joomla\CMS\Form\FormField;

/**
 * Captcha Provider Interface
 *
 * @since   5.0.0
 */
interface CaptchaProviderInterface
{
    /**
     * Return Captcha name, CMD string.
     *
     * @return string
     * @since   5.0.0
     */
    public function getName(): string;

    /**
     * Gets the challenge HTML
     *
     * @param   string  $name        Input name
     * @param   array   $attributes  The class of the field
     *
     * @return  string  The HTML to be embedded in the form
     *
     * @since   5.0.0
     *
     * @throws  \RuntimeException
     */
    public function display(string $name = '', array $attributes = []): string;

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct.
     *
     * @param   ?string  $code  Answer provided by user
     *
     * @return  bool  If the answer is correct, false otherwise
     *
     * @since   5.0.0
     *
     * @throws  \RuntimeException
     */
    public function checkAnswer(?string $code = null): bool;

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   FormField          $field    Captcha field instance
     * @param   \SimpleXMLElement  $element  XML form definition
     *
     * @return void
     *
     * @since  5.0.0
     *
     * @throws  \RuntimeException
     */
    public function setupField(FormField $field, \SimpleXMLElement $element): void;
}
