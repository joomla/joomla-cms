<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Service\HTML;

use Joomla\CMS\Language\Text;

/**
 * Utility class working with phpsetting
 *
 * @since  1.6
 */
class PhpSetting
{
    /**
     * Method to generate a boolean message for a value
     *
     * @param   boolean  $val  is the value set?
     *
     * @return  string html code
     */
    public function boolean($val)
    {
        return Text::_($val ? 'JON' : 'JOFF');
    }

    /**
     * Method to generate a boolean message for a value
     *
     * @param   boolean  $val  is the value set?
     *
     * @return  string html code
     */
    public function set($val)
    {
        return Text::_($val ? 'JYES' : 'JNO');
    }

    /**
     * Method to generate a string message for a value
     *
     * @param   string  $val  a php ini value
     *
     * @return  string html code
     */
    public function string($val)
    {
        return !empty($val) ? $val : Text::_('JNONE');
    }
}
