<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for form elements
 *
 * @since  1.5
 */
abstract class Form
{
    /**
     * Array containing information for loaded files.
     *
     * @var    array
     *
     * @since  3.8.0
     */
    protected static $loaded = array();

    /**
     * Displays a hidden token field to reduce the risk of CSRF exploits
     *
     * Use in conjunction with Session::checkToken()
     *
     * @param   array  $attribs  Input element attributes.
     *
     * @return  string  A hidden input field with a token
     *
     * @see     Session::checkToken()
     * @since   1.5
     */
    public static function token(array $attribs = array())
    {
        $attributes = '';

        if ($attribs !== array()) {
            $attributes .= ' ' . ArrayHelper::toString($attribs);
        }

        return '<input type="hidden" name="' . Session::getFormToken() . '" value="1"' . $attributes . '>';
    }

    /**
     * Add CSRF form token to Joomla script options that developers can get it by Javascript.
     *
     * @param   string  $name  The script option key name.
     *
     * @return  void
     *
     * @since   3.8.0
     */
    public static function csrf($name = 'csrf.token')
    {
        if (isset(static::$loaded[__METHOD__][$name])) {
            return;
        }

        /** @var HtmlDocument $doc */
        $doc = Factory::getDocument();

        if (!$doc instanceof HtmlDocument || $doc->getType() !== 'html') {
            return;
        }

        $doc->addScriptOptions($name, Session::getFormToken());

        static::$loaded[__METHOD__][$name] = true;
    }
}
