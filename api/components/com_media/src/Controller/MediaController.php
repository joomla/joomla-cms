<?php

/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Controller;

use Doctrine\Inflector\InflectorFactory;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Media\Administrator\Exception\FileExistsException;
use Joomla\Component\Media\Administrator\Exception\InvalidPathException;
use Joomla\Component\Media\Administrator\File\TmpFileUpload;
use Joomla\Component\Media\Administrator\Provider\ProviderManagerHelperTrait;
use Joomla\Component\Media\Api\Model\MediumModel;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Tobscure\JsonApi\Exception\InvalidParameterException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media web service controller.
 *
 * @since  4.1.0
 */
class MediaController extends ApiController
{
    use ProviderManagerHelperTrait;

    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.1.0
     */
    protected $contentType = 'media';

    /**
     * Query parameters => model state mappings
     *
     * @var    array
     * @since  4.1.0
     */
    private static $listQueryModelStateMap = [
        'path' => [
            'name' => 'path',
            'type' => 'STRING',
        ],
        'url' => [
            'name' => 'url',
            'type' => 'BOOLEAN',
        ],
        'temp' => [
            'name' => 'temp',
            'type' => 'BOOLEAN',
        ],
        'content' => [
            'name' => 'content',
            'type' => 'BOOLEAN',
        ],
    ];

    /**
     * Item query parameters => model state mappings
     *
     * @var    array
     * @since  4.1.0
     */
    private static $itemQueryModelStateMap = [
        'path' => [
            'name' => 'path',
            'type' => 'STRING',
        ],
        'url' => [
            'name' => 'url',
            'type' => 'BOOLEAN',
        ],
        'temp' => [
            'name' => 'temp',
            'type' => 'BOOLEAN',
        ],
        'content' => [
            'name' => 'content',
            'type' => 'BOOLEAN',
        ],
    ];

    /**
     * The default view for the display method.
     *
     * @var    string
     *
     * @since  4.1.0
     */
    protected $default_view = 'media';

    /**
     * Display a list of files and/or folders.
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   4.1.0
     *
     * @throws  \Exception
     */
    public function displayList()
    {
        // Set list specific request parameters in model state.
        $this->setModelState(self::$listQueryModelStateMap);

        // Display files in specific path.
        if ($this->input->exists('path')) {
            $this->modelState->set('path', $this->input->get('path', '', 'STRING'));
        }

        // Return files (not folders) as urls.
        if ($this->input->exists('url')) {
            $this->modelState->set('url', $this->input->get('url', true, 'BOOLEAN'));
        }

        // Map JSON:API compliant filter[search] to com_media model state.
        $apiFilterInfo = $this->input->get('filter', [], 'array');
        $filter        = InputFilter::getInstance();

        // Search for files matching (part of) a name or glob pattern.
        if (\array_key_exists('search', $apiFilterInfo)) {
            $this->modelState->set('search', $filter->clean($apiFilterInfo['search'], 'STRING'));

            // Tell model to search recursively
            $this->modelState->set('search_recursive', $this->input->get('search_recursive', false, 'BOOLEAN'));
        }

        return parent::displayList();
    }

    /**
     * Display one specific file or folder.
     *
     * @param   string  $path  The path of the file to display. Leave empty if you want to retrieve data from the request.
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   4.1.0
     *
     * @throws  InvalidPathException
     * @throws  \Exception
     */
    public function displayItem($path = '')
    {
        // Set list specific request parameters in model state.
        $this->setModelState(self::$itemQueryModelStateMap);

        // Display files in specific path.
        $this->modelState->set('path', $path ?: $this->input->get('path', '', 'STRING'));

        // Return files (not folders) as urls.
        if ($this->input->exists('url')) {
            $this->modelState->set('url', $this->input->get('url', true, 'BOOLEAN'));
        }

        return parent::displayItem();
    }

    /**
     * Set model state using a list of mappings between query parameters and model state names.
     *
     * @param   array  $mappings  A list of mappings between query parameters and model state names.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    private function setModelState(array $mappings): void
    {
        foreach ($mappings as $queryName => $modelState) {
            if ($this->input->exists($queryName)) {
                $this->modelState->set($modelState['name'], $this->input->get($queryName, '', $modelState['type']));
            }
        }
    }

    /**
     * Method to add a new file or folder.
     *
     * @return  void
     *
     * @since   4.1.0
     *
     * @throws  FileExistsException
     * @throws  InvalidPathException
     * @throws  InvalidParameterException
     * @throws  \RuntimeException
     * @throws  \Exception
     */
    public function add(): void
    {
        $path    = $this->input->json->get('path', '', 'STRING');
        $content = $this->input->json->get('content', '', 'RAW');

        $missingParameters = [];

        if (empty($path)) {
            $missingParameters[] = 'path';
        }

        // Content is only required when it is a file
        if (empty($content) && str_contains($path, '.')) {
            $missingParameters[] = 'content';
        }

        if (\count($missingParameters)) {
            throw new InvalidParameterException(
                Text::sprintf('WEBSERVICE_COM_MEDIA_MISSING_REQUIRED_PARAMETERS', implode(' & ', $missingParameters))
            );
        }

        $this->modelState->set('path', $this->input->json->get('path', '', 'STRING'));

        // Check if an existing file may be overwritten. Defaults to false.
        $this->modelState->set('override', $this->input->json->get('override', false));

        parent::add();
    }

