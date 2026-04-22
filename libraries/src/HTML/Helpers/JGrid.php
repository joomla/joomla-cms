<?php

/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for creating HTML Grids
 *
 * @since  1.6
 */
abstract class JGrid
{
    /**
     * Returns an action on a grid
     */
    public static function action(
        $i,
        $task,
        $prefix = '',
        $activeTitle = '',
        $inactiveTitle = '',
        $tip = false,
        $activeClass = '',
        $inactiveClass = '',
        $enabled = true,
        $translate = true,
        $checkbox = 'cb',
        $formId = null
    ) {
        $custom = null;

        if (\is_array($prefix)) {
            $options       = $prefix;
            $activeTitle   = $options['active_title']   ?? $activeTitle;
            $inactiveTitle = $options['inactive_title'] ?? $inactiveTitle;
            $tip           = $options['tip']            ?? $tip;
            $activeClass   = $options['active_class']   ?? $activeClass;
            $inactiveClass = $options['inactive_class'] ?? $inactiveClass;
            $enabled       = $options['enabled']        ?? $enabled;
            $translate     = $options['translate']      ?? $translate;
            $checkbox      = $options['checkbox']       ?? $checkbox;
            $formId        = $options['formId']         ?? $formId;
            $prefix        = $options['prefix']         ?? '';

            $custom        = $options['custom']         ?? null;
        }

        // Build custom attribute safely
        $customAttr = '';

        if ($custom !== null) {
            try {
                $customValue = (\is_array($custom) || \is_object($custom))
                    ? json_encode($custom, JSON_THROW_ON_ERROR)
                    : (string) $custom;

                $customAttr = ' data-item-custom="'
                    . htmlspecialchars($customValue, ENT_QUOTES, 'UTF-8')
                    . '"';
            } catch (\JsonException $e) {
                $customAttr = '';
            }
        }

        $formIdAttr = $formId ? ', document.getElementById(\'' . $formId . '\')' : '';

        if ($tip) {
            $title = $enabled
                ? ($translate ? Text::_($activeTitle) : $activeTitle)
                : ($translate ? Text::_($inactiveTitle) : $inactiveTitle);

            $ariaTitle = $title;
            $class = $enabled ? $activeClass : $inactiveClass;

            return '<a'
                . ' class="tbody-icon' . ($enabled ? ' active' : '') . ' hasTooltip"'
                . ($enabled
                    ? ' href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $prefix . $task . '\'' . $formIdAttr . ')"'
                    : '')
                . ' title="' . $title . '"'
                . ' aria-label="' . $ariaTitle . '"'
                . $customAttr
                . '>'
                . '<span class="icon-' . $class . '" aria-hidden="true"></span>'
                . '</a>' . "\n";
        }

        $class = $enabled ? $activeClass : $inactiveClass;

        return '<a'
            . ' class="tbody-icon' . ($enabled ? ' active' : '') . '"'
            . ($enabled
                ? ' href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $prefix . $task . '\'' . $formIdAttr . ')"'
                : '')
            . $customAttr
            . '>'
            . '<span class="icon-' . $class . '" aria-hidden="true"></span>'
            . '</a>' . "\n";
    }

    /**
     * Returns a published state on a grid
     */
    public static function published(
        $value,
        $i,
        $prefix = '',
        $enabled = true,
        $checkbox = 'cb',
        $publishUp = null,
        $publishDown = null,
        $formId = null
    ) {
        $custom = null;

        if (\is_array($prefix)) {
            $options     = $prefix;
            $enabled     = $options['enabled']     ?? $enabled;
            $checkbox    = $options['checkbox']    ?? $checkbox;
            $publishUp   = $options['publishUp']   ?? $publishUp;
            $publishDown = $options['publishDown'] ?? $publishDown;
            $formId      = $options['formId']      ?? $formId;
            $prefix      = $options['prefix']      ?? '';
            $custom      = $options['custom']      ?? null;
        }

        $states = [
            1  => ['unpublish', 'JPUBLISHED',  'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED',  true, 'publish',   'publish'],
            0  => ['publish',   'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM',   'JUNPUBLISHED', true, 'unpublish', 'unpublish'],
            2  => ['unpublish', 'JARCHIVED',    'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED',    true, 'archive',   'archive'],
            -2 => ['publish',   'JTRASHED',     'JLIB_HTML_PUBLISH_ITEM',   'JTRASHED',     true, 'trash',     'trash'],
        ];

        $now  = Factory::getDate();
        $up   = $publishUp ? Factory::getDate($publishUp) : null;
        $down = $publishDown ? Factory::getDate($publishDown) : null;

        if ($value == 1 && $up && $now->toUnix() < $up->toUnix()) {
            $value = 4;
        }

        if ($value == 1 && $down && $now->toUnix() > $down->toUnix()) {
            $value = 5;
        }

        $actionOptions = [
            'prefix'         => $prefix,
            'checkbox'       => $checkbox,
            'formId'         => $formId,
            'enabled'        => $enabled,
            'active_title'   => $states[$value][2] ?? '',
            'inactive_title' => $states[$value][3] ?? '',
            'tip'            => $states[$value][4] ?? false,
            'active_class'   => $states[$value][5] ?? '',
            'inactive_class' => $states[$value][6] ?? '',
            'translate'      => true,
        ];

        if ($custom !== null) {
            $actionOptions['custom'] = $custom;
        }

        $task = $states[$value][0] ?? 'publish';

        return static::action($i, $task, $actionOptions);
    }
}