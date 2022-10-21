<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Plugin\Editors\TinyMCE\PluginTraits\DisplayTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * TinyMCE Editor Plugin
 *
 * @since  1.5
 */
class PlgEditorTinymce extends CMSPlugin
{
    use DisplayTrait;

    /**
     * Base path for editor files
     *
     * @since  3.5
     *
     * @deprecated 5.0
     */
    protected $_basePath = 'media/vendor/tinymce';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Loads the application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  3.2
     */
    protected $app = null;

    /**
     * Initialises the Editor.
     *
     * @return  void
     *
     * @since   1.5
     */
    public function onInit()
    {
    }
}
