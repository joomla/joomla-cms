<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Service\Html;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Banner HTML class.
 *
 * @since  2.5
 */
class Banner
{
    use DatabaseAwareTrait;

    /**
     * Display a batch widget for the client selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   2.5
     */
    public function clients()
    {
        // Create the batch selector to change the client on a selection list.
        return implode(
            "\n",
            [
                '<label id="batch-client-lbl" for="batch-client-id">',
                Text::_('COM_BANNERS_BATCH_CLIENT_LABEL'),
                '</label>',
                '<select class="form-select" name="batch[client_id]" id="batch-client-id">',
                '<option value="">' . Text::_('COM_BANNERS_BATCH_CLIENT_NOCHANGE') . '</option>',
                '<option value="0">' . Text::_('COM_BANNERS_NO_CLIENT') . '</option>',
                HTMLHelper::_('select.options', static::clientlist(), 'value', 'text'),
                '</select>',
            ]
        );
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   1.6
     */
    public function clientlist()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id', 'value'),
                    $db->quoteName('name', 'text'),
                ]
            )
            ->from($db->quoteName('#__banner_clients'))
            ->order($db->quoteName('name'));

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $options;
    }

    /**
     * Returns a pinned state on a grid
     *
     * @param   integer  $value     The state value.
     * @param   integer  $i         The row index
     * @param   boolean  $enabled   An optional setting for access control on the action.
     * @param   string   $checkbox  An optional prefix for checkboxes.
     *
     * @return  string   The Html code
     *
     * @see     HTMLHelperJGrid::state
     * @since   2.5.5
     */
    public function pinned($value, $i, $enabled = true, $checkbox = 'cb')
    {
        $states = [
            1 => [
                'sticky_unpublish',
                'COM_BANNERS_BANNERS_PINNED',
                'COM_BANNERS_BANNERS_HTML_UNPIN_BANNER',
                'COM_BANNERS_BANNERS_PINNED',
                true,
                'publish',
                'publish',
            ],
            0 => [
                'sticky_publish',
                'COM_BANNERS_BANNERS_UNPINNED',
                'COM_BANNERS_BANNERS_HTML_PIN_BANNER',
                'COM_BANNERS_BANNERS_UNPINNED',
                true,
                'unpublish',
                'unpublish',
            ],
        ];

        return HTMLHelper::_('jgrid.state', $states, $value, $i, 'banners.', $enabled, true, $checkbox);
    }
}
