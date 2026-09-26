<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.override
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Installer\Override\Extension;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Override Plugin
 *
 * @since  4.0.0
 */
final class Override extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

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
            'onExtensionBeforeUpdate'    => 'onExtensionBeforeUpdate',
            'onExtensionAfterUpdate'     => 'onExtensionAfterUpdate',
            'onJoomlaBeforeUpdate'       => 'onJoomlaBeforeUpdate',
            'onJoomlaAfterUpdate'        => 'onJoomlaAfterUpdate',
            'onInstallerBeforeInstaller' => 'onInstallerBeforeInstaller',
            'onInstallerAfterInstaller'  => 'onInstallerAfterInstaller',
        ];
    }

    /**
     * Method to get com_templates model instance.
     *
     * @param   string  $name    The model name. Optional
     * @param   string  $prefix  The class prefix. Optional
     *
     * @return  \Joomla\Component\Templates\Administrator\Model\TemplateModel
     *
     * @since   4.0.0
     *
     * @throws \Exception
     */
    public function getModel($name = 'Template', $prefix = 'Administrator')
    {
        /** @var \Joomla\Component\Templates\Administrator\Extension\TemplatesComponent $templateProvider */
        $templateProvider = $this->getApplication()->bootComponent('com_templates');

        /** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
        $model = $templateProvider->getMVCFactory()->createModel($name, $prefix);

        return $model;
    }

    /**
     * Purges session array.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function purge()
    {
        // Delete stored session value.
        $session = $this->getApplication()->getSession();
        $session->remove('override.beforeEventFiles');
        $session->remove('override.afterEventFiles');
    }

    /**
     * Method to store files before event.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function storeBeforeEventFiles()
    {
        // Delete stored session value.
        $this->purge();

        // Get list and store in session.
        $list = $this->getOverrideCoreList();
        $this->getApplication()->getSession()->set('override.beforeEventFiles', $list);
    }

    /**
     * Method to store files after event.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function storeAfterEventFiles()
    {
        // Get list and store in session.
        $list = $this->getOverrideCoreList();
        $this->getApplication()->getSession()->set('override.afterEventFiles', $list);
    }

    /**
     * Method to prepare changed or updated core file.
     *
     * @param   string  $action  The name of the action.
     *
     * @return   array   A list of changed files.
     *
     * @since   4.0.0
     */
    public function getUpdatedFiles($action)
    {
        $session = $this->getApplication()->getSession();

        $after  = $session->get('override.afterEventFiles');
        $before = $session->get('override.beforeEventFiles');
        $result = [];

        if (!\is_array($after) || !\is_array($before)) {
            return $result;
        }

        $size1  = \count($after);
        $size2  = \count($before);

        if ($size1 === $size2) {
            for ($i = 0; $i < $size1; $i++) {
                if ($after[$i]->coreFile !== $before[$i]->coreFile) {
                    $after[$i]->action = $action;
                    $result[]          = $after[$i];
                }
            }
        }

        return $result;
    }

    /**
     * Method to get core list of override files.
     *
     * @return   array  The list of core files.
     *
     * @since   4.0.0
     */
    public function getOverrideCoreList()
    {
        try {
            /** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $templateModel */
            $templateModel = $this->getModel();
        } catch (\Exception) {
            return [];
        }

        return $templateModel->getCoreList();
    }

    /**
     * Last process of this plugin.
     *
     * @param   array  $result  Result array.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function finalize($result)
    {
        $num  = \count($result);
        $link = 'index.php?option=com_templates&view=templates';

        if ($num != 0) {
            $this->getApplication()->enqueueMessage(Text::plural('PLG_INSTALLER_OVERRIDE_N_FILE_UPDATED', $num, $link), 'notice');
            $this->saveOverrides($result);
        }

        // Delete stored session value.
        $this->purge();
    }

    /**
     * Event before extension update.
     *
     * @return   void
     *
     * @since   4.0.0
     */
    public function onExtensionBeforeUpdate()
    {
        $this->storeBeforeEventFiles();
    }

    /**
     * Event after extension update.
     *
     * @return   void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterUpdate()
    {
        $this->storeAfterEventFiles();
        $result = $this->getUpdatedFiles('Extension Update');
        $this->finalize($result);
    }

    /**
     * Event before joomla update.
     *
     * @return   void
     *
     * @since   4.0.0
     */
    public function onJoomlaBeforeUpdate()
    {
        $this->storeBeforeEventFiles();
    }

    /**
     * Event after joomla update.
     *
     * @return   void
     *
     * @since   4.0.0
     */
    public function onJoomlaAfterUpdate()
    {
        $this->storeAfterEventFiles();
        $result = $this->getUpdatedFiles('Joomla Update');
        $this->finalize($result);
    }

    /**
     * Event before install.
     *
     * @return   void
     *
     * @since   4.0.0
     */
    public function onInstallerBeforeInstaller()
    {
        $this->storeBeforeEventFiles();
    }

    /**
     * Event after install.
     *
     * @return   void
     *
     * @since   4.0.0
     */
    public function onInstallerAfterInstaller()
    {
        $this->storeAfterEventFiles();
        $result = $this->getUpdatedFiles('Extension Install');
        $this->finalize($result);
    }

    /**
     * Check for existing id.
     *
     * @param   string   $id    Hash id of file.
     * @param   integer  $exid  Extension id of file.
     *
     * @return   boolean  True/False
     *
     * @since   4.0.0
     */
    public function load($id, $exid)
    {
        $db = $this->getDatabase();

        // Create a new query object.
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName('hash_id'))
            ->from($db->quoteName('#__template_overrides'))
            ->where($db->quoteName('hash_id') . ' = :id')
            ->where($db->quoteName('extension_id') . ' = :exid')
            ->bind(':id', $id)
            ->bind(':exid', $exid, ParameterType::INTEGER);

        $db->setQuery($query);
        $results = $db->loadObjectList();

        if (\count($results) === 1) {
            return true;
        }

        return false;
    }

    /**
     * Save the updated files.
     *
     * @param   array  $pks  Updated files.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws   \Joomla\Database\Exception\ExecutionFailureException|\Joomla\Database\Exception\ConnectionFailureException
     */
    private function saveOverrides($pks)
    {
        // Insert columns.
        $columns = [
            'template',
            'hash_id',
            'action',
            'created_date',
            'modified_date',
            'extension_id',
            'state',
            'client_id',
        ];

        $db = $this->getDatabase();

        // Create an insert query.
        $insertQuery = $db->getQuery(true)
            ->insert($db->quoteName('#__template_overrides'))
            ->columns($db->quoteName($columns));

        foreach ($pks as $pk) {
            $date        = new Date('now');
            $createdDate = $date->toSql();

            if (empty($pk->coreFile)) {
                $modifiedDate = null;
            } else {
                $modifiedDate = $createdDate;
            }

            if ($this->load($pk->id, $pk->extension_id)) {
                $updateQuery = $db->getQuery(true)
                    ->update($db->quoteName('#__template_overrides'))
                    ->set(
                        [
                            $db->quoteName('modified_date') . ' = :modifiedDate',
                            $db->quoteName('action') . ' = :pkAction',
                            $db->quoteName('state') . ' = 0',
                        ]
                    )
                    ->where($db->quoteName('hash_id') . ' = :pkId')
                    ->where($db->quoteName('extension_id') . ' = :exId')
                    ->bind(':modifiedDate', $modifiedDate)
                    ->bind(':pkAction', $pk->action)
                    ->bind(':pkId', $pk->id)
                    ->bind(':exId', $pk->extension_id, ParameterType::INTEGER);

                // Set the query using our newly populated query object and execute it.
                $db->setQuery($updateQuery);
                $db->execute();

                continue;
            }

            // Insert values, preserve order
            $bindArray = $insertQuery->bindArray(
                [
                    $pk->template,
                    $pk->id,
                    $pk->action,
                    $createdDate,
                    $modifiedDate,
                ],
                ParameterType::STRING
            );

            $bindArray = array_merge(
                $bindArray,
                $insertQuery->bindArray(
                    [
                        $pk->extension_id,
                        0,
                        (int) $pk->client,
                    ],
                    ParameterType::INTEGER
                )
            );

            $insertQuery->values(implode(',', $bindArray));
        }

        if (!empty($bindArray)) {
            $db->setQuery($insertQuery);
            $db->execute();
        }
    }
}