    /**
     * Method to check if it's allowed to add a new file or folder
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   4.1.0
     */
    protected function allowAdd($data = []): bool
    {
        $user = $this->app->getIdentity();

        // Require generic management permissions for the component
        if (!$user->authorise('core.manage', 'com_media')) {
            return false;
        }

        // In the override mode, adding a file will override and therefore edit an existing file
        if ($this->input->json->get('override', false)) {
            return $user->authorise('core.edit', 'com_media');
        }

        return $user->authorise('core.create', 'com_media');
    }

    /**
     * Method to modify an existing file or folder.
     *
     * @return  void
     *
     * @since   4.1.0
     *
     * @throws  FileExistsException
     * @throws  InvalidPathException
     * @throws  \RuntimeException
     * @throws  \Exception
     */
    public function edit(): void
    {
        // Access check.
        if (!$this->allowEdit()) {
            throw new NotAllowed('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED', 403);
        }

        $path    = $this->input->json->get('path', '', 'STRING');
        $content = $this->input->json->get('content', '', 'RAW');

        if (empty($path) && empty($content)) {
            throw new InvalidParameterException(
                Text::sprintf('WEBSERVICE_COM_MEDIA_MISSING_REQUIRED_PARAMETERS', 'path | content')
            );
        }

        $this->modelState->set('path', $this->input->json->get('path', '', 'STRING'));
        // For renaming/moving files, we need the path to the existing file or folder.
        $this->modelState->set('old_path', $this->input->get('path', '', 'STRING'));
        // Check if an existing file may be overwritten. Defaults to true.
        $this->modelState->set('override', $this->input->json->get('override', true));

        $recordId = $this->save();

        $this->displayItem($recordId);
    }

    /**
     * Method to check if it's allowed to modify an existing file or folder.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   4.1.0
     */
    protected function allowEdit($data = [], $key = 'id'): bool
    {
        $user = $this->app->getIdentity();

        // Require generic management permissions for the component
        if (!$user->authorise('core.manage', 'com_media')) {
            return false;
        }

        // com_media's access rules contains no specific update rule.
        return $user->authorise('core.edit', 'com_media');
    }

    /**
     * Method to create or modify a file or folder.
     *
     * @param   integer  $recordKey  The primary key of the item (if exists)
     *
     * @return  string   The path
     *
     * @since   4.1.0
     */
    protected function save($recordKey = null)
    {
        // Explicitly get the single item model name.
        $inflector = InflectorFactory::create()->build();
        $modelName = $this->input->get('model', $inflector->singularize($this->contentType));

        /** @var MediumModel $model */
        $model = $this->getModel($modelName, '', ['ignore_request' => true, 'state' => $this->modelState]);

        $json         = $this->input->json;
        $name         = basename($json->getString('path', ''));
        $mediaContent = base64_decode($json->get('content', '', 'raw'));
        $tmpFile      = '';

        // Create tmp file
        if ($mediaContent) {
            $tmpContent   = $mediaContent;
            $tmpFile      = Path::clean($this->app->get('tmp_path') . '/tmp_upload/' . uniqid('tmp-', true));
            $mediaLength  = \strlen($tmpContent);
            $mediaContent = new TmpFileUpload([
                'name'     => $name,
                'tmp_name' => $tmpFile,
                'size'     => $mediaLength,
                'error'    => 0,
            ]);

            $this->checkContent($mediaLength);

            if (!File::write($tmpFile, $tmpContent)) {
                throw new \Exception(Text::_('JLIB_MEDIA_ERROR_UPLOAD_INPUT'));
            }
        }

        // If there is no content, com_media assumes the path refers to a folder.
        $this->modelState->set('content', $mediaContent);

        $result = $model->save();

        if ($tmpFile) {
            try {
                File::delete($tmpFile);
            } catch (\Exception) {
            }
        }

        return $result;
    }

    /**
     * Performs various checks to see if it is allowed to save the content.
     *
     * @param  integer $fileSize  The size of submitted file
     *
     * @return  void
     *
     * @since   4.1.0
     *
     * @throws  \RuntimeException
     */
    private function checkContent(int $fileSize): void
    {
        $params              = ComponentHelper::getParams('com_media');
        $paramsUploadMaxsize = $params->get('upload_maxsize', 0) * 1024 * 1024;

        if ($paramsUploadMaxsize > 0 && $fileSize > $paramsUploadMaxsize) {
            throw new \RuntimeException(Text::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'), 400);
        }
    }

    /**
     * Method to delete an existing file or folder.
     *
     * @return  void
     *
     * @since   4.1.0
     *
     * @throws  InvalidPathException
     * @throws  \RuntimeException
     * @throws  \Exception
     */
    public function delete($id = null): void
    {
        if (!$this->allowDelete()) {
            throw new NotAllowed('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED', 403);
        }

        $this->modelState->set('path', $this->input->get('path', '', 'STRING'));

        $inflector = InflectorFactory::create()->build();
        $modelName = $this->input->get('model', $inflector->singularize($this->contentType));
        $model     = $this->getModel($modelName, '', ['ignore_request' => true, 'state' => $this->modelState]);

        $model->delete();

        $this->app->setHeader('status', 204);
    }

    /**
     * Method to check if it's allowed to delete an existing file or folder.
     *
     * @return  boolean
     *
     * @since   4.1.0
     */
    protected function allowDelete(): bool
    {
        $user = $this->app->getIdentity();

        // Require generic management permissions for the component
        if (!$user->authorise('core.manage', 'com_media')) {
            return false;
        }

        return $user->authorise('core.delete', 'com_media');
    }
}
