<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.override
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\ParameterType;

/**
 * Override Plugin
 *
 * @since  4.0.0
 */
class PlgInstallerOverride extends CMSPlugin
{
    /**
     * Application object.
     *
     * @var    CMSApplicationInterface
     */
    protected $app;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Database object
     *
     * @var    \Joomla\Database\DatabaseInterface
     * @since  4.0.0
     */
    protected $db;

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
        $templateProvider = $this->app->bootComponent('com_templates');

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
        $session = $this->app->getSession();
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
        $this->app->getSession()->set('override.beforeEventFiles', $list);
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
        $this->app->getSession()->set('override.afterEventFiles', $list);
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
        $session = $this->app->getSession();

        $after  = $session->get('override.afterEventFiles');
        $before = $session->get('override.beforeEventFiles');
        $result = array();

        if (!is_array($after) || !is_array($before)) {
            return $result;
        }

        $size1  = count($after);
        $size2  = count($before);

        if ($size1 === $size2) {
            for ($i = 0; $i < $size1; $i++) {
                if ($after[$i]->coreFile !== $before[$i]->coreFile) {
                    $after[$i]->action = $action;
                    $result[] = $after[$i];
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
        } catch (\Exception $e) {
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
        $num = count($result);

        if ($num != 0) {
            $this->app->enqueueMessage(Text::plural('PLG_INSTALLER_OVERRIDE_N_FILE_UPDATED', $num), 'notice');
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
        $db = $this->db;

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

        if (count($results) === 1) {
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

        // Create an insert query.
        $insertQuery = $this->db->getQuery(true)
            ->insert($this->db->quoteName('#__template_overrides'))
            ->columns($this->db->quoteName($columns));

        foreach ($pks as $pk) {
            $date = new Date('now');
            $createdDate = $date->toSql();

            if (empty($pk->coreFile)) {
                $modifiedDate = null;
            } else {
                $modifiedDate = $createdDate;
            }

            if ($this->load($pk->id, $pk->extension_id)) {
                $updateQuery = $this->db->getQuery(true)
                    ->update($this->db->quoteName('#__template_overrides'))
                    ->set(
                        [
                            $this->db->quoteName('modified_date') . ' = :modifiedDate',
                            $this->db->quoteName('action') . ' = :pkAction',
                            $this->db->quoteName('state') . ' = 0',
                        ]
                    )
                    ->where($this->db->quoteName('hash_id') . ' = :pkId')
                    ->where($this->db->quoteName('extension_id') . ' = :exId')
                    ->bind(':modifiedDate', $modifiedDate)
                    ->bind(':pkAction', $pk->action)
                    ->bind(':pkId', $pk->id)
                    ->bind(':exId', $pk->extension_id, ParameterType::INTEGER);

                // Set the query using our newly populated query object and execute it.
                $this->db->setQuery($updateQuery);
                $this->db->execute();

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
            $this->db->setQuery($insertQuery);
            $this->db->execute();
        }
    }
}
