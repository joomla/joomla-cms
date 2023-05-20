<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a MVC factory service class.
 *
 * @since  4.0.0
 */
trait MVCFactoryServiceTrait
{
    /**
     * The MVC Factory.
     *
     * @var MVCFactoryInterface
     */
    private $mvcFactory;

    /**
     * Get the factory.
     *
     * @return  MVCFactoryInterface
     *
     * @since   4.0.0
     * @throws  \UnexpectedValueException May be thrown if the factory has not been set.
     */
    public function getMVCFactory(): MVCFactoryInterface
    {
        if (!$this->mvcFactory) {
            throw new \UnexpectedValueException('MVC factory not set in ' . __CLASS__);
        }

        return $this->mvcFactory;
    }

    /**
     * The MVC Factory.
     *
     * @param   MVCFactoryInterface  $mvcFactory  The factory
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function setMVCFactory(MVCFactoryInterface $mvcFactory)
    {
        $this->mvcFactory = $mvcFactory;
    }
}
