<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Plugin;

use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Media Manager Base Plugin for the media actions
 *
 * @since  4.0.0
 */
class MediaActionPlugin extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * The form event. Load additional parameters when available into the field form.
     * Only when the type of the form is of interest.
     *
     * @param   Form       $form  The form
     * @param   \stdClass  $data  The data
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        // Check if it is the right form
        if ($form->getName() != 'com_media.file') {
            return;
        }

        $this->loadCss();
        $this->loadJs();

        // The file with the params for the edit view
        $paramsFile = JPATH_PLUGINS . '/media-action/' . $this->_name . '/form/' . $this->_name . '.xml';

        // When the file exists, load it into the form
        if (file_exists($paramsFile)) {
            $form->loadFile($paramsFile);
        }
    }

    /**
     * Load the javascript files of the plugin.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function loadJs()
    {
        HTMLHelper::_(
            'script',
            'plg_media-action_' . $this->_name . '/' . $this->_name . '.js',
            ['version' => 'auto', 'relative' => true],
            ['type' => 'module']
        );
    }

    /**
     * Load the CSS files of the plugin.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function loadCss()
    {
        HTMLHelper::_(
            'stylesheet',
            'plg_media-action_' . $this->_name . '/' . $this->_name . '.css',
            ['version' => 'auto', 'relative' => true]
        );
    }
}
