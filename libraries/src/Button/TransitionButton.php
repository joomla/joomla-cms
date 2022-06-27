<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Button;

use Joomla\CMS\Language\Text;

/**
 * The PublishedButton class.
 *
 * @since  4.0.0
 */
class TransitionButton extends ActionButton
{
    /**
     * The layout path to render.
     *
     * @var  string
     *
     * @since  4.0.0
     */
    protected $layout = 'joomla.button.transition-button';

    /**
     * ActionButton constructor.
     *
     * @param   array  $options  The options for all buttons in this group.
     *
     * @since   4.0.0
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->unknownState['icon'] = 'shuffle';
        $this->unknownState['title'] = $options['title'] ?? Text::_('JLIB_HTML_UNKNOWN_STATE');
        $this->unknownState['tip_content'] = $options['tip_content'] ?? $this->unknownState['title'];
    }

    /**
     * Render action button by item value.
     *
     * @param   integer|null  $value        Current value of this item.
     * @param   integer|null  $row          The row number of this item.
     * @param   array         $options      The options to override group options.
     *
     * @return  string  Rendered HTML.
     *
     * @since  4.0.0
     */
    public function render(?int $value = null, ?int $row = null, array $options = []): string
    {
        $default  = $this->unknownState;

        $options['tip_title'] = $options['tip_title'] ?? ($options['title'] ?? $default['title']);

        return parent::render($value, $row, $options);
    }
}
