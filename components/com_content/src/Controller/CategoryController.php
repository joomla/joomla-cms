<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Controller;

use Joomla\CMS\Access\Access;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content category frontend controller.
 *
     * @since   __DEPLOY_VERSION__
 */
class CategoryController extends FormController
{
    /**
     * Proxy for getModel to ensure the correct model is returned.
     *
     * @param   string  $name    The model name.
     * @param   string  $prefix  The class prefix.
     * @param   array   $config  Configuration array.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getModel($name = 'CategoryForm', $prefix = 'Site', $config = [])
    {
        $config['ignore_request'] = false;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * The URL view item variable.
     *
     * @var    string
     * @since   __DEPLOY_VERSION__
     */
    protected $view_item = 'categoryform';

    /**
     * The URL view list variable.
     *
     * @var    string
     * @since   __DEPLOY_VERSION__
     */
    protected $view_list = 'categories';

    /**
     * The URL edit variable.
     *
     * @var    string
     * @since   __DEPLOY_VERSION__
     */
    protected $urlVar = 'id';

    /**
     * Constructor.
     *
     * @param   array                         $config   An optional associative array of configuration settings.
     * @param   \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null  $factory  The factory.
     * @param   \Joomla\CMS\Application\CMSApplication|null       $app      The application.
     * @param   \Joomla\Input\Input|null                          $input    The input object.
     */
    public function __construct($config = [], $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // Disable save2new/save2copy on frontend category form.
        $this->unregisterTask('save2new');
        $this->unregisterTask('save2copy');
    }

    /**
     * Override save to ensure the record id is pulled from the posted form.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key.
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        $data = $this->input->post->get('jform', [], 'array');

        $data['id'] = isset($data['id']) ? (int) $data['id'] : 0;

        // Ensure the id is available as request variable for FormController.
        $this->input->set('id', (int) $data['id']);
        $this->input->set('layout', 'edit');
        $this->input->set('view', $this->view_item);

        return parent::save($key, $urlVar);
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   6.1.0
     */
    protected function allowAdd($data = [])
    {
        $user      = $this->app->getIdentity();
        $parentId  = ArrayHelper::getValue($data, 'parent_id', $this->input->getInt('parent_id'), 'int');
        $assetName = $parentId ? 'com_content.category.' . $parentId : 'com_content';
        $frontendEdit = Access::check($user->id, 'core.edit.frontend', $assetName);

        if ($frontendEdit === false) {
            return false;
        }

        if ($parentId) {
            return $user->authorise('core.create', $assetName);
        }

        return parent::allowAdd($data);
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   6.1.0
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = isset($data[$key]) ? (int) $data[$key] : $this->input->getInt($key);
        $user     = $this->app->getIdentity();
        $asset    = 'com_content.category.' . $recordId;

        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        $frontendEdit = Access::check($user->id, 'core.edit.frontend', $asset);

        if ($frontendEdit === false) {
            return false;
        }

        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        if ($user->authorise('core.edit.own', $asset)) {
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            return (int) $record->created_user_id === (int) $user->id;
        }

        return false;
    }
}
