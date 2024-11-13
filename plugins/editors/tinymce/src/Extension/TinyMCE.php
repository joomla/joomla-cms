<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\Extension;

use Joomla\CMS\Event\Editor\EditorSetupEvent;
use Joomla\CMS\Event\Plugin\AjaxEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\String\StringableInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Folder;
use Joomla\Plugin\Editors\TinyMCE\PluginTraits\KnownButtons;
use Joomla\Plugin\Editors\TinyMCE\PluginTraits\ToolbarPresets;
use Joomla\Plugin\Editors\TinyMCE\Provider\TinyMCEProvider;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * TinyMCE Editor Plugin
 *
 * @since  1.5
 */
final class TinyMCE extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    // @todo: KnownButtons, ToolbarPresets for backward compatibility. Remove in Joomla 6
    use KnownButtons;
    use ToolbarPresets;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onEditorSetup' => 'onEditorSetup',
            'onAjaxTinymce' => 'onAjaxTinymce',
        ];
    }

    /**
     * Register Editor instance
     *
     * @param EditorSetupEvent $event
     *
     * @return void
     *
     * @since   5.0.0
     */
    public function onEditorSetup(EditorSetupEvent $event)
    {
        $this->loadLanguage();

        $event->getEditorsRegistry()
            ->add(new TinyMCEProvider($this->params, $this->getApplication(), $this->getDispatcher(), $this->getDatabase()));
    }

    /**
     * Returns the templates
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onAjaxTinymce(AjaxEvent $event)
    {
        // Create response object, with list of the templates
        $response = new class () implements StringableInterface {
            public $data = [];

            public function __toString(): string
            {
                return json_encode($this->data);
            }
        };
        $event->updateEventResult($response);

        if (!Session::checkToken('request')) {
            return;
        }

        $this->loadLanguage();

        $templates = [];
        $language  = $this->getApplication()->getLanguage();
        $template  = $this->getApplication()->getInput()->getPath('template', '');

        if ('' === $template) {
            return;
        }

        $filepaths = is_dir(JPATH_ROOT . '/templates/' . $template)
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

        // Add the list of templates to the response
        $response->data = $templates;
    }
}
