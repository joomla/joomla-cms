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

		// Disable features that loaded by Joomla! API, or not in use
		$this->setEnableJqueryNoConflict(false);
		$this->disableVendor('jquery');
		$this->disableVendor('fontawesome');
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
	public function renderHead()
	{
		list($cssFiles, $jsFiles, $inlineCss, $inlineJs, $inlineHead) = $this->getAssets(null, self::RELATIVE_URL);
		$html = '';
		$doc  = Factory::getApplication()->getDocument();

		foreach ($cssFiles as $file)
		{
			$html .= sprintf('<link rel="stylesheet" type="text/css" href="%s">' . "\n", $file);
		}

		foreach ($inlineCss as $content)
		{
			$html .= sprintf('<style>%s</style>' . "\n", $content);
		}

		foreach ($jsFiles as $file)
		{
			$html .= sprintf('<script type="text/javascript" src="%s" defer></script>' . "\n", $file);
		}

		$nonce = '';

		if ($doc->cspNonce)
		{
			$nonce = ' nonce="' . $doc->cspNonce . '"';
		}

		foreach ($inlineJs as $content)
		{
			$html .= sprintf('<script type="module"%s>%s</script>' . "\n", $nonce, $content);
		}

		foreach ($inlineHead as $content)
		{
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
	public function render($initialize = true, $renderStackedData = true)
	{
		$js  = '';
		$doc = Factory::getApplication()->getDocument();

		if ($initialize)
		{
			$js = $this->getJsInitializationCode();
		}

		if ($renderStackedData && $this->debugBar->hasStackedData())
		{
			foreach ($this->debugBar->getStackedData() as $id => $data)
			{
				$js .= $this->getAddDatasetCode($id, $data, '(stacked)');
			}
		}

		$suffix = !$initialize ? '(ajax)' : null;
		$js .= $this->getAddDatasetCode($this->debugBar->getCurrentRequestId(), $this->debugBar->getData(), $suffix);

		$nonce = '';

		if ($doc->cspNonce)
		{
			$nonce = ' nonce="' . $doc->cspNonce . '"';
		}

		if ($this->useRequireJs)
		{
			return "<script type=\"module\"$nonce>\nrequire(['debugbar'], function(PhpDebugBar){ $js });\n</script>\n";
		}
		else
		{
			return "<script type=\"module\"$nonce>\n$js\n</script>\n";
		}
	}
}
