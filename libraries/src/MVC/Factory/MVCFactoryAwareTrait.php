<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * MVCFactory aware trait.
 *
 * @since  4.0.0
 */
trait MVCFactoryAwareTrait
{
    /**
     * The mvc factory.
     *
     * @var    MVCFactoryInterface
     * @since  4.0.0
     */
    private $mvcFactory;

    /**
     * Returns the MVC factory.
     *
     * @return  MVCFactoryInterface
     *
     * @since   4.0.0
     * @throws  \UnexpectedValueException
     */
    protected function getMVCFactory(): MVCFactoryInterface
    {
        if ($this->mvcFactory) {
            return $this->mvcFactory;
        }

        throw new \UnexpectedValueException('MVC Factory not set in ' . __CLASS__);
    }

    /**
     * Set the MVC factory.
     *
     * @param   MVCFactoryInterface  $mvcFactory  The MVC factory
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setMVCFactory(MVCFactoryInterface $mvcFactory)
    {
        $this->mvcFactory = $mvcFactory;
    }
}
