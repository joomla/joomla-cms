<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AsyncAdminResponseTrait;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The article controller
 *
 * @since  1.6
 */
class ArticleController extends FormController
{
    use VersionableControllerTrait;
    use AsyncAdminResponseTrait;

    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'default_task', 'model_path', and
     *                                          'view_path' (this list is not meant to be comprehensive).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    Input
     *
     * @since   3.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // An article edit form can come from the articles or featured view.
        // Adjust the redirect view on the value of 'return' in the request.
        if ($this->input->get('return') == 'featured') {
            $this->view_list = 'featured';
            $this->view_item = 'article&return=featured';
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since   5.0.0
     */
    public function cancel($key = null)
    {
        $result = parent::cancel($key);

        // When editing in modal then redirect to modalreturn layout
        if ($result && $this->input->get('layout') === 'modal') {
            $id     = $this->input->get('id');
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id)
                . '&layout=modalreturn&from-task=cancel';

            $this->setRedirect(Route::_($return, false));
        }

        return $result;
    }

    /**
     * Function that allows child controller access to model data
     * after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
        if ($this->getTask() === 'save2menu') {
            $editState = [];

            $id = $model->getState('article.id');

            $link = 'index.php?option=com_content&view=article';
            $type = 'component';

            $editState['link']          = $link;
            $editState['title']         = $model->getItem($id)->title;
            $editState['type']          = $type;
            $editState['request']['id'] = $id;

            $this->app->setUserState('com_menus.edit.item', [
                'data' => $editState,
                'type' => $type,
                'link' => $link,
            ]);

            $this->setRedirect(Route::_('index.php?option=com_menus&view=item&client_id=0&menutype=mainmenu&layout=edit', false));
        } elseif ($this->input->get('layout') === 'modal' && $this->task === 'save') {
            // When editing in modal then redirect to modalreturn layout
            $id     = $model->getState('article.id', '');
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id)
                . '&layout=modalreturn&from-task=save';

            $this->setRedirect(Route::_($return, false));
        }
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowAdd($data = [])
    {
        $categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');

        if ($categoryId) {
            // If the category has been passed in the data or URL check it.
            return $this->app->getIdentity()->authorise('core.create', 'com_content.category.' . $categoryId);
        }

        // In the absence of better information, revert to the component permissions.
        return parent::allowAdd();
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = isset($data[$key]) ? (int) $data[$key] : 0;
        $user     = $this->app->getIdentity();

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Check edit on the record asset (explicit or inherited)
        if ($user->authorise('core.edit', 'com_content.article.' . $recordId)) {
            return true;
        }

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', 'com_content.article.' . $recordId)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->id == $record->created_by;
        }

        return false;
    }

    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean   True if successful, false otherwise and internal error is set.
     *
     * @since   1.6
     */
    public function batch($model = null)
    {
        $this->checkToken();

        // Set the model
        /** @var \Joomla\Component\Content\Administrator\Model\ArticleModel $model */
        $model = $this->getModel('Article', 'Administrator', []);

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_content&view=articles' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }

    /**
     * Autosave endpoint for asynchronous edit-form saving.
     *
     * @return  boolean
     *
     * @since   6.1.0
     */
    public function autosave()
    {
        if ($this->isAsyncAutosaveRequest()) {
            $guardDecision = $this->evaluateAutosaveGuard();

            if ($guardDecision !== null) {
                echo new JsonResponse(
                    $this->buildAsyncAdminResponseEnvelope(
                        true,
                        [],
                        null,
                        [],
                        $guardDecision
                    ),
                    null,
                    false,
                    true
                );

                $this->app->close();
            }
        }

        $this->input->post->set('task', 'apply');
        $this->task = 'apply';

        $result = parent::save();

        if ($this->isAsyncAutosaveRequest()) {
            $messages = [];

            foreach ((array) $this->app->getMessageQueue() as $message) {
                if (!isset($message['type'], $message['message'])) {
                    continue;
                }

                $messages[$message['type']][] = $message['message'];
            }

            if (!empty($this->message)) {
                $messageType               = $this->messageType ?: CMSWebApplicationInterface::MSG_MESSAGE;
                $messages[$messageType][] = $this->message;
            }

            echo new JsonResponse(
                $this->buildAsyncAdminResponseEnvelope(
                    $result && $this->messageType !== 'error',
                    $messages,
                    null,
                    [],
                    $this->markAutosaveGuardSaved()
                ),
                null,
                false,
                true
            );

            $this->app->close();
        }

        return $result;
    }

    /**
     * Determine whether current request is an async autosave request.
     *
     * @return  boolean
     *
     * @since   6.1.0
     */
    private function isAsyncAutosaveRequest(): bool
    {
        if (!ComponentHelper::getParams('com_content')->get('autosave_enabled', 0)) {
            return false;
        }

        return strtolower($this->input->server->getString('HTTP_X_REQUESTED_WITH', '')) === 'xmlhttprequest';
    }

    /**
     * Evaluate whether autosave should be skipped to avoid version/history spam.
     *
     * @return  array|null  Meta payload when autosave should be skipped, null when save should continue.
     *
     * @since   6.1.0
     */
    private function evaluateAutosaveGuard(): ?array
    {
        $guardKey = $this->getAutosaveGuardKey();

        if ($guardKey === '') {
            return null;
        }

        $session    = $this->app->getSession();
        $guardState = (array) $session->get($guardKey, []);
        $hash       = $this->buildAutosavePayloadHash();
        $now        = time();

        if ($hash !== '' && ($guardState['hash'] ?? '') === $hash) {
            return [
                'autosave'   => true,
                'autosaveAt' => Factory::getDate()->toSql(),
                'skipped'    => true,
                'reason'     => 'unchanged',
            ];
        }

        $minInterval = max(5, (int) ComponentHelper::getParams('com_content')->get('autosave_interval', 30));
        $lastSavedAt = (int) ($guardState['savedAt'] ?? 0);

        if ($lastSavedAt > 0 && ($now - $lastSavedAt) < $minInterval) {
            return [
                'autosave'   => true,
                'autosaveAt' => Factory::getDate()->toSql(),
                'skipped'    => true,
                'reason'     => 'throttled',
                'retryAfter' => max(0, $minInterval - ($now - $lastSavedAt)),
            ];
        }

        return null;
    }

    /**
     * Persist autosave guard state after a successful save and return autosave meta.
     *
     * @return  array
     *
     * @since   6.1.0
     */
    private function markAutosaveGuardSaved(): array
    {
        $meta = [
            'autosave'   => true,
            'autosaveAt' => Factory::getDate()->toSql(),
        ];

        if (!$this->isAsyncAutosaveRequest()) {
            return $meta;
        }

        $guardKey = $this->getAutosaveGuardKey();

        if ($guardKey === '') {
            return $meta;
        }

        $hash = $this->buildAutosavePayloadHash();

        $this->app->getSession()->set($guardKey, [
            'hash'    => $hash,
            'savedAt' => time(),
        ]);

        return $meta;
    }

    /**
     * Build a stable session key for autosave guard state.
     *
     * @return  string
     *
     * @since   6.1.0
     */
    private function getAutosaveGuardKey(): string
    {
        $articleId = (int) ($this->input->post->get('jform', [], 'array')['id'] ?? 0);

        if ($articleId <= 0) {
            $articleId = (int) $this->input->getInt('id', 0);
        }

        $userId = (int) $this->app->getIdentity()->id;

        if ($userId <= 0) {
            return '';
        }

        return 'com_content.autosave.guard.' . $userId . '.' . max(0, $articleId);
    }

    /**
     * Build a stable hash of autosave payload data.
     *
     * @return  string
     *
     * @since   6.1.0
     */
    private function buildAutosavePayloadHash(): string
    {
        $data = (array) $this->input->post->get('jform', [], 'array');

        if (empty($data)) {
            return '';
        }

        unset($data['asset_id'], $data['version_note']);

        $normalize = static function (&$value) use (&$normalize): void {
            if (is_array($value)) {
                ksort($value);

                foreach ($value as &$childValue) {
                    $normalize($childValue);
                }
            }
        };

        $normalize($data);

        return hash('sha256', json_encode($data));
    }
}
