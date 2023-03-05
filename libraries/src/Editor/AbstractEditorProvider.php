<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor;

use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract editor provider
 *
 * @since   __DEPLOY_VERSION__
 */
abstract class AbstractEditorProvider implements EditorProviderInterface
{
    /**
     * Displays the editor buttons.
     *
     * @param   string  $name     Button name.
     * @param   mixed   $buttons  Array with button names. Empty array or boolean true to display all buttons.
     * @param   array   $params   Associative array with additional parameters
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function displayButtons(string $name, $buttons, array $params = [])
    {
        if ($buttons === false) {
            return '';
        }

        $helper = Editor::getInstance($this->getName());
        $list   = $helper->getButtons($name, $buttons);

        return LayoutHelper::render('joomla.editors.buttons', $list);
    }
}
