<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;

/**
 * Banners component helper.
 *
 * @since  1.6
 */
class BannersHelper extends ContentHelper
{
    /**
     * Update / reset the banners
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public static function updateReset()
    {
        $db   = Factory::getDbo();
        $date = Factory::getDate();
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__banners'))
            ->where(
                [
                    $db->quoteName('reset') . ' <= :date',
                    $db->quoteName('reset') . ' IS NOT NULL',
                ]
            )
            ->bind(':date', $date)
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('checked_out') . ' IS NULL',
                    $db->quoteName('checked_out') . ' = :userId',
                ],
                'OR'
            )
            ->bind(':userId', $user->id, ParameterType::INTEGER);

        $db->setQuery($query);

        try {
            $rows = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        foreach ($rows as $row) {
            $purchaseType = $row->purchase_type;

            if ($purchaseType < 0 && $row->cid) {
                /** @var \Joomla\Component\Banners\Administrator\Table\ClientTable $client */
                $client = Table::getInstance('ClientTable', '\\Joomla\\Component\\Banners\\Administrator\\Table\\');
                $client->load($row->cid);
                $purchaseType = $client->purchase_type;
            }

            if ($purchaseType < 0) {
                $params = ComponentHelper::getParams('com_banners');
                $purchaseType = $params->get('purchase_type');
            }

            switch ($purchaseType) {
                case 1:
                    $reset = null;
                    break;
                case 2:
                    $date = Factory::getDate('+1 year ' . date('Y-m-d'));
                    $reset = $date->toSql();
                    break;
                case 3:
                    $date = Factory::getDate('+1 month ' . date('Y-m-d'));
                    $reset = $date->toSql();
                    break;
                case 4:
                    $date = Factory::getDate('+7 day ' . date('Y-m-d'));
                    $reset = $date->toSql();
                    break;
                case 5:
                    $date = Factory::getDate('+1 day ' . date('Y-m-d'));
                    $reset = $date->toSql();
                    break;
            }

            // Update the row ordering field.
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__banners'))
                ->set(
                    [
                        $db->quoteName('reset') . ' = :reset',
                        $db->quoteName('impmade') . ' = 0',
                        $db->quoteName('clicks') . ' = 0',
                    ]
                )
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':reset', $reset, $reset === null ? ParameterType::NULL : ParameterType::STRING)
                ->bind(':id', $row->id, ParameterType::INTEGER);

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                $app->enqueueMessage($e->getMessage(), 'error');

                return false;
            }
        }

        return true;
    }

    /**
     * Get client list in text/value format for a select field
     *
     * @return  array
     */
    public static function getClientOptions()
    {
        $options = array();

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id', 'value'),
                    $db->quoteName('name', 'text'),
                ]
            )
            ->from($db->quoteName('#__banner_clients', 'a'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->order($db->quoteName('a.name'));

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('COM_BANNERS_NO_CLIENT')));

        return $options;
    }
}
