<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Helper;

use Joomla\CMS\Filter\InputFilter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Languages component helper.
 *
 * @since  1.6
 */
class LanguagesHelper
{
    /**
     * Filter method for language keys.
     * This method will be called by \JForm while filtering the form data.
     *
     * @param   string  $value  The language key to filter.
     *
     * @return  string  The filtered language key.
     *
     * @since       2.5
     */
    public static function filterKey($value)
    {
        $filter = InputFilter::getInstance([], [], InputFilter::ONLY_BLOCK_DEFINED_TAGS, InputFilter::ONLY_BLOCK_DEFINED_ATTRIBUTES);

        return strtoupper($filter->clean($value, 'cmd'));
    }

    /**
     * Filter method for language strings.
     * This method will be called by \JForm while filtering the form data.
     *
     * @param   string  $value  The language string to filter.
     *
     * @return  string  The filtered language string.
     *
     * @since       2.5
     */
    public static function filterText($value)
    {
        $filter = InputFilter::getInstance([], [], InputFilter::ONLY_BLOCK_DEFINED_TAGS, InputFilter::ONLY_BLOCK_DEFINED_ATTRIBUTES);

        return $filter->clean($value);
    }
}
