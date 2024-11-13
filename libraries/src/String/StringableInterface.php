<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\String;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A transitioning interface to PHP implicit \Stringable interface
 *
 * @since  __DEPLOY_VERSION__
 */
interface StringableInterface
{
    /**
     * To String magick.
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __toString(): string;
}
