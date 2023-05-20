<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JDocument metas renderer
 *
 * @since  4.0.0
 */
class MetasRenderer extends DocumentRenderer
{
    /**
     * Renders the document metas and returns the results as a string
     *
     * @param   string  $head     (unused)
     * @param   array   $params   Associative array of values
     * @param   string  $content  The script
     *
     * @return  string  The output of the script
     *
     * @since   4.0.0
     */
    public function render($head, $params = [], $content = null)
    {
        // Convert the tagids to titles
        if (isset($this->_doc->_metaTags['name']['tags'])) {
            $tagsHelper                            = new TagsHelper();
            $this->_doc->_metaTags['name']['tags'] = implode(', ', $tagsHelper->getTagNames($this->_doc->_metaTags['name']['tags']));
        }

        /** @var \Joomla\CMS\Application\CMSApplication $app */
        $app = Factory::getApplication();
        $wa  = $this->_doc->getWebAssetManager();

        // Check for AttachBehavior and web components
        foreach ($wa->getAssets('script', true) as $asset) {
            if ($asset instanceof WebAssetAttachBehaviorInterface) {
                $asset->onAttachCallback($this->_doc);
            }
        }

        // Trigger the onBeforeCompileHead event
        $app->triggerEvent('onBeforeCompileHead');

        // Add Script Options as inline asset
        $scriptOptions = $this->_doc->getScriptOptions();

        if ($scriptOptions) {
            $prettyPrint = (JDEBUG && \defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
            $jsonOptions = json_encode($scriptOptions, $prettyPrint);
            $jsonOptions = $jsonOptions ?: '{}';

            $wa->addInlineScript(
                $jsonOptions,
                ['name' => 'joomla.script.options', 'position' => 'before'],
                ['type' => 'application/json', 'class' => 'joomla-script-options new'],
                ['core']
            );
        }

        // Lock the AssetManager
        $wa->lock();

        // Get line endings
        $lnEnd        = $this->_doc->_getLineEnd();
        $tab          = $this->_doc->_getTab();
        $buffer       = '';

        // Generate charset when using HTML5 (should happen first)
        if ($this->_doc->isHtml5()) {
            $buffer .= $tab . '<meta charset="' . $this->_doc->getCharset() . '">' . $lnEnd;
        }

        // Generate base tag (need to happen early)
        $base = $this->_doc->getBase();

        if (!empty($base)) {
            $buffer .= $tab . '<base href="' . $base . '">' . $lnEnd;
        }

        $noFavicon = true;
        $searchFor = 'image/vnd.microsoft.icon';

        array_map(function ($value) use (&$noFavicon, $searchFor) {
            if (isset($value['attribs']['type']) && $value['attribs']['type'] === $searchFor) {
                $noFavicon = false;
            }
        }, array_values((array)$this->_doc->_links));

        if ($noFavicon) {
            $client   = $app->isClient('administrator') === true ? 'administrator/' : 'site/';
            $template = $app->getTemplate(true);

            // Try to find a favicon by checking the template and root folder
            $icon           = '/favicon.ico';
            $foldersToCheck = [
                JPATH_BASE,
                JPATH_ROOT . '/media/templates/' . $client . $template->template,
                JPATH_BASE . '/templates/' . $template->template,
            ];

            foreach ($foldersToCheck as $base => $dir) {
                if (
                    $template->parent !== ''
                    && $base === 1
                    && !is_file(JPATH_ROOT . '/media/templates/' . $client . $template->template . $icon)
                ) {
                    $dir = JPATH_ROOT . '/media/templates/' . $client . $template->parent;
                }

                if (is_file($dir . $icon)) {
                    $urlBase = in_array($base, [0, 2]) ? Uri::base(true) : Uri::root(true);
                    $base    = in_array($base, [0, 2]) ? JPATH_BASE : JPATH_ROOT;
                    $path    = str_replace($base, '', $dir);
                    $path    = str_replace('\\', '/', $path);
                    $this->_doc->addFavicon($urlBase . $path . $icon);
                    break;
                }
            }
        }

        // Generate META tags (needs to happen as early as possible in the head)
        foreach ($this->_doc->_metaTags as $type => $tag) {
            foreach ($tag as $name => $contents) {
                if ($type === 'http-equiv' && !($this->_doc->isHtml5() && $name === 'content-type')) {
                    $buffer .= $tab . '<meta http-equiv="' . $name . '" content="'
                        . htmlspecialchars($contents, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
                } elseif ($type !== 'http-equiv' && !empty($contents)) {
                    $buffer .= $tab . '<meta ' . $type . '="' . $name . '" content="'
                        . htmlspecialchars($contents, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
                }
            }
        }

        // Don't add empty descriptions
        $documentDescription = $this->_doc->getDescription();

        if ($documentDescription) {
            $buffer .= $tab . '<meta name="description" content="' . htmlspecialchars($documentDescription, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
        }

        // Don't add empty generators
        $generator = $this->_doc->getGenerator();

        if ($generator) {
            $buffer .= $tab . '<meta name="generator" content="' . htmlspecialchars($generator, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
        }

        $buffer .= $tab . '<title>' . htmlspecialchars($this->_doc->getTitle(), ENT_COMPAT, 'UTF-8') . '</title>' . $lnEnd;

        // Generate link declarations
        foreach ($this->_doc->_links as $link => $linkAtrr) {
            $buffer .= $tab . '<link href="' . $link . '" ' . $linkAtrr['relType'] . '="' . $linkAtrr['relation'] . '"';

            if (\is_array($linkAtrr['attribs'])) {
                if ($temp = ArrayHelper::toString($linkAtrr['attribs'])) {
                    $buffer .= ' ' . $temp;
                }
            }

            $buffer .= '>' . $lnEnd;
        }

        return ltrim($buffer, $tab);
    }
}
