<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Html helper class.
 *
 * @since  1.6
 */
class Templates
{
	/**
	 * Display the thumb for the template.
	 *
	 * @param   string   $template  The name of the template.
	 * @param   integer  $clientId  The application client ID the template applies to
	 *
	 * @return  string  The html string
	 *
	 * @since   1.6
	 *
	 * @deprecated  5.0  Use \Joomla\Component\Templates\Administrator\Helper\TemplatesHelper::getThumbnail() instead
	 */
	public function thumb($template, $clientId = 0)
	{
		$client = ApplicationHelper::getClientInfo($clientId);
		$basePath = $client->path . '/templates/' . $template;
		$thumb = $basePath . '/template_thumbnail.png';
		$preview = $basePath . '/template_preview.png';
		$html = '';

		if (file_exists($thumb))
		{
			$clientPath = ($clientId == 0) ? '' : 'administrator/';
			$thumb = $clientPath . 'templates/' . $template . '/template_thumbnail.png';
			$html = HTMLHelper::_('image', $thumb, Text::_('COM_TEMPLATES_PREVIEW'));

			if (file_exists($preview))
			{
				$html = '<button type="button" data-bs-target="#' . $template . '-Modal" class="thumbnail" data-bs-toggle="modal" title="'. Text::_('COM_TEMPLATES_CLICK_TO_ENLARGE') . '">' . $html . '</button>';
			}
		}

		return $html;
	}

	/**
	 * Renders the html for the modal linked to thumb.
	 *
	 * @param   string   $template  The name of the template.
	 * @param   integer  $clientId  The application client ID the template applies to
	 *
	 * @return  string  The html string
	 *
	 * @since   3.4
	 *
	 * @deprecated  5.0  Use \Joomla\Component\Templates\Administrator\Helper\TemplatesHelper::getThumbnailModal() instead
	 */
	public function thumbModal($template, $clientId = 0)
	{
		$client = ApplicationHelper::getClientInfo($clientId);
		$basePath = $client->path . '/templates/' . $template;
		$baseUrl = ($clientId == 0) ? Uri::root(true) : Uri::root(true) . '/administrator';
		$thumb = $basePath . '/template_thumbnail.png';
		$preview = $basePath . '/template_preview.png';
		$html = '';

		if (file_exists($thumb) && file_exists($preview))
		{
			$preview = $baseUrl . '/templates/' . $template . '/template_preview.png';
			$footer  = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('JTOOLBAR_CLOSE') . '</button>';

			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				$template . '-Modal',
				array(
					'title'  => Text::sprintf('COM_TEMPLATES_SCREENSHOT', ucfirst($template)),
					'height' => '500px',
					'width'  => '800px',
					'footer' => $footer,
				),
				$body = '<div><img src="' . $preview . '" style="max-width:100%" alt="' . $template . '"></div>'
			);
		}

		return $html;
	}

	/**
	 * Display the thumb for the template.
	 *
	 * @param   object   $template  The name of the template.
	 *
	 * @return  string  The html string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getThumbnail($template)
	{
		$html   = '';
		$client = ApplicationHelper::getClientInfo($template->client_id);

		if ((isset($template->xmldata->inheritable) && (bool) $template->xmldata->inheritable) || (isset($template->xmldata->parent) && (string) $template->xmldata->parent !== ''))
		{
			if (isset($template->xmldata->parent) && (string) $template->xmldata->parent !== '' && file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_thumbnail.png'))
			{
				$html = HTMLHelper::_('image', Uri::root(true) . 'media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'));

				if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_preview.png')) {
					$html = '<button type="button" data-bs-target="#' . $template->name . '-Modal" class="thumbnail" data-bs-toggle="modal" title="' . Text::_('COM_TEMPLATES_CLICK_TO_ENLARGE') . '">' . $html . '</button>';
				}
			}
			elseif (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->name . '/images/template_thumbnail.png'))
			{
				$html = HTMLHelper::_('image', Uri::root(true) . 'media/templates/' . $client->name . '/' . $template->name . '/images/template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'));

				if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->name . '/images/template_preview.png')) {
					$html = '<button type="button" data-bs-target="#' . $template->name . '-Modal" class="thumbnail" data-bs-toggle="modal" title="' . Text::_('COM_TEMPLATES_CLICK_TO_ENLARGE') . '">' . $html . '</button>';
				}
			}
			else
			{
				// @todo some image fallback
				$html = HTMLHelper::_('image', 'media/system/images/template_no_thumb.png', Text::_('COM_TEMPLATES_PREVIEW'));
			}

		}
		elseif (file_exists(JPATH_ROOT . $client->path . '/templates/' . $template->name . '/template_thumbnail.png'))
		{
			$html = HTMLHelper::_('image', (($template->client_id == 0) ? Uri::root(true) : Uri::root(true) . '/administrator') . '/templates/' . $template->name . '/template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'));

			if (file_exists(JPATH_ROOT . $client->path . '/templates/' . $template->name . '/images/template_preview.png')) {
				$html = '<button type="button" data-bs-target="#' . $template->name . '-Modal" class="thumbnail" data-bs-toggle="modal" title="' . Text::_('COM_TEMPLATES_CLICK_TO_ENLARGE') . '">' . $html . '</button>';
			}
		}
		else
		{
			// @todo some image fallback
			$html = HTMLHelper::_('image', 'media/system/images/template_no_thumb.png', Text::_('COM_TEMPLATES_PREVIEW'));
		}

		return $html;
	}


	/**
	 * Renders the html for the modal linked to thumb.
	 *
	 * @param   object   $template  The name of the template.
	 *
	 * @return  string  The html string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getThumbnailModal($template)
	{
		$html    = '';
		$thumb   = '';
		$preview = '';
		$client  = ApplicationHelper::getClientInfo($template->client_id);

		if ((isset($template->xmldata->inheritable) && (bool) $template->xmldata->inheritable) || (isset($template->xmldata->parent) && (string) $template->xmldata->parent !== ''))
		{
			if (isset($template->xmldata->parent) && (string) $template->xmldata->parent !== '' && file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_thumbnail.png'))
			{
				$thumb = 'media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_thumbnail.png';

				if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_preview.png'))
				{
					$preview = 'media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_preview.png';
				}
			}
			elseif (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->name . '/images/template_thumbnail.png'))
			{
				$thumb = 'media/templates/' . $client->name . '/' . $template->name . '/images/template_thumbnail.png';

				if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->name . '/images/template_preview.png'))
				{
					$preview = 'media/templates/' . $client->name . '/' . $template->name . '/images/template_preview.png';
				}
			}
		}
		elseif (file_exists(JPATH_ROOT . $client->path . '/templates/' . $template->name . '/template_thumbnail.png'))
		{
			$thumb = 'templates/' . $template->name . '/template_thumbnail.png';

			if (file_exists(JPATH_ROOT . $client->path . '/templates/' . $template->name . '/images/template_preview.png'))
			{
				$preview = $client->path . '/templates/' . $template->name . '/images/template_preview.png';
			}
		}

		if ($thumb !== '' && $preview !== '')
		{
			$footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
				. Text::_('JTOOLBAR_CLOSE') . '</button>';

			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				$template->name . '-Modal',
				array(
					'title'  => Text::sprintf('COM_TEMPLATES_SCREENSHOT', ucfirst($template->name)),
					'height' => '500px',
					'width'  => '800px',
					'footer' => $footer,
				),
				$body = '<div><img src="' . Uri::root() . $preview . '" style="max-width:100%" alt="' . $template->name . '"></div>'
			);
		}

		return $html;
	}
}
