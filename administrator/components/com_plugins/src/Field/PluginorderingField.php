<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\Field;

use Joomla\CMS\Form\Field\OrderingField;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML select list of plugins.
 *
 * @since  1.6
 */
class PluginorderingField extends OrderingField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'Pluginordering';

    /**
     * Builds the query for the ordering list.
     *
     * @return  \Joomla\Database\DatabaseQuery  The query for the ordering form field.
     */
    protected function getQuery()
    {
        $db     = $this->getDatabase();
        $folder = $this->form->getValue('folder');

        // Build the query for the ordering list.
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('ordering', 'value'),
                    $db->quoteName('name', 'text'),
                    $db->quoteName('type'),
                    $db->quote('folder'),
                    $db->quote('extension_id'),
                ]
            )
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = :folder')
            ->order($db->quoteName('ordering'))
            ->bind(':folder', $folder);

        return $query;
    }

    /**
     * Retrieves the current Item's Id.
     *
     * @return  integer  The current item ID.
     */
    protected function getItemId()
    {
        return (int) $this->form->getValue('extension_id');
    }
}
