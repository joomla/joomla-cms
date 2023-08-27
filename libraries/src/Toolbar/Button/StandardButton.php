<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Renders a standard button
 *
 * @since  3.0
 */
class StandardButton extends BasicButton
{
    /**
     * Property layout.
     *
     * @var  string
     *
     * @since  4.0.0
     */
    protected $layout = 'joomla.toolbar.standard';

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
        parent::prepareOptions($options);

        if (empty($options['is_child'])) {
            $class = $this->fetchButtonClass($this->getName());

            $options['btnClass'] = $options['button_class'] = ($options['button_class'] ?? $class);
        }

        $options['onclick'] = $options['onclick'] ?? $this->_getCommand();
    }

    /**
     * Fetch the HTML for the button
     *
     * @param   string   $type    Unused string.
     * @param   string   $name    The name of the button icon class.
     * @param   string   $text    Button text.
     * @param   string   $task    Task associated with the button.
     * @param   boolean  $list    True to allow lists
     * @param   string   $formId  The id of action form.
     *
     * @return  string  HTML string for the button
     *
     * @since   3.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use render() instead.
     */
    public function fetchButton($type = 'Standard', $name = '', $text = '', $task = '', $list = true, $formId = null)
    {
        $this->name($name)
            ->text($text)
            ->task($task)
            ->listCheck($list);

        if ($formId !== null) {
            $this->form($formId);
        }

        return $this->renderButton($this->options);
    }

    /**
     * Fetch button class for standard buttons.
     *
     * @param   string  $name  The button name.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function fetchButtonClass(string $name): string
    {
        switch ($name) {
            case 'apply':
            case 'new':
            case 'save':
            case 'save-new':
            case 'save-copy':
            case 'save-close':
            case 'publish':
                return 'btn btn-success';

            case 'featured':
                return 'btn btn-warning';

            case 'cancel':
            case 'trash':
            case 'delete':
            case 'unpublish':
                return 'btn btn-danger';

            default:
                return 'btn btn-primary';
        }
    }

    /**
     * Get the JavaScript command for the button
     *
     * @return  string   JavaScript command string
     *
     * @since   3.0
     */
    protected function _getCommand()
    {
        Text::script($this->getListCheckMessage() ?: 'JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
        Text::script('ERROR');

        $cmd = "Joomla.submitbutton('" . $this->getTask() . "');";

        if ($this->getListCheck()) {
            $messages = "{error: [Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
            $alert    = 'Joomla.renderMessages(' . $messages . ')';
            $cmd      = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';
        }

        return $cmd;
    }
}
