<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer as DebugBarJavascriptRenderer;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Custom JavascriptRenderer for DebugBar
 *
 * @since  4.0.0
 */
class JavascriptRenderer extends DebugBarJavascriptRenderer
{
    /**
     * Class constructor.
     *
     * @param   \DebugBar\DebugBar  $debugBar  DebugBar instance
     * @param   string              $baseUrl   The base URL from which assets will be served
     * @param   string              $basePath  The path which assets are relative to
     *
     * @since  4.0.0
     */
    public function __construct(DebugBar $debugBar, $baseUrl = null, $basePath = null)
    {
        parent::__construct($debugBar, $baseUrl, $basePath);
    }

    /**
     * Renders the html to include needed assets
     *
     * Only useful if Assetic is not used
     *
     * @return string
     *
     * @since  4.0.0
     */
    public function renderHead(): string
    {
        $assets     = $this->getAssets(self::RELATIVE_URL);
        $cssFiles   = $assets['css'];
        $jsFiles    = $assets['js'];
        $inlineCss  = $assets['inline_css'];
        $inlineJs   = $assets['inline_js'];
        $inlineHead = $assets['inline_head'];
        $html                                                     = '';
        $doc                                                      = Factory::getApplication()->getDocument();

        foreach ($cssFiles as $file) {
            $html .= \sprintf('<link rel="stylesheet" type="text/css" href="%s">' . "\n", $file);
        }

        foreach ($inlineCss as $content) {
            $html .= \sprintf('<style>%s</style>' . "\n", $content);
        }

        foreach ($jsFiles as $file) {
            $html .= \sprintf('<script type="text/javascript" src="%s" defer></script>' . "\n", $file);
        }

        $nonce = '';

        if ($doc->cspNonce) {
            $nonce = ' nonce="' . $doc->cspNonce . '"';
        }

        foreach ($inlineJs as $content) {
            $html .= \sprintf('<script%s>%s</script>' . "\n", $nonce, $content);
        }

        foreach ($inlineHead as $content) {
            $html .= $content . "\n";
        }

        return $html;
    }

    /**
     * Returns the code needed to display the debug bar
     *
     * AJAX request should not render the initialization code.
     *
     * @param   boolean  $initialize         Whether or not to render the debug bar initialization code
     * @param   boolean  $renderStackedData  Whether or not to render the stacked data
     *
     * @return string
     *
     * @since  4.0.0
     */
    public function render(bool $initialize = true, bool $renderStackedData = true): string
    {
        $this->setCspNonce(Factory::getApplication()->getDocument()->cspNonce);

        return parent::render($initialize, $renderStackedData);
    }
}
