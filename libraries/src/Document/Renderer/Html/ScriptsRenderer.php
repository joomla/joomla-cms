<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\WebAsset\WebAssetItemInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JDocument head renderer
 *
 * @since  4.0.0
 */
class ScriptsRenderer extends DocumentRenderer
{
    /**
     * List of already rendered src
     *
     * @var array
     *
     * @since   4.0.0
     */
    private $renderedSrc = [];

    /**
     * Renders the document script tags and returns the results as a string
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
        // Get line endings
        $lnEnd        = $this->_doc->_getLineEnd();
        $tab          = $this->_doc->_getTab();
        $buffer       = '';
        $wam          = $this->_doc->getWebAssetManager();
        $assets       = $wam->getAssets('script', true);

        // Get a list of inline assets and their relation with regular assets
        $inlineAssets   = $wam->filterOutInlineAssets($assets);
        $inlineRelation = $wam->getInlineRelation($inlineAssets);

        // Merge with existing scripts, for rendering
        $assets = array_merge(array_values($assets), $this->_doc->_scripts);

        // Generate script file links
        foreach ($assets as $key => $item) {
            // Check whether we have an Asset instance, or old array with attributes
            $asset = $item instanceof WebAssetItemInterface ? $item : null;

            // Add src attribute for non Asset item
            if (!$asset) {
                $item['src'] = $key;
            }

            // Check for inline content "before"
            if ($asset && !empty($inlineRelation[$asset->getName()]['before'])) {
                foreach ($inlineRelation[$asset->getName()]['before'] as $itemBefore) {
                    $buffer .= $this->renderInlineElement($itemBefore);

                    // Remove this item from inline queue
                    unset($inlineAssets[$itemBefore->getName()]);
                }
            }

            $buffer .= $this->renderElement($item);

            // Check for inline content "after"
            if ($asset && !empty($inlineRelation[$asset->getName()]['after'])) {
                foreach ($inlineRelation[$asset->getName()]['after'] as $itemBefore) {
                    $buffer .= $this->renderInlineElement($itemBefore);

                    // Remove this item from inline queue
                    unset($inlineAssets[$itemBefore->getName()]);
                }
            }
        }

        // Generate script declarations for assets
        foreach ($inlineAssets as $item) {
            $buffer .= $this->renderInlineElement($item);
        }

        // Generate script declarations for old scripts
        foreach ($this->_doc->_script as $type => $contents) {
            // Test for B.C. in case someone still store script declarations as single string
            if (\is_string($contents)) {
                $contents = [$contents];
            }

            foreach ($contents as $content) {
                $buffer .= $this->renderInlineElement(
                    [
                        'type'    => $type,
                        'content' => $content,
                    ]
                );
            }
        }

        // Output the custom tags - array_unique makes sure that we don't output the same tags twice
        foreach (array_unique($this->_doc->_custom) as $custom) {
            $buffer .= $tab . $custom . $lnEnd;
        }

        return ltrim($buffer, $tab);
    }

    /**
     * Renders the element
     *
     * @param   WebAssetItemInterface|array  $item  The element
     *
     * @return  string  The resulting string
     *
     * @since   4.0.0
     */
    private function renderElement($item): string
    {
        $buffer = '';
        $asset  = $item instanceof WebAssetItemInterface ? $item : null;
        $src    = $asset ? $asset->getUri() : ($item['src'] ?? '');

        // Make sure we have a src, and it not already rendered
        if (!$src || !empty($this->renderedSrc[$src]) || ($asset && $asset->getOption('webcomponent'))) {
            return '';
        }

        $lnEnd        = $this->_doc->_getLineEnd();
        $tab          = $this->_doc->_getTab();
        $mediaVersion = $this->_doc->getMediaVersion();

        // Get the attributes and other options
        if ($asset) {
            $attribs     = $asset->getAttributes();
            $version     = $asset->getVersion();
            $conditional = $asset->getOption('conditional');

            // Add an asset info for debugging
            if (JDEBUG) {
                $attribs['data-asset-name'] = $asset->getName();

                if ($asset->getDependencies()) {
                    $attribs['data-asset-dependencies'] = implode(',', $asset->getDependencies());
                }

                if ($asset->getOption('deprecated')) {
                    @trigger_error(
                        sprintf('Web Asset script [%s] is deprecated. %s', $asset->getName(), $asset->getOption('deprecatedMsg', '')),
                        E_USER_DEPRECATED
                    );
                }
            }
        } else {
            $attribs     = $item;
            $version     = isset($attribs['options']['version']) ? $attribs['options']['version'] : '';
            $conditional = !empty($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;
        }

        // Add "nonce" attribute if exist
        if ($this->_doc->cspNonce && !is_null($this->_doc->cspNonce)) {
            $attribs['nonce'] = $this->_doc->cspNonce;
        }

        // To prevent double rendering
        $this->renderedSrc[$src] = true;

        // Check if script uses media version.
        if ($version && strpos($src, '?') === false && ($mediaVersion || $version !== 'auto')) {
            $src .= '?' . ($version === 'auto' ? $mediaVersion : $version);
        }

        $buffer .= $tab;

        // This is for IE conditional statements support.
        if (!\is_null($conditional)) {
            $buffer .= '<!--[if ' . $conditional . ']>';
        }

        // Render the element with attributes
        $buffer .= '<script src="' . htmlspecialchars($src) . '"';
        $buffer .= $this->renderAttributes($attribs);
        $buffer .= '></script>';

        // This is for IE conditional statements support.
        if (!\is_null($conditional)) {
            $buffer .= '<![endif]-->';
        }

        $buffer .= $lnEnd;

        return $buffer;
    }

    /**
     * Renders the inline element
     *
     * @param   WebAssetItemInterface|array  $item  The element
     *
     * @return  string  The resulting string
     *
     * @since   4.0.0
     */
    private function renderInlineElement($item): string
    {
        $buffer = '';
        $lnEnd  = $this->_doc->_getLineEnd();
        $tab    = $this->_doc->_getTab();

        if ($item instanceof WebAssetItemInterface) {
            $attribs = $item->getAttributes();
            $content = $item->getOption('content');
        } else {
            $attribs = $item;
            $content = $item['content'] ?? '';

            unset($attribs['content']);
        }

        // Do not produce empty elements
        if (!$content) {
            return '';
        }

        // Add "nonce" attribute if exist
        if ($this->_doc->cspNonce && !is_null($this->_doc->cspNonce)) {
            $attribs['nonce'] = $this->_doc->cspNonce;
        }

        $buffer .= $tab . '<script';
        $buffer .= $this->renderAttributes($attribs);
        $buffer .= '>';

        // This is for full XHTML support.
        if ($this->_doc->_mime !== 'text/html') {
            $buffer .= $tab . $tab . '//<![CDATA[' . $lnEnd;
        }

        $buffer .= $content;

        // See above note
        if ($this->_doc->_mime !== 'text/html') {
            $buffer .= $tab . $tab . '//]]>' . $lnEnd;
        }

        $buffer .= '</script>' . $lnEnd;

        return $buffer;
    }

    /**
     * Renders the element attributes
     *
     * @param   array  $attributes  The element attributes
     *
     * @return  string  The attributes string
     *
     * @since   4.0.0
     */
    private function renderAttributes(array $attributes): string
    {
        $buffer = '';

        $defaultJsMimes         = ['text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript'];
        $html5NoValueAttributes = ['defer', 'async', 'nomodule'];

        foreach ($attributes as $attrib => $value) {
            // Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
            if ($attrib === 'options' || $attrib === 'src') {
                continue;
            }

            // Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
            if (\in_array($attrib, ['type', 'mime']) && $this->_doc->isHtml5() && \in_array($value, $defaultJsMimes)) {
                continue;
            }

            // B/C: If defer and async is false or empty don't render the attribute. Also skip if value is bool:false.
            if (\in_array($attrib, ['defer', 'async']) && !$value || $value === false) {
                continue;
            }

            // NoValue attribute, if it have bool:true
            $isNoValueAttrib = $value === true || \in_array($attrib, $html5NoValueAttributes);

            // Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
            if ($attrib === 'mime') {
                $attrib = 'type';
            } elseif ($isNoValueAttrib) {
                // NoValue attribute in non HTML5 should contain a value, set it equal to attribute name.
                $value = $attrib;
            }

            // Add attribute to script tag output.
            $buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

            if (!($this->_doc->isHtml5() && $isNoValueAttrib)) {
                // Json encode value if it's an array.
                $value = !is_scalar($value) ? json_encode($value) : $value;

                $buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
            }
        }

        return $buffer;
    }
}
