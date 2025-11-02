<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.Finder
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Extension\Finder\Extension;

use Joomla\CMS\Event\Extension\AbstractExtensionEvent;
use Joomla\CMS\Event\Extension\AfterInstallEvent;
use Joomla\CMS\Event\Extension\AfterUninstallEvent;
use Joomla\CMS\Event\Extension\AfterUpdateEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finder extension plugin
 *
 * @since  4.0.0
 */
final class Finder extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onExtensionAfterInstall'   => 'onExtensionAfterInstall',
            'onExtensionAfterUpdate'    => 'onExtensionAfterUpdate',
            'onExtensionAfterUninstall' => 'onExtensionAfterUninstall',
        ];
    }

    /**
     * Add common words to finder after language got installed
     *
     * @param   AfterInstallEvent $event  Event instance.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterInstall(AbstractExtensionEvent $event): void
    {
        $eid = $event->getEid();

        if (!$eid) {
            return;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['element', 'client_id']))
            ->from($db->quoteName('#__extensions'))
            ->where(
                [
                    $db->quoteName('extension_id') . ' = :eid',
                    $db->quoteName('type') . ' = ' . $db->quote('language'),
                ]
            )
            ->bind(':eid', $eid, ParameterType::INTEGER);

        $extension = $db->setQuery($query)->loadObject();

        if ($extension) {
            $this->addCommonWords($extension);
        }
    }

    /**
     * Add common words to finder after language got updated
     *
     * @param   AfterUpdateEvent $event  Event instance.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterUpdate(AfterUpdateEvent $event): void
    {
        $this->onExtensionAfterInstall($event);
    }

    /**
     * Remove common words to finder after language got uninstalled
     *
     * @param   AfterUninstallEvent $event  Event instance.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterUninstall(AfterUninstallEvent $event): void
    {
        $installer = $event->getInstaller();
        $eid       = $event->getEid();
        $removed   = $event->getRemoved();

        // Check that the language was successfully uninstalled.
        if ($eid && $removed && $installer->extension->type === 'language') {
            $this->removeCommonWords($installer->extension);
        }
    }

    /**
     * Add common words from a txt file to com_finder
     *
     * @param   object  $extension  Extension object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function addCommonWords($extension)
    {
        if ($extension->client_id == 0) {
            $path = JPATH_SITE . '/language/' . $extension->element . '/com_finder.commonwords.txt';
        } else {
            $path = JPATH_ADMINISTRATOR . '/language/' . $extension->element . '/com_finder.commonwords.txt';
        }

        if (!file_exists($path)) {
            return;
        }

        $this->removeCommonWords($extension);

        $file_content = file_get_contents($path);
        $words        = explode("\n", $file_content);
        $words        = array_map(
            function ($word) {
                // Remove comments
                if (StringHelper::strpos($word, ';') !== false) {
                    $word = StringHelper::substr($word, 0, StringHelper::strpos($word, ';'));
                }

                return $word;
            },
            $words
        );

        $words = array_filter(array_map('trim', $words));
        $words = array_unique($words);
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $lang = Helper::getPrimaryLanguage($extension->element);

        $query->insert($db->quoteName('#__finder_terms_common'))
            ->columns($db->quoteName(['term', 'language', 'custom']));

        foreach ($words as $word) {
            $bindNames = $query->bindArray([$word, $lang], ParameterType::STRING);

            $query->values(implode(',', $bindNames) . ', 0');
        }

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (\Exception) {
            // It would be nice if the common word is stored to the DB, but it isn't super important
        }
    }

    /**
     * Remove common words of a language from com_finder
     *
     * @param   object  $extension  Extension object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function removeCommonWords($extension)
    {
        $db   = $this->getDatabase();
        $lang = Helper::getPrimaryLanguage($extension->element);

        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__finder_terms_common'))
            ->where(
                [
                    $db->quoteName('language') . ' = :lang',
                    $db->quoteName('custom') . ' = 0',
                ]
            )
            ->bind(':lang', $lang);

        $db->setQuery($query);
        $db->execute();
    }
}
