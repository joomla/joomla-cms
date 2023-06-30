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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
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
     * Returns the templates
     *
     * @return  void
     *
     * @since   5.0.0
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
                'title'       => $language->hasKey('PLG_TINY_TEMPLATE_' . $title_upper . '_TITLE') ? $this->text('PLG_TINY_TEMPLATE_' . $title_upper . '_TITLE') : $filename,
                'description' => $language->hasKey('PLG_TINY_TEMPLATE_' . $title_upper . '_DESC') ? $this->text('PLG_TINY_TEMPLATE_' . $title_upper . '_DESC') : ' ',
                'content'     => file_get_contents($filepath),
            ];
        }

        echo json_encode($templates);
        exit();
    }
}
