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
use Joomla\Database\DatabaseDriver;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tuf\Client\Updater;
use Tuf\Exception\Attack\FreezeAttackException;
use Tuf\Exception\Attack\RollbackAttackException;
use Tuf\Exception\Attack\SignatureThresholdException;
use Tuf\Exception\DownloadSizeException;
use Tuf\Exception\MetadataException;
use Tuf\Loader\SizeCheckingLoader;

\defined('JPATH_PLATFORM') or die;

/**
 * @since  __DEPLOY_VERSION__
 */
class TufFetcher
{
    /**
     * The id of the extension to be updated
     *
     * @var integer
     */
    private $extensionId;

    /**
     * The params of the validator
     *
     * @var mixed
     */
    private $params;

    /**
     * The database driver
     *
     * @var DatabaseDriver
     */
    protected $db;

    /**
     * Validating updates with TUF
     *
     * @param integer $extensionId The ID of the extension to be checked
     * @param mixed $params The parameters containing the Base-URI, the Metadata- and Targets-Path and mirrors for the update
     */
    public function __construct(int $extensionId, $params = [], DatabaseDriver $db = null)
    {
        $this->extensionId = $extensionId;
        $this->db = $db ?? Factory::getContainer()->get(DatabaseDriver::class);

        $resolver = new OptionsResolver;

        try {
            $this->configureTufOptions($resolver);
        } catch (\Exception $e) {
        }

        try {
            $params = $resolver->resolve($params);
        } catch (\Exception $e) {
            if ($e instanceof UndefinedOptionsException || $e instanceof InvalidOptionsException) {
                throw $e;
            }
        }

        $this->params = $params;
    }

    /**
     * Configures default values or pass arguments to params
     *
     * @param OptionsResolver $resolver The OptionsResolver for the params
     * @return void
     */
    protected function configureTufOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'location' => ''
            ]
        )
            ->setAllowedTypes('location', 'string')
            ->setRequired('location');
    }

    /**
     * Checks for updates and writes it into the database if they are valid. Then it gets the targets.json content and
     * returns it
     *
     * @return mixed Returns the targets.json if the validation is successful, otherwise null
     */
    public function getValidUpdate()
    {
        $httpLoader = new HttpLoader($this->params['location']);
        $sizeCheckingLoader = new SizeCheckingLoader($httpLoader);

        $storage = new DatabaseStorage($this->db, $this->extensionId);

        $updater = new Updater(
            $sizeCheckingLoader,
            $storage
        );

        $app = Factory::getApplication();

        try {
            try
            {
                // Refresh the data if needed, it will be written inside the DB, then we fetch it afterwards and return it to
                // the caller
                $updater->refresh();

                // Persist the data as it was correctly fetched and verified
                $storage->persist();

                return $storage->read('targets');
            } catch (\Exception $e) {
                if (JDEBUG && $message = $e->getMessage()) {
                    Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_INSTALLER_TUF_DEBUG_MESSAGE', $message), 'error');
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
            ->delete($db->quoteName('#__tuf_metadata'))
            ->columns(['snapshot_json', 'targets_json', 'timestamp_json']);

        $db->setQuery($query)->execute();
    }
}
