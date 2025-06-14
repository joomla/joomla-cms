<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Tuf as MetadataTable;
use Joomla\Database\DatabaseInterface;
use Joomla\Http\Http;
use Tuf\Client\Updater;
use Tuf\Exception\Attack\FreezeAttackException;
use Tuf\Exception\Attack\RollbackAttackException;
use Tuf\Exception\Attack\SignatureThresholdException;
use Tuf\Exception\DownloadSizeException;
use Tuf\Exception\MetadataException;
use Tuf\Exception\TufException;
use Tuf\Loader\SizeCheckingLoader;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since  5.1.0
 *
 * @internal Currently this class is only used for Joomla! updates and will be extended in the future to support 3rd party updates
 *           Don't extend this class in your own code, it is subject to change without notice.
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
     * @var DatabaseInterface
     */
    protected DatabaseInterface $db;

    /**
     * The web application object
     *
     * @var CMSApplicationInterface
     */
    protected CMSApplicationInterface $app;

    /**
     * The http client
     *
     * @var Http
     */
    protected Http $httpClient;

    /**
     * Validating updates with TUF
     *
     * @param   MetadataTable            $metadataTable  The table object holding the metadata
     * @param   string                   $repositoryUrl  The repo url
     * @param   DatabaseInterface        $db             The database driver
     * @param   Http                     $httpClient     A client for sending Http requests
     * @param   CMSApplicationInterface  $app            The application object for sending errors to users
     */
    public function __construct(
        MetadataTable $metadataTable,
        string $repositoryUrl,
        DatabaseInterface $db,
        Http $httpClient,
        CMSApplicationInterface $app
    ) {
        $this->metadataTable = $metadataTable;
        $this->repositoryUrl = $repositoryUrl;
        $this->db            = $db;
        $this->httpClient    = $httpClient;
        $this->app           = $app;
    }

    /**
     * Checks for updates and writes it into the database if they are valid. Then it gets the targets.json content and
     * returns it
     *
     * @return mixed Returns the targets.json if the validation is successful, otherwise null
     */
    public function getValidUpdate()
    {
        $httpLoader         = new HttpLoader($this->repositoryUrl, $this->httpClient);
        $sizeCheckingLoader = new SizeCheckingLoader($httpLoader);

        $storage = new DatabaseStorage($this->metadataTable);

        $updater = new Updater(
            $sizeCheckingLoader,
            $storage
        );

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
                    $this->app->enqueueMessage(Text::sprintf('JLIB_INSTALLER_TUF_DEBUG_MESSAGE', $message), CMSApplicationInterface::MSG_ERROR);
                }
                throw $e;
            }
        } catch (DownloadSizeException) {
            $this->app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_DOWNLOAD_SIZE'), CMSApplicationInterface::MSG_ERROR);
        } catch (MetadataException) {
            $this->app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_INVALID_METADATA'), CMSApplicationInterface::MSG_ERROR);
        } catch (FreezeAttackException) {
            $this->app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_FREEZE_ATTACK'), CMSApplicationInterface::MSG_ERROR);
        } catch (RollbackAttackException) {
            $this->app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_ROLLBACK_ATTACK'), CMSApplicationInterface::MSG_ERROR);
        } catch (SignatureThresholdException) {
            $this->app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_SIGNATURE_THRESHOLD'), CMSApplicationInterface::MSG_ERROR);
        } catch (TufException) {
            $this->app->enqueueMessage(Text::_('JLIB_INSTALLER_TUF_ERROR_GENERIC'), CMSApplicationInterface::MSG_ERROR);
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
