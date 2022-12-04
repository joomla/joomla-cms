<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\HTML\HTMLHelper;
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

    /**
     * Apply dark color scheme options when the template communicates it supports Dark Mode.
     *
     * If the user has disabled the ‘Auto-apply Dark Mode’ option we do nothing.
     *
     * If $forcedDark is false the editor must respect the browser color scheme preference with a
     * media query.
     *
     * If $forcedDark is true the editor must always apply a dark color scheme, regardless of the
     * browser preference.
     *
     * If this method is not called, the editor assumes that only a light color scheme, or whatever
     * skin the user provided is to be loaded.
     *
     * If the user has selected a skin other than oxide we do NOT change it automatically for Dark
     * Mode. We assume the user has a good reason for selecting a non-default color scheme. In this
     * case we will still apply a body class depending on the dark mode type (auto or forced), and
     * will load the custom CSS for the content if the user has enabled that option. This allows
     * frontend templates to provide custom content styling for automatic and forced dark mode.
     *
     * @param   bool  $forcedDark
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    public function onTemplateDarkModeSupported(bool $forcedDark)
    {
        // If we're told not to support Dark Mode, do nothing.
        if ($this->params->get('darkmode', 1) == 0) {
            return;
        }

        $document = $this->app->getDocument();
        $opts     = $document->getScriptOptions('plg_editor_tinymce');

        if (empty($opts) || !is_array($opts)) {
            return;
        }

        $opts['tinyMCE'] = (!isset($opts['tinyMCE']) || !is_array($opts['tinyMCE'])) ? [] : $opts['tinyMCE'];
        $opts['tinyMCE']['default'] = $opts['tinyMCE']['default'] ?? [];
        $opts['tinyMCE']['default']['skin'] = $opts['tinyMCE']['default']['skin'] ?? 'oxide';

        $hasDefaultSkin = $opts['tinyMCE']['default']['skin'] != 'oxide';

        if ($forcedDark) {
            // Forced dark mode: use oxide-dark and set the body class to `joomla-forced-dark`
            if ($hasDefaultSkin) {
                $opts['tinyMCE']['default']['skin'] = 'oxide-dark';
            }

            $opts['tinyMCE']['default']['body_class'] = 'joomla-forced-dark';
        } else {
            // Auto dark mode: use the custom `autodark` theme and set the body class to `joomla-suto-dark`
            if ($hasDefaultSkin) {
                $opts['tinyMCE']['default']['skin_url'] = '/media/plg_editors_tinymce/css/autodark';
            }

            $opts['tinyMCE']['default']['body_class'] = 'joomla-auto-dark';
        }

        // Optional: force Dark Mode compatibility in TinyMCE content
        if ($this->params->get('darkmode_content', 1) == 1) {
            // Find the content-dark.css file of the current (frontend or backend) template.
            $autoDark = HTMLHelper::_(
                'stylesheet',
                'content-dark.css',
                [
                    'pathOnly' => true,
                    'relative' => true,
                    'detectDebug' => true,
                ]
            );

            // If the file exists, load it as the last CSS file
            if (!empty($autoDark)) {
                $opts['tinyMCE']['default']['content_css'] =
                    $opts['tinyMCE']['default']['content_css'] .
                    ',' . $autoDark;
            }
        }

        // Apply the new TinyMCE default options
        $document->addScriptOptions('plg_editor_tinymce', $opts);
    }
}
