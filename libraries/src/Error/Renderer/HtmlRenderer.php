<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error\Renderer;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Error\AbstractRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * HTML error page renderer
 *
 * @since  4.0.0
 * @todo   Change this renderer to use JDocumentHtml instead of JDocumentError, the latter is only used for B/C at this time
 */
class HtmlRenderer extends AbstractRenderer
{
	/**
	 * The format (type) of the error page
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $type = 'error';

	/**
	 * Render the error page for the given object
	 *
	 * @param   \Throwable  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function render(\Throwable $error): string
	{
		$app = Factory::getApplication();

		// Get the current template from the application
		$template = $app->getTemplate(true);

		// Push the error object into the document
		$this->getDocument()->setError($error);

		// Add registry file for the template asset
		$wa = $this->getDocument()->getWebAssetManager()->getRegistry();

		$wa->addTemplateRegistryFile($template->template, $app->getClientId());

		if (!empty($template->parent))
		{
			$wa->addTemplateRegistryFile($template->parent, $app->getClientId());
		}

		if (ob_get_contents())
		{
			ob_end_clean();
		}

		$this->getDocument()->setTitle(Text::_('Error') . ': ' . $error->getCode());

		return $this->getDocument()->render(
			false,
			[
				'template'         => $template->template,
				'directory'        => JPATH_THEMES,
				'debug'            => JDEBUG,
				'csp_nonce'        => $app->get('csp_nonce'),
				'templateInherits' => $template->parent,
				'params'           => $template->params,
			]
		);
	}
}
