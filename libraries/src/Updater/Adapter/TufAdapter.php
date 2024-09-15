<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater\Adapter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Tuf as MetadataTable;
use Joomla\CMS\TUF\TufFetcher;
use Joomla\CMS\Updater\ConstraintChecker;
use Joomla\CMS\Updater\UpdateAdapter;
use Joomla\CMS\Updater\Updater;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tuf\Exception\MetadataException;

/**
 * TUF Update Adapter Class
 *
 * @since   5.1.0
 *
 * @internal Currently this class is only used for Joomla! updates and will be extended in the future to support 3rd party updates
 *           Don't extend this class in your own code, it is subject to change without notice.
 */
class TufAdapter extends UpdateAdapter
{
    /**
     * Finds an update.
     *
     * @param array $options Update options.
     *
     * @return  array|boolean  Array containing the array of update sites and array of updates. False on failure
     *
     * @since   5.1.0
     */
    public function findUpdate($options)
    {
        $updates = [];
        $targets = $this->getUpdateTargets($options);

        if ($targets) {
            foreach ($targets as $target) {
                $updateTable                 = Table::getInstance('update');
                $updateTable->update_site_id = $options['update_site_id'];

                $updateTable->bind($target);

                $updates[] = $updateTable;
            }
        }

        return ['update_sites' => [], 'updates' => $updates];
    }

    /**
     * Finds targets.
     *
     * @param array $options Update options.
     *
     * @return  array|boolean  Array containing the array of update sites and array of updates. False on failure
     *
     * @since   5.1.0
     */
    public function getUpdateTargets($options)
    {
        $versions = [];

        /** @var MetadataTable $metadataTable */
        $metadataTable = new MetadataTable($this->db);
        $metadataTable->load(['update_site_id' => $options['update_site_id']]);

        $tufFetcher = new TufFetcher($metadataTable, $options['location'], $this->db, (new HttpFactory())->getHttp(), Factory::getApplication());
        $metaData   = $tufFetcher->getValidUpdate();

        $metaData = json_decode((string) $metaData, true);

        if (!isset($metaData['signed']['targets'])) {
            return false;
        }

        foreach ($metaData['signed']['targets'] as $filename => $target) {
            $version = $this->processTufTarget($filename, $target);

            if (!$version) {
                continue;
            }

            $version['detailsurl'] = $options['location'];

            $versions[] = $version;
        }

        // We only want the latest version we support
        usort($versions, function ($a, $b) {
            return version_compare($b['version'], $a['version']);
        });

        $constraintChecker = new ConstraintChecker();

        foreach ($versions as $version) {
            // Return the version as a match if either all constraints are matched
            // or "only" env related constraints fail - the later one is the existing behavior of the XML updater
            if (
                $constraintChecker->check($version, $options['minimum_stability'] ?? Updater::STABILITY_STABLE) === true
                || !empty((array) $constraintChecker->getFailedEnvironmentConstraints())
            ) {
                return [$version];
            }
        }

        return false;
    }

    protected function processTufTarget(string $filename, array $target): bool|array
    {
        $resolver = new OptionsResolver();

        try {
            $this->configureUpdateOptions($resolver);
            $customKeys = $resolver->getDefinedOptions();
        } catch (\Exception $e) {
            return false;
        }

        $values = [];

        if (!isset($target["hashes"])) {
            throw new MetadataException("No trusted hashes are available for '$filename'");
        }

        foreach ($customKeys as $key) {
            if (isset($target["custom"][$key])) {
                $values[$key] = $target["custom"][$key];
            }
        }

        if (isset($values['client']) && \is_string($values['client'])) {
            $client = ApplicationHelper::getClientInfo($values['client'], true);

            if (\is_object($client)) {
                $values['client_id'] = $client->id;
            }

            unset($values['client']);
        }

        if (isset($values['infourl']['url'])) {
            $values['infourl'] = $values['infourl']['url'];
        }

        try {
            $values = $resolver->resolve($values);
        } catch (\Exception $e) {
            return false;
        }

        return $values;
    }

    /**
     * Configures default values or pass arguments to params
     *
     * @param OptionsResolver $resolver The OptionsResolver for the params
     *
     * @return void
     *
     * @since  5.1.0
     */
    protected function configureUpdateOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'name'                => null,
                'description'         => '',
                'element'             => '',
                'type'                => null,
                'client_id'           => 0,
                'version'             => "1",
                'data'                => '',
                'detailsurl'          => '',
                'infourl'             => '',
                'downloads'           => [],
                'targetplatform'      => new \StdClass(),
                'php_minimum'         => null,
                'channel'             => null,
                'supported_databases' => new \StdClass(),
                'stability'           => '',
            ]
        )
            ->setAllowedTypes('version', 'string')
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('element', 'string')
            ->setAllowedTypes('data', 'string')
            ->setAllowedTypes('description', 'string')
            ->setAllowedTypes('type', 'string')
            ->setAllowedTypes('detailsurl', 'string')
            ->setAllowedTypes('infourl', 'string')
            ->setAllowedTypes('client_id', 'int')
            ->setAllowedTypes('downloads', 'array')
            ->setAllowedTypes('targetplatform', 'array')
            ->setAllowedTypes('php_minimum', 'string')
            ->setAllowedTypes('channel', 'string')
            ->setAllowedTypes('supported_databases', 'array')
            ->setAllowedTypes('stability', 'string')
            ->setRequired(['version']);
    }
}
