<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\Helper;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Mailtags HTML helper class.
 *
 * @since  4.0.0
 */
abstract class MailsHelper
{
    /**
     * Display a clickable list of tags for a mail template
     *
     * @param   object  $mail       Row of the mail template.
     * @param   string  $fieldname  Name of the target field.
     *
     * @return  string  List of tags that can be inserted into a field.
     *
     * @since   4.0.0
     */
    public static function mailtags($mail, $fieldname)
    {
        Factory::getApplication()->triggerEvent('onMailBeforeTagsRendering', [$mail->template_id, &$mail]);

        if (!isset($mail->params['tags']) || !\count($mail->params['tags'])) {
            return '';
        }

        $html = '<ul class="list-group">';

        foreach ($mail->params['tags'] as $tag) {
            $html .= '<li class="list-group-item">'
                . '<a href="#" class="edit-action-add-tag" data-tag="{' . strtoupper($tag) . '}" data-target="' . $fieldname . '"'
                    . ' title="' . $tag . '">' . $tag . '</a>'
                . '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Load the translation files for an extension
     *
     * @param   string  $extension  Extension name
     * @param   string  $language   Language to load
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public static function loadTranslationFiles($extension, $language = 'en-GB')
    {
        static $cache = [];

        $extension = strtolower($extension);

        if (isset($cache[$extension])) {
            return;
        }

        $lang   = Factory::getLanguage();
        $source = '';

        switch (substr($extension, 0, 3)) {
            case 'com':
            default:
                $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
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
        }

        $lang->load($extension, JPATH_ADMINISTRATOR, $language, true)
        || $lang->load($extension, $source, $language, true);

        if (!$lang->hasKey(strtoupper($extension))) {
            $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $language, true)
            || $lang->load($extension . '.sys', $source, $language, true);
        }

        $cache[$extension] = true;
    }
}
