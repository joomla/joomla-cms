<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides a modal media selector including upload mechanism
 *
 * @since  1.6
 */
class MediaField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'Media';

    /**
     * The authorField.
     *
     * @var    string
     * @since  3.2
     */
    protected $authorField;

    /**
     * The asset.
     *
     * @var    string
     * @since  3.2
     */
    protected $asset;

    /**
     * The link.
     *
     * @var    string
     * @since  3.2
     */
    protected $link;

    /**
     * Modal width.
     *
     * @var    integer
     * @since  3.4.5
     */
    protected $width;

    /**
     * Modal height.
     *
     * @var    integer
     * @since  3.4.5
     */
    protected $height;

    /**
     * The preview.
     *
     * @var    string
     * @since  3.2
     */
    protected $preview;

    /**
     * The directory.
     *
     * @var    string
     * @since  3.2
     */
    protected $directory;

    /**
     * The previewWidth.
     *
     * @var    integer
     * @since  3.2
     */
    protected $previewWidth;

    /**
     * The previewHeight.
     *
     * @var    integer
     * @since  3.2
     */
    protected $previewHeight;

    /**
     * The folder.
     *
     * @var    string
     * @since  4.3.0
     */
    protected $folder;

    /**
     * Comma separated types of files for Media Manager
     * Possible values: images,audios,videos,documents
     *
     * @var    string
     * @since  4.0.0
     */
    protected $types;

    /**
     * Layout to render
     *
     * @var    string
     * @since  3.5
     */
    protected $layout = 'joomla.form.field.media';

    /**
     * The parent class of the field
     *
     * @var  string
     * @since 4.0.0
     */
    protected $parentclass;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        switch ($name) {
            case 'authorField':
            case 'asset':
            case 'link':
            case 'width':
            case 'height':
            case 'preview':
            case 'directory':
            case 'previewWidth':
            case 'previewHeight':
            case 'folder':
            case 'types':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'authorField':
            case 'asset':
            case 'link':
            case 'width':
            case 'height':
            case 'preview':
            case 'directory':
            case 'folder':
            case 'types':
                $this->$name = (string) $value;
                break;

            case 'previewWidth':
            case 'previewHeight':
                $this->$name = (int) $value;
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result === true) {
            $assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';

            $this->authorField   = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
            $this->asset         = $this->form->getValue($assetField) ?: (string) $this->element['asset_id'];
            $this->link          = (string) $this->element['link'];
            $this->width         = isset($this->element['width']) ? (int) $this->element['width'] : 800;
            $this->height        = isset($this->element['height']) ? (int) $this->element['height'] : 500;
            $this->preview       = (string) $this->element['preview'];
            $this->directory     = (string) $this->element['directory'];
            $this->previewWidth  = isset($this->element['preview_width']) ? (int) $this->element['preview_width'] : 200;
            $this->previewHeight = isset($this->element['preview_height']) ? (int) $this->element['preview_height'] : 200;
            $this->types         = isset($this->element['types']) ? (string) $this->element['types'] : 'images';
        }

        return $result;
    }

    /**
     * Method to get the field input markup for a media selector.
     * Use attributes to identify specific created_by and asset_id fields
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput()
    {
        if (empty($this->layout)) {
            throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }

        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    /**
     * Get the data that is going to be passed to the layout
     *
     * @return  array
     */
    public function getLayoutData()
    {
        // Get the basic field data
        $data = parent::getLayoutData();

        $asset = $this->asset;

        if ($asset === '') {
            $asset = Factory::getApplication()->getInput()->get('option');
        }

        // Value in new format such as images/headers/blue-flower.jpg#joomlaImage://local-images/headers/blue-flower.jpg?width=700&height=180
        if ($this->value && strpos($this->value, '#') !== false) {
            $uri     = new Uri(explode('#', $this->value)[1]);
            $adapter = $uri->getHost();
            $path    = $uri->getPath();

            // Remove filename from stored path to get the path to the folder which file is stored
            $pos = strrpos($path, '/');

            if ($pos !== false) {
                $path = substr($path, 0, $pos);
            }

            if ($path === '') {
                $path = '/';
            }

            $this->folder = $adapter . ':' . $path;
        } elseif ($this->value && is_file(JPATH_ROOT . '/' . $this->value)) {
            /**
             * Local image, for example images/sampledata/cassiopeia/nasa2-640.jpg. We need to validate and make sure
             * the top level folder is one of the directories configured in the filesystem local plugin to avoid an error
             * message being displayed when users click on Select button to select a new image.
             */
            $paths = explode('/', Path::clean($this->value, '/'));

            // Remove filename from $paths array
            array_pop($paths);

            if (MediaHelper::isValidLocalDirectory($paths[0])) {
                $adapterName  = array_shift($paths);
                $this->folder = 'local-' . $adapterName . ':/' . implode('/', $paths);
            }
        } elseif ($this->directory && is_dir(JPATH_ROOT . '/' . ComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $this->directory)) {
            /**
             * This is the case where a folder is configured in directory attribute of the form field. The directory needs
             * to be a relative folder of the folder configured in Path to Images Folder config option of Media component.
             * Same with an already stored local image above, we need to validate and make sure the top level folder is one of the
             * directories configured in the filesystem local plugin.
             */
            $path  = ComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $this->directory;
            $paths = explode('/', Path::clean($path, '/'));

            if (MediaHelper::isValidLocalDirectory($paths[0])) {
                $adapterName  = array_shift($paths);
                $this->folder = 'local-' . $adapterName . ':/' . implode('/', $paths);
            }
        } elseif ($this->directory && strpos($this->directory, ':')) {
            /**
             * Directory contains adapter information and path, for example via programming or directly defined in xml
             * via directory attribute
             */
            $this->folder = $this->directory;
        } else {
            $this->folder = '';
        }

        $mediaTypes   = array_map('trim', explode(',', $this->types));
        $types        = [];
        $imagesExt    = array_map(
            'trim',
            explode(
                ',',
                ComponentHelper::getParams('com_media')->get(
                    'image_extensions',
                    'bmp,gif,jpg,jpeg,png,webp'
                )
            )
        );
        $audiosExt = array_map(
            'trim',
            explode(
                ',',
                ComponentHelper::getParams('com_media')->get(
                    'audio_extensions',
                    'mp3,m4a,mp4a,ogg'
                )
            )
        );
        $videosExt = array_map(
            'trim',
            explode(
                ',',
                ComponentHelper::getParams('com_media')->get(
                    'video_extensions',
                    'mp4,mp4v,mpeg,mov,webm'
                )
            )
        );
        $documentsExt = array_map(
            'trim',
            explode(
                ',',
                ComponentHelper::getParams('com_media')->get(
                    'doc_extensions',
                    'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv'
                )
            )
        );

        $imagesAllowedExt    = [];
        $audiosAllowedExt    = [];
        $videosAllowedExt    = [];
        $documentsAllowedExt = [];

        // Cleanup the media types
        array_map(
            function ($mediaType) use (&$types, &$imagesAllowedExt, &$audiosAllowedExt, &$videosAllowedExt, &$documentsAllowedExt, $imagesExt, $audiosExt, $videosExt, $documentsExt) {
                switch ($mediaType) {
                    case 'images':
                        $types[] = '0';
                        $imagesAllowedExt = $imagesExt;
                        break;
                    case 'audios':
                        $types[] = '1';
                        $audiosAllowedExt = $audiosExt;
                        break;
                    case 'videos':
                        $types[] = '2';
                        $videosAllowedExt = $videosExt;
                        break;
                    case 'documents':
                        $types[] = '3';
                        $documentsAllowedExt = $documentsExt;
                        break;
                    default:
                        break;
                }
            },
            $mediaTypes
        );

        sort($types);

        $extraData = [
            'asset'               => $asset,
            'authorField'         => $this->authorField,
            'authorId'            => $this->form->getValue($this->authorField),
            'folder'              => $this->folder,
            'link'                => $this->link,
            'preview'             => $this->preview,
            'previewHeight'       => $this->previewHeight,
            'previewWidth'        => $this->previewWidth,
            'mediaTypes'          => implode(',', $types),
            'imagesExt'           => $imagesExt,
            'audiosExt'           => $audiosExt,
            'videosExt'           => $videosExt,
            'documentsExt'        => $documentsExt,
            'imagesAllowedExt'    => $imagesAllowedExt,
            'audiosAllowedExt'    => $audiosAllowedExt,
            'videosAllowedExt'    => $videosAllowedExt,
            'documentsAllowedExt' => $documentsAllowedExt,
        ];

        return array_merge($data, $extraData);
    }
}
