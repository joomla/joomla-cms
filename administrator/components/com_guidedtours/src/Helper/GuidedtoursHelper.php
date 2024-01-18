<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Helper;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Guidedtours component helper.
 *
 * @since  5.0.0
 */
class GuidedtoursHelper
{
    /**
     * Load the translation files for an Guided Tour
     *
     * @param   string  $uid    Guided Tour Unique Identifier
     * @param   boolean $steps  Should tour steps language file be loaded
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public static function loadTranslationFiles($uid, bool $steps = false)
    {
        static $cache = [];
        $uid          = strtolower($uid);

        if (isset($cache[$uid])) {
            return;
        }

        $lang   = Factory::getLanguage();

        // The uid has an extension separator so we need to check the extension language files
        if (strpos($uid, '.') > 0) {
            list($extension, $tourid) = explode('.', $uid, 2);

            $source = '';

            switch (substr($extension, 0, 3)) {
                case 'com':
                    $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
                    break;

                case 'lib':
                    $source = JPATH_LIBRARIES . '/' . substr($extension, 4);
                    break;

                case 'mod':
                    $source = JPATH_SITE . '/modules/' . $extension;
                    break;

                case 'plg':
                    $parts = explode('_', $extension, 3);

                    if (\count($parts) > 2) {
                        $source = JPATH_PLUGINS . '/' . $parts[1] . '/' . $parts[2];
                    }
                    break;

                case 'pkg':
                    $source = JPATH_SITE;
                    break;

                case 'tpl':
                    $source = JPATH_BASE . '/templates/' . substr($extension, 4);
                    break;

                default:
                    $source = JPATH_ADMINISTRATOR . '/components/com_' . $extension;
                    break;
            }

            $lang->load($extension . '.' . str_replace('-', '_', $tourid), JPATH_ADMINISTRATOR)
                || $lang->load($extension . '.' . str_replace('-', '_', $tourid), $source);
            if ($steps) {
                $lang->load($extension . '.' . str_replace('-', '_', $tourid) . '_steps', JPATH_ADMINISTRATOR)
                    || $lang->load($extension . '.' . str_replace('-', '_', $tourid) . '_steps', $source);
            }
        } else {
            $lang->load('guidedtours.' . str_replace('-', '_', $uid), JPATH_ADMINISTRATOR);
            if ($steps) {
                $lang->load('guidedtours.' . str_replace('-', '_', $uid) . '_steps', JPATH_ADMINISTRATOR);
            }
        }

        $cache[$uid] = true;
    }
}
