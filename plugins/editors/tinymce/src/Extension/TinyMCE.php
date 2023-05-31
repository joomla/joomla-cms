<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\Extension;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Plugin\Editors\TinyMCE\PluginTraits\DisplayTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * TinyMCE Editor Plugin
 *
 * @since  1.5
 */
final class TinyMCE extends CMSPlugin
{
    use DisplayTrait;
    use DatabaseAwareTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns the HTML for the highlighter plugin
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onAjaxTinymceHighlighter()
    {
        $isFullScreen = $this->getApplication()->input->getPath('fullscreen', '') === '' ? false : true;

        // Default JS files
        $jsFiles = [
            'media/vendor/codemirror/lib/codemirror',
            'media/vendor/codemirror/addon/edit/matchbrackets',
            'media/vendor/codemirror/mode/xml/xml',
            'media/vendor/codemirror/mode/javascript/javascript',
            'media/vendor/codemirror/mode/css/css',
            'media/vendor/codemirror/mode/htmlmixed/htmlmixed',
            'media/vendor/codemirror/addon/dialog/dialog',
            'media/vendor/codemirror/addon/search/searchcursor',
            'media/vendor/codemirror/addon/search/search',
            'media/vendor/codemirror/addon/selection/active-line',
        ];

        // Default CSS files
        $cssFiles = [
            'media/vendor/codemirror/lib/codemirror',
            'media/vendor/codemirror/addon/dialog/dialog',
        ];

        if ($isFullScreen) {
            $jsFiles[]  = 'media/vendor/codemirror/addon/display/fullscreen';
            $cssFiles[] = 'media/vendor/codemirror/addon/display/fullscreen';
        }

        $base    = Uri::root(true);
        $jsFile  = HTMLHelper::_('script', 'plg_editors_tinymce/plugins/highlighter/source.js', ['version' => 'auto', 'relative' => true, 'pathOnly' => true], []);
        $cssFile = HTMLHelper::_('stylesheet', 'plg_editors_tinymce/plugins/highlighter/source.css', ['version' => 'auto', 'relative' => true, 'pathOnly' => true], []);

        $nonce   = $this->getApplication()->get('csp_nonce');
        $jsTags  = '';
        $cssTags = '';

        foreach ($jsFiles as $js) {
            $jsTags .= '<script defer src="' . $base . '/' . $js . (JDEBUG ? '.js' : '.min.js') . '"' . (!$nonce ? '' : ' nonce="' . $nonce . '"') . '></script>';
        }

        foreach ($cssFiles as $css) {
            $cssTags .= '<link rel="stylesheet" href="' . $base . '/' . $css . '.css"' . (!$nonce ? '' : ' nonce="' . $nonce . '"') . '>';
        }

        $cssFileTag = '<link rel="stylesheet" href="' . $cssFile . '"' . (!$nonce ? '' : ' nonce="' . $nonce . '"') . '>';
        $jsFileTag  = '<script defer src="' . $jsFile . '"' . (!$nonce ? '' : ' nonce="' . $nonce . '"') . '></script>';
        return <<<HTMLSTRING
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html"/>
        <meta charset="UTF-8">
        $jsTags
        $cssTags
        $cssFileTag
        $jsFileTag
    </head>
    <body></body>
</html>
HTMLSTRING;
    }

    /**
     * Returns the templates
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onAjaxTinymce()
    {
        if (!Session::checkToken('request')) {
            echo json_encode([]);
            exit();
        }

        $templates = [];
        $language  = $this->getApplication()->getLanguage();
        $template  = $this->getApplication()->input->getPath('template', '');

        if ('' === $template) {
            echo json_encode([]);
            exit();
        }

        $filepaths = Folder::exists(JPATH_ROOT . '/templates/' . $template)
            ? Folder::files(JPATH_ROOT . '/templates/' . $template, '\.(html|txt)$', false, true)
            : [];

        foreach ($filepaths as $filepath) {
            $fileinfo    = pathinfo($filepath);
            $filename    = $fileinfo['filename'];
            $title_upper = strtoupper($filename);

            if ($filename === 'index') {
                continue;
            }

            $templates[] = (object) [
                'title'       => $language->hasKey('PLG_TINY_TEMPLATE_' . $title_upper . '_TITLE') ? Text::_('PLG_TINY_TEMPLATE_' . $title_upper . '_TITLE') : $filename,
                'description' => $language->hasKey('PLG_TINY_TEMPLATE_' . $title_upper . '_DESC') ? Text::_('PLG_TINY_TEMPLATE_' . $title_upper . '_DESC') : ' ',
                'content'     => file_get_contents($filepath),
            ];
        }

        echo json_encode($templates);
        exit();
    }
}
