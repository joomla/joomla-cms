<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Renders a standard button with a confirm dialog
 *
 * @method self message(string $value)
 * @method bool getMessage()
 *
 * @since  3.0
 */
class ConfirmButton extends StandardButton
{
    /**
     * Prepare options for this button.
     *
     * @param   array  $options  The options about this button.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function prepareOptions(array &$options)
    {
        $options['message'] = Text::_($options['message'] ?? '');

        parent::prepareOptions($options);
    }

    /**
     * Fetch the HTML for the button
     *
     * @param   string   $type      Unused string.
     * @param   string   $msg       Message to render
     * @param   string   $name      Name to be used as apart of the id
     * @param   string   $text      Button text
     * @param   string   $task      The task associated with the button
     * @param   boolean  $list      True to allow use of lists
     * @param   boolean  $hideMenu  True to hide the menu on click
     *
     * @return  string   HTML string for the button
     *
     * @since   3.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use render() instead.
     */
    public function fetchButton($type = 'Confirm', $msg = '', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
    {
        $this->name($name)
            ->text($text)
            ->listCheck($list)
            ->message($msg)
            ->task($task);

        return $this->renderButton($this->options);
    }

    /**
     * Get the JavaScript command for the button
     *
     * @return  string  JavaScript command string
     *
     * @since   3.0
     */
    protected function _getCommand()
    {
        Text::script($this->getListCheckMessage() ?: 'JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
        Text::script('ERROR');

        $msg = $this->getMessage();

        $cmd = "if (confirm('" . $msg . "')) { Joomla.submitbutton('" . $this->getTask() . "'); }";

        if ($this->getListCheck()) {
            $message = "{'error': [Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
            $alert   = 'Joomla.renderMessages(' . $message . ')';
            $cmd     = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';
        }

        return $cmd;
    }

    /**
     * Method to configure available option accessors.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected static function getAccessors(): array
    {
        return array_merge(
            parent::getAccessors(),
            [
                'message',
            ]
        );
    }
}
