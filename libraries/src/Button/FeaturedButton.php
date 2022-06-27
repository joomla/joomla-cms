<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Button;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * The FeaturedButton class.
 *
 * @since  4.0.0
 */
class FeaturedButton extends ActionButton
{
    /**
     * Configure this object.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function preprocess()
    {
        $this->addState(
            0,
            'featured',
            'icon-unfeatured',
            Text::_('JGLOBAL_TOGGLE_FEATURED'),
            ['tip_title' => Text::_('JUNFEATURED')]
        );
        $this->addState(
            1,
            'unfeatured',
            'icon-color-featured icon-star',
            Text::_('JGLOBAL_TOGGLE_FEATURED'),
            ['tip_title' => Text::_('JFEATURED')]
        );
    }

    /**
     * Render action button by item value.
     *
     * @param   integer|null  $value         Current value of this item.
     * @param   integer|null  $row           The row number of this item.
     * @param   array         $options       The options to override group options.
     * @param   string|Date   $featuredUp    The date which item featured up.
     * @param   string|Date   $featuredDown  The date which item featured down.
     *
     * @return  string  Rendered HTML.
     *
     * @since  4.0.0
     */
    public function render(?int $value = null, ?int $row = null, array $options = [], $featuredUp = null, $featuredDown = null): string
    {
        if ($featuredUp || $featuredDown) {
            $bakState = $this->getState($value);
            $default  = $this->getState($value) ?? $this->unknownState;

            $nowDate  = Factory::getDate()->toUnix();

            $tz       = Factory::getUser()->getTimezone();

            if (!is_null($featuredUp)) {
                $featuredUp = Factory::getDate($featuredUp, 'UTC')->setTimezone($tz);
            }

            if (!is_null($featuredDown)) {
                $featuredDown = Factory::getDate($featuredDown, 'UTC')->setTimezone($tz);
            }

            // Add tips and special titles
            // Create special titles for featured items
            if ($value === 1) {
                // Create tip text, only we have featured up or down settings
                $tips = [];

                if ($featuredUp) {
                    $tips[] = Text::sprintf('JLIB_HTML_FEATURED_STARTED', HTMLHelper::_('date', $featuredUp, Text::_('DATE_FORMAT_LC5'), 'UTC'));
                }

                if ($featuredDown) {
                    $tips[] = Text::sprintf('JLIB_HTML_FEATURED_FINISHED', HTMLHelper::_('date', $featuredDown, Text::_('DATE_FORMAT_LC5'), 'UTC'));
                }

                $tip = empty($tips) ? false : implode('<br>', $tips);

                $default['title'] = $tip;

                $options['tip_title'] = Text::_('JLIB_HTML_FEATURED_ITEM');

                if ($featuredUp && $nowDate < $featuredUp->toUnix()) {
                    $options['tip_title'] = Text::_('JLIB_HTML_FEATURED_PENDING_ITEM');
                    $default['icon'] = 'pending';
                }

                if ($featuredDown && $nowDate > $featuredDown->toUnix()) {
                    $options['tip_title'] = Text::_('JLIB_HTML_FEATURED_EXPIRED_ITEM');
                    $default['icon'] = 'expired';
                }
            }

            $this->states[$value] = $default;

            $html = parent::render($value, $row, $options);

            $this->states[$value] = $bakState;

            return $html;
        }

        return parent::render($value, $row, $options);
    }
}
