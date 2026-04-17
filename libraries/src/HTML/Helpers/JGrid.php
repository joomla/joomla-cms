<?php

/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for creating HTML grids.
 *
 * @since  1.6
 */
abstract class JGrid
{
    /**
     * Returns an action button for use in a list view grid.
     *
     * Supports being called either with individual parameters or with a single
     * $prefix options array containing all configuration keys.
     *
     * @param   integer      $i              The row index.
     * @param   string       $task           The task to perform on click (e.g. 'publish').
     * @param   string|array $prefix         The task prefix, or an options array.
     * @param   string       $activeTitle    The tooltip title when item is active/enabled.
     * @param   string       $inactiveTitle  The tooltip title when item is inactive/disabled.
     * @param   boolean      $tip            Whether to show a tooltip.
     * @param   string       $activeClass    The CSS icon class for the active state.
     * @param   string       $inactiveClass  The CSS icon class for the inactive state.
     * @param   boolean      $enabled        Whether the button should be interactive.
     * @param   boolean      $translate      Whether to pass title strings through Text::_().
     * @param   string       $checkbox       The checkbox name prefix (default: 'cb').
     * @param   string|null  $formId         The form element ID, if applicable.
     *
     * @return  string  The generated HTML string.
     *
     * @since   1.6
     */
    public static function action(
        int $i,
        string $task,
        $prefix = '',
        string $activeTitle = '',
        string $inactiveTitle = '',
        bool $tip = false,
        string $activeClass = '',
        string $inactiveClass = '',
        bool $enabled = true,
        bool $translate = true,
        string $checkbox = 'cb',
        ?string $formId = null
    ): string {
        $html    = [];
        $options = [];

        // Allow all options to be passed as an array via the $prefix parameter.
        if (\is_array($prefix)) {
            $options       = $prefix;
            $activeTitle   = $options['active_title']   ?? $activeTitle;
            $inactiveTitle = $options['inactive_title'] ?? $inactiveTitle;
            $tip           = (bool) ($options['tip']    ?? $tip);
            $activeClass   = $options['active_class']   ?? $activeClass;
            $inactiveClass = $options['inactive_class'] ?? $inactiveClass;
            $enabled       = (bool) ($options['enabled']   ?? $enabled);
            $translate     = (bool) ($options['translate'] ?? $translate);
            $checkbox      = $options['checkbox']       ?? $checkbox;
            $prefix        = $options['prefix']         ?? '';
        }

        // Build an optional safe custom data attribute.
        $customDataAttr = '';

        if (!empty($options['custom'])) {
            $customValue = \is_array($options['custom'])
                ? htmlspecialchars(json_encode($options['custom']), ENT_QUOTES, 'UTF-8')
                : htmlspecialchars((string) $options['custom'], ENT_QUOTES, 'UTF-8');

            $customDataAttr = ' data-item-custom="' . $customValue . '"';
        }

        // Initialise tooltip variables to safe defaults.
        $ariaid = '';
        $title  = '';

        if ($tip) {
            $title  = $enabled ? $activeTitle : $inactiveTitle;
            $title  = $translate ? Text::_($title) : $title;
            $ariaid = $checkbox . $task . $i . '-desc';

            // Suppress tooltip if the resolved title is empty.
            if ($title === '') {
                $tip = false;
            }
        }

        if ($enabled) {
            Factory::getDocument()->getWebAssetManager()->useScript('list-view');

            $html[] = '<a href="#"'
                . ' class="js-grid-item-action tbody-icon'
                . ($activeClass === 'publish' ? ' active' : '') . '"';

            $html[] = ' data-item-id="'
                . htmlspecialchars($checkbox . $i, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-item-task="'
                . htmlspecialchars($prefix . $task, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-item-form-id="'
                . htmlspecialchars((string) $formId, ENT_QUOTES, 'UTF-8') . '"';

            $html[] = $customDataAttr;

            if ($tip && $ariaid !== '') {
                $html[] = ' aria-labelledby="'
                    . htmlspecialchars($ariaid, ENT_QUOTES, 'UTF-8') . '"';
            }

            $html[] = '>';
            $html[] = LayoutHelper::render('joomla.icon.iconclass', ['icon' => $activeClass]);
            $html[] = '</a>';

            if ($tip && $ariaid !== '') {
                $html[] = '<div role="tooltip" id="'
                    . htmlspecialchars($ariaid, ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
                    . '</div>';
            }
        } else {
            $html[] = '<span class="tbody-icon jgrid"';

            if ($tip && $ariaid !== '') {
                $html[] = ' aria-labelledby="'
                    . htmlspecialchars($ariaid, ENT_QUOTES, 'UTF-8') . '"';
            }

            $html[] = '>';
            $html[] = LayoutHelper::render('joomla.icon.iconclass', ['icon' => $inactiveClass]);
            $html[] = '</span>';

            if ($tip && $ariaid !== '') {
                $html[] = '<div role="tooltip" id="'
                    . htmlspecialchars($ariaid, ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
                    . '</div>';
            }
        }

        return implode('', $html);
    }

    /**
     * Returns a state toggle button based on an array of possible states.
     *
     * The $states array should be keyed by state value. Each entry may use
     * either named keys (task, active_title, etc.) or numeric indices
     * for backward compatibility with legacy Joomla code.
     *
     * @param   array        $states     Associative or indexed array of state definitions.
     * @param   mixed        $value      The current state value.
     * @param   integer      $i          The row index.
     * @param   string|array $prefix     The task prefix, or an options array.
     * @param   boolean      $enabled    Whether the button should be interactive.
     * @param   boolean      $translate  Whether to translate title strings.
     * @param   string       $checkbox   The checkbox name prefix.
     * @param   string|null  $formId     The form element ID, if applicable.
     *
     * @return  string  The generated HTML string.
     *
     * @since   1.6
     */
    public static function state(
        array $states,
        $value,
        int $i,
        $prefix = '',
        bool $enabled = true,
        bool $translate = true,
        string $checkbox = 'cb',
        ?string $formId = null
    ): string {
        if (\is_array($prefix)) {
            $options   = $prefix;
            $enabled   = (bool) ($options['enabled']   ?? $enabled);
            $translate = (bool) ($options['translate'] ?? $translate);
            $checkbox  = $options['checkbox']          ?? $checkbox;
            $prefix    = $options['prefix']            ?? '';
        }

        $state = ArrayHelper::getValue($states, (int) $value, $states[0]);

        // Support both named-key arrays and legacy numeric-index arrays.
        $task          = $state['task']           ?? ($state[0]  ?? '');
        $activeTitle   = $state['active_title']   ?? ($state[2]  ?? '');
        $inactiveTitle = $state['inactive_title'] ?? ($state[3]  ?? '');
        $tip           = $state['tip']            ?? ($state[4]  ?? false);
        $activeClass   = $state['active_class']   ?? ($state[5]  ?? '');
        $inactiveClass = $state['inactive_class'] ?? ($state[6]  ?? '');

        return static::action(
            $i,
            $task,
            $prefix,
            $activeTitle,
            $inactiveTitle,
            $tip,
            $activeClass,
            $inactiveClass,
            $enabled,
            $translate,
            $checkbox,
            $formId
        );
    }

    /**
     * Returns a published state toggle button.
     *
     * Handles published (1), unpublished (0), archived (2), and trashed (-2) states.
     *
     * @param   mixed       $value        The current published state value.
     * @param   integer     $i            The row index.
     * @param   string      $prefix       The task prefix.
     * @param   boolean     $enabled      Whether the button should be interactive.
     * @param   string      $checkbox     The checkbox name prefix.
     * @param   string|null $publishUp    The optional publish-up date (unused in rendering).
     * @param   string|null $publishDown  The optional publish-down date (unused in rendering).
     * @param   string|null $formId       The form element ID, if applicable.
     *
     * @return  string  The generated HTML string.
     *
     * @since   1.6
     */
    public static function published(
        $value,
        int $i,
        string $prefix = '',
        bool $enabled = true,
        string $checkbox = 'cb',
        ?string $publishUp = null,
        ?string $publishDown = null,
        ?string $formId = null
    ): string {
        $states = [
            1  => [
                'task'           => 'unpublish',
                'active_title'   => 'JPUBLISHED',
                'inactive_title' => 'JLIB_HTML_UNPUBLISH_ITEM',
                'tip'            => true,
                'active_class'   => 'publish',
                'inactive_class' => 'publish',
            ],
            0  => [
                'task'           => 'publish',
                'active_title'   => 'JUNPUBLISHED',
                'inactive_title' => 'JLIB_HTML_PUBLISH_ITEM',
                'tip'            => true,
                'active_class'   => 'unpublish',
                'inactive_class' => 'unpublish',
            ],
            2  => [
                'task'           => 'unpublish',
                'active_title'   => 'JARCHIVED',
                'inactive_title' => 'JLIB_HTML_UNPUBLISH_ITEM',
                'tip'            => true,
                'active_class'   => 'archive',
                'inactive_class' => 'archive',
            ],
            -2 => [
                'task'           => 'publish',
                'active_title'   => 'JTRASHED',
                'inactive_title' => 'JLIB_HTML_PUBLISH_ITEM',
                'tip'            => true,
                'active_class'   => 'trash',
                'inactive_class' => 'trash',
            ],
        ];

        return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox, $formId);
    }

    /**
     * Returns a default/featured state toggle button.
     *
     * @param   mixed       $value          The current default state (0 or 1).
     * @param   integer     $i              The row index.
     * @param   string      $prefix         The task prefix.
     * @param   boolean     $enabled        Whether the button should be interactive.
     * @param   string      $checkbox       The checkbox name prefix.
     * @param   string|null $formId         The form element ID, if applicable.
     * @param   string      $active_class   The icon class for the active (default) state.
     * @param   string      $inactive_class The icon class for the inactive (non-default) state.
     *
     * @return  string  The generated HTML string.
     *
     * @since   1.6
     */
    public static function isdefault(
        $value,
        int $i,
        string $prefix = '',
        bool $enabled = true,
        string $checkbox = 'cb',
        ?string $formId = null,
        string $active_class = 'icon-color-featured icon-star',
        string $inactive_class = 'icon-unfeatured'
    ): string {
        $states = [
            0 => [
                'task'           => 'setDefault',
                'active_title'   => '',
                'inactive_title' => 'JLIB_HTML_SETDEFAULT_ITEM',
                'tip'            => true,
                'active_class'   => $inactive_class,
                'inactive_class' => $inactive_class,
            ],
            1 => [
                'task'           => 'unsetDefault',
                'active_title'   => 'JDEFAULT',
                'inactive_title' => 'JLIB_HTML_UNSETDEFAULT_ITEM',
                'tip'            => true,
                'active_class'   => $active_class,
                'inactive_class' => $active_class,
            ],
        ];

        return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox, $formId);
    }

    /**
     * Returns an order-up arrow button for reordering rows.
     *
     * @param   integer     $i        The row index.
     * @param   string      $task     The task name (default: 'orderup').
     * @param   string      $prefix   The task prefix.
     * @param   string      $text     The button label language key.
     * @param   boolean     $enabled  Whether the button should be interactive.
     * @param   string      $checkbox The checkbox name prefix.
     * @param   string|null $formId   The form element ID, if applicable.
     *
     * @return  string  The generated HTML string.
     *
     * @since   1.6
     */
    public static function orderUp(
        int $i,
        string $task = 'orderup',
        string $prefix = '',
        string $text = 'JLIB_HTML_MOVE_UP',
        bool $enabled = true,
        string $checkbox = 'cb',
        ?string $formId = null
    ): string {
        return static::action(
            $i,
            $task,
            $prefix,
            $text,
            $text,
            false,
            'uparrow',
            'uparrow_disabled',
            $enabled,
            true,
            $checkbox,
            $formId
        );
    }

    /**
     * Returns an order-down arrow button for reordering rows.
     *
     * @param   integer     $i        The row index.
     * @param   string      $task     The task name (default: 'orderdown').
     * @param   string      $prefix   The task prefix.
     * @param   string      $text     The button label language key.
     * @param   boolean     $enabled  Whether the button should be interactive.
     * @param   string      $checkbox The checkbox name prefix.
     * @param   string|null $formId   The form element ID, if applicable.
     *
     * @return  string  The generated HTML string.
     *
     * @since   1.6
     */
    public static function orderDown(
        int $i,
        string $task = 'orderdown',
        string $prefix = '',
        string $text = 'JLIB_HTML_MOVE_DOWN',
        bool $enabled = true,
        string $checkbox = 'cb',
        ?string $formId = null
    ): string {
        return static::action(
            $i,
            $task,
            $prefix,
            $text,
            $text,
            false,
            'downarrow',
            'downarrow_disabled',
            $enabled,
            true,
            $checkbox,
            $formId
        );
    }
}