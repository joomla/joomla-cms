<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\Component\Privacy\Administrator\Export\Domain;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy component helper.
 *
 * @since  3.9.0
 */
class PrivacyHelper extends ContentHelper
{
    /**
     * Render the data request as a XML document.
     *
     * @param   Domain[]  $exportData  The data to be exported.
     *
     * @return  string
     *
     * @since   3.9.0
     */
    public static function renderDataAsXml(array $exportData)
    {
        $export = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><data-export />');

        foreach ($exportData as $domain) {
            $xmlDomain = $export->addChild('domain');
            $xmlDomain->addAttribute('name', $domain->name);
            $xmlDomain->addAttribute('description', $domain->description);

            foreach ($domain->getItems() as $item) {
                $xmlItem = $xmlDomain->addChild('item');

                if ($item->id) {
                    $xmlItem->addAttribute('id', $item->id);
                }

                foreach ($item->getFields() as $field) {
                    $xmlItem->{$field->name} = $field->value;
                }
            }
        }

        $dom = new \DOMDocument();
        $dom->loadXML($export->asXML());
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * Gets the privacyconsent system plugin extension id.
     *
     * @return  integer  The privacyconsent system plugin extension id.
     *
     * @since   3.9.2
     */
    public static function getPrivacyConsentPluginId()
    {
        $db    = Factory::getDbo();
        $query = $db->createQuery()
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('privacyconsent'));

        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
