<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Behaviour.versionable
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Behaviour\Versionable\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Table\AfterStoreEvent;
use Joomla\CMS\Event\Table\BeforeDeleteEvent;
use Joomla\CMS\Helper\CMSHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\CMS\Versioning\Versioning;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Filter\InputFilter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Implements the Versionable behaviour which allows extensions to automatically support content history for their content items.
 *
 * This plugin supersedes JTableObserverContenthistory.
 *
 * @since  4.0.0
 */
final class Versionable extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTableAfterStore'   => 'onTableAfterStore',
            'onTableBeforeDelete' => 'onTableBeforeDelete',
        ];
    }

    /**
     * The input filter
     *
     * @var    InputFilter
     * @since  4.2.0
     */
    private $filter;

    /**
     * The CMS helper
     *
     * @var    CMSHelper
     * @since  4.2.0
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface   $dispatcher   The dispatcher
     * @param   array                 $config       An optional associative array of configuration settings
     * @param   InputFilter           $filter       The input filter
     * @param   CMSHelper             $helper       The CMS helper
     *
     * @since   4.0.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, InputFilter $filter, CMSHelper $helper)
    {
        parent::__construct($dispatcher, $config);

        $this->filter = $filter;
        $this->helper = $helper;
    }

    /**
     * Post-processor for $table->store($updateNulls)
     *
     * @param   AfterStoreEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableAfterStore(AfterStoreEvent $event)
    {
        // Extract arguments
        /** @var VersionableTableInterface $table */
        $table  = $event['subject'];
        $result = $event['result'];

        if (!$result) {
            return;
        }

        if (!(is_object($table) && $table instanceof VersionableTableInterface)) {
            return;
        }

        // Get the Tags helper and assign the parsed alias
        $typeAlias  = $table->getTypeAlias();
        $aliasParts = explode('.', $typeAlias);

        if ($aliasParts[0] === '' || !ComponentHelper::getParams($aliasParts[0])->get('save_history', 0)) {
            return;
        }

        $id     = $table->getId();
        $data   = $this->helper->getDataObject($table);
        $input  = $this->getApplication()->input;
        $jform  = $input->get('jform', [], 'array');
        $versionNote = '';

        if (isset($jform['version_note'])) {
            $versionNote = $this->filter->clean($jform['version_note'], 'string');
        }

        Versioning::store($typeAlias, $id, $data, $versionNote);
    }

    /**
     * Pre-processor for $table->delete($pk)
     *
     * @param   BeforeDeleteEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableBeforeDelete(BeforeDeleteEvent $event)
    {
        // Extract arguments
        /** @var VersionableTableInterface $table */
        $table = $event['subject'];

        if (!(is_object($table) && $table instanceof VersionableTableInterface)) {
            return;
        }

        $typeAlias  = $table->getTypeAlias();
        $aliasParts = explode('.', $typeAlias);

        if ($aliasParts[0] && ComponentHelper::getParams($aliasParts[0])->get('save_history', 0)) {
            Versioning::delete($typeAlias, $table->getId());
        }
    }
}
