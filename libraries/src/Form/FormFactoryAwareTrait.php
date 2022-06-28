<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

/**
 * Defines the trait for a FormFactoryInterface Aware Class.
 *
 * @since  4.0.0
 */
trait FormFactoryAwareTrait
{
    /**
     * FormFactoryInterface
     *
     * @var    FormFactoryInterface
     * @since  4.0.0
     */
    private $formFactory;

    /**
     * Get the FormFactoryInterface.
     *
     * @return  FormFactoryInterface
     *
     * @since   4.0.0
     * @throws  \UnexpectedValueException May be thrown if the FormFactory has not been set.
     */
    public function getFormFactory(): FormFactoryInterface
    {
        if ($this->formFactory) {
            return $this->formFactory;
        }

        throw new \UnexpectedValueException('FormFactory not set in ' . __CLASS__);
    }

    /**
     * Set the form factory to use.
     *
     * @param   FormFactoryInterface  $formFactory  The form factory to use.
     *
     * @return  $this
     *
     * @since   4.0.0
     */
    public function setFormFactory(FormFactoryInterface $formFactory = null)
    {
        $this->formFactory = $formFactory;

        return $this;
    }
}
