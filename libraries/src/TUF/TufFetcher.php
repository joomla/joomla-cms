<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Tuf as MetadataTable;
use Joomla\Database\DatabaseDriver;
use Tuf\Client\Updater;
use Tuf\Exception\Attack\FreezeAttackException;
use Tuf\Exception\Attack\RollbackAttackException;
use Tuf\Exception\Attack\SignatureThresholdException;
use Tuf\Exception\DownloadSizeException;
use Tuf\Exception\MetadataException;
use Tuf\Loader\SizeCheckingLoader;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since  __DEPLOY_VERSION__
 */
class TufFetcher
{
    /**
     * The table object holding the metadata
     *
     * @var MetadataTable
     */
    private MetadataTable $metadataTable;

    /**
     * The repository base url
     *
     * @var mixed
     */
    private string $repositoryUrl;

    /**
     * The database driver
     *
     * @var DatabaseDriver
     */
    protected DatabaseDriver $db;

    /**
     * Validating updates with TUF
     *
     * @param MetadataTable $metadataTable  The table object holding the metadata
     * @param string        $repositoryUrl  The base repo URL
     */
    public function __construct(MetadataTable $metadataTable, string $repositoryUrl, DatabaseDriver $db = null)
    {
        $this->metadataTable = $metadataTable;
        $this->repositoryUrl = $repositoryUrl;
        $this->db = $db ?? Factory::getContainer()->get(DatabaseDriver::class);
    }


    /**
     * Checks for updates and writes it into the database if they are valid. Then it gets the targets.json content and
     * returns it
     *
     * @return mixed Returns the targets.json if the validation is successful, otherwise null
     */
    public function getValidUpdate()
    {
        $httpLoader = new HttpLoader($this->repositoryUrl);
        $sizeCheckingLoader = new SizeCheckingLoader($httpLoader);

        $storage = new DatabaseStorage($this->metadataTable);

        $updater = new Updater(
            $sizeCheckingLoader,
            $storage
        );

        $app = Factory::getApplication();

        try {
            try {
                // Refresh the data if needed, it will be written inside the DB, then we fetch it afterwards and return it to
                // the caller
                $updater->refresh();

                // Persist the data as it was correctly fetched and verified
                $storage->persist();

                return $storage->read('targets');
            } catch (\Exception $e) {
                if (JDEBUG && $message = $e->getMessage()) {
                    $app->enqueueMessage(Text::sprintf('JLIB_INSTALLER_TUF_DEBUG_MESSAGE', $message), 'error');
                }
                throw $e;
            }
        } catch (DownloadSizeException $e) {
            $app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_DOWNLOAD_SIZE'), 'error');
        } catch (MetadataException $e) {
            $app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_INVALID_METADATA'), 'error');
        } catch (FreezeAttackException $e) {
            $app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_FREEZE_ATTACK'), 'error');
        } catch (RollbackAttackException $e) {
            $app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_ROLLBACK_ATTACK'), 'error');
        } catch (SignatureThresholdException $e) {
            $app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_SIGNATURE_THRESHOLD'), 'error');
        }

        $this->rollBackTufMetadata();
    }

    /**
     * When the validation fails, for example when one file is written but the others don't, we roll back everything
     *
     * @return void
     */
    private function rollBackTufMetadata()
    {
        $db = $this->db;

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__tuf_metadata'))
            ->set($db->quoteName('snapshot') . ' = NULL')
            ->set($db->quoteName('targets') . ' = NULL')
            ->set($db->quoteName('timestamp') . ' = NULL');

        $db->setQuery($query)->execute();
    }
}
