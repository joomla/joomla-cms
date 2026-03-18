<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Field to load the form inside current form
 *
 * @since  4.0.0
 */
class AccessiblemediaField extends SubformField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'Accessiblemedia';

    /**
     * The preview.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $preview;

    /**
     * The directory.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $directory;

    /**
     * The previewWidth.
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $previewWidth;

    /**
     * The previewHeight.
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $previewHeight;

    /**
     * Layout to render
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout;

    /**
     * Media types like 'images, audios, documents, videos'
     *
     * @var    string
     * @since  6.1.0
     */
    protected $types;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   4.0.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'directory':
            case 'preview':
            case 'previewHeight':
            case 'previewWidth':
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
     * @since   4.0.0
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'directory':
            case 'preview':
            case 'types':
                $this->$name = (string) $value;
                break;

            case 'previewHeight':
            case 'previewWidth':
                $this->$name = (int) $value;
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        /**
         * When you have subforms which are not repeatable (i.e. a subform custom field with the
         * repeat attribute set to 0) you get an array here since the data comes from decoding the
         * JSON into an associative array, including the media subfield's data.
         *
         * However, this method expects an object or a string, not an array. Typecasting the array
         * to an object solves the data format discrepancy.
         */
        $value = \is_array($value) ? (object) $value : $value;

        /**
         * If the value is not a string, it is
         * most likely within a custom field of type subform
         * and the value is a stdClass with properties
         * imagefile and alt_text. So it is fine.
        */
        if (\is_string($value)) {
            json_decode($value);

            // Check if value is a valid JSON string.
            if ($value !== '' && json_last_error() !== JSON_ERROR_NONE) {
                /**
                 * If the value is not empty and is not a valid JSON string,
                 * it is most likely a custom field created in Joomla 3 and
                 * the value is a string that contains the file name.
                */
                if (is_file(JPATH_ROOT . '/' . $value)) {
                    $value = '{"imagefile":"' . $value . '","alt_text":""}';
                } else {
                    $value = '';
                }
            }
        } elseif (
            !\is_object($value)
            || !property_exists($value, 'imagefile')
            || !property_exists($value, 'alt_text')
        ) {
            return false;
        }

        if (!parent::setup($element, $value, $group)) {
            return false;
        }

        $this->directory     = (string)$this->element['directory'];
        $this->preview       = (string)$this->element['preview'];
        $this->previewHeight = isset($this->element['preview_height']) ? (int)$this->element['preview_height'] : 200;
        $this->previewWidth  = isset($this->element['preview_width']) ? (int)$this->element['preview_width'] : 200;
        $this->types         = isset($this->element['types']) ? (string)$this->element['types'] : 'images';
        $mediaTypes          = explode(',', $this->types);
        $fieldName           = (\in_array('images', $mediaTypes)) ? 'imagefile' : 'file';

        // Build the form source
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<form>
	<fieldset
		name="accessiblemedia"
		label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_LABEL"
	>
		<field
			name="$fieldName"
			type="media"
			label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_IMAGEFILE_LABEL"
			directory="$this->directory"
			preview="$this->preview"
			preview_width="$this->previewWidth"
			preview_height="$this->previewHeight"
			types="$this->types"
			schemes="http,https,ftp,ftps,data,file"
			validate="url"
			relative="true"
		/>
XML;
        if (\in_array('images', $mediaTypes)) {
            $xml .= <<<XML
		<field
			name="alt_text"
			type="text"
			label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_ALT_TEXT_LABEL"
		/>

		<field
			name="alt_empty"
			type="checkbox"
			label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_ALT_EMPTY_LABEL"
			description="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_ALT_EMPTY_DESC"
		/>
XML;
        }
        if (\in_array('videos', $mediaTypes) && $this->element['video_poster'] == 1) {
            $xml .= <<<XML
		<field
			name="poster"
			type="media"
			label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_VIDEO_POSTER_LABEL"
			directory="$this->directory"
			preview="$this->preview"
			preview_width="$this->previewWidth"
			preview_height="$this->previewHeight"
			types="images"
			schemes="http,https,ftp,ftps,data,file"
			validate="url"
			relative="true"
		/>
XML;
        }
        if (\in_array('documents', $mediaTypes)) {
            $xml .= <<<XML
		<field
			name="linktext"
			type="text"
			label="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_LINKTEXT_LABEL"
			description="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_LINKTEXT_DESC"
			filter="string"
			hint="JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_LINKTEXT_DEFAULT_VALUE"
		/>
XML;
        }
        $xml .= <<<XML
	</fieldset>
</form>
XML;

        $this->formsource = $xml;

        $this->layout = 'joomla.form.field.media.accessiblemedia';

        return true;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   6.1.0
     */
    protected function getInput()
    {
        $subForm = $this->loadSubForm();

        $mediaTypes = explode(',', $this->types);
        $label      = '';
        if (\count($mediaTypes) > 1) {
            $labels = [];

            foreach ($mediaTypes as $type) {
                $const    = 'JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_MEDIA_TYPE_' . strtoupper($type);
                $labels[] = Text::_($const);
            }

            if (!empty($labels)) {
                $label = Text::sprintf('JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_LABEL', implode(', ', $labels));
            }
        } else {
            $label = Text::_('JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_MEDIA_TYPE_' . strtoupper($mediaTypes[0]));
        }

        $fieldName = (\in_array('images', $mediaTypes)) ? 'imagefile' : 'file';
        if (!empty($label) && $subForm->getField($fieldName)) {
            $subForm->setFieldAttribute($fieldName, 'label', $label);
        }

        if ($subForm && $subForm->getField('linktext')) {
            $subForm->setFieldAttribute('linktext', 'default', Text::_('JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_LINKTEXT_DEFAULT_VALUE'));
        }

        $this->form = $subForm;

        return parent::getInput();
    }
}
