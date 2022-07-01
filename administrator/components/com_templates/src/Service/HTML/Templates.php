<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Service\HTML;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;

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
     * @param   string|object  $template  The name of the template or the template object.
     * @param   integer        $clientId  The application client ID the template applies to
     *
     * @return  string  The html string
     *
     * @since   1.6
     *
     * @deprecated  5.0  The argument $template should be object and $clientId will be removed
     */
    public function thumb($template, $clientId = 0)
    {
        if (is_string($template)) {
            return HTMLHelper::_('image', 'template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'), [], true, -1);
        }

        $client = ApplicationHelper::getClientInfo($template->client_id);

        if (!isset($template->xmldata)) {
            $template->xmldata = TemplatesHelper::parseXMLTemplateFile($client->id === 0 ? JPATH_ROOT : JPATH_ROOT . '/administrator', $template->name);
        }

        if ((isset($template->xmldata->inheritable) && (bool) $template->xmldata->inheritable) || isset($template->xmldata->parent)) {
            if (isset($template->xmldata->parent) && (string) $template->xmldata->parent !== '' && file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_thumbnail.png')) {
                if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_preview.png')) {
                    $html = HTMLHelper::_('image', 'media/templates/' . $client->name . '/' . $template->element . '/images/template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'));
                    $html = '<button type="button" data-bs-target="#' . $template->element . '-Modal" class="thumbnail" data-bs-toggle="modal" title="' . Text::_('COM_TEMPLATES_CLICK_TO_ENLARGE') . '">' . $html . '</button>';
                } elseif ((file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_preview.png'))) {
                    $html = HTMLHelper::_('image', 'media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'));
                    $html = '<button type="button" data-bs-target="#' . $template->element . '-Modal" class="thumbnail" data-bs-toggle="modal" title="' . Text::_('COM_TEMPLATES_CLICK_TO_ENLARGE') . '">' . $html . '</button>';
                } else {
                    $html = HTMLHelper::_('image', 'template_thumb.svg', Text::_('COM_TEMPLATES_PREVIEW'), ['style' => 'width:200px; height:120px;']);
                }
            } elseif (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_thumbnail.png')) {
                $html = HTMLHelper::_('image', 'media/templates/' . $client->name . '/' . $template->element . '/images/template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'));

                if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_preview.png')) {
                    $html = '<button type="button" data-bs-target="#' . $template->element . '-Modal" class="thumbnail" data-bs-toggle="modal" title="' . Text::_('COM_TEMPLATES_CLICK_TO_ENLARGE') . '">' . $html . '</button>';
                }
            } else {
                $html = HTMLHelper::_('image', 'template_thumb.svg', Text::_('COM_TEMPLATES_PREVIEW'), ['style' => 'width:200px; height:120px;']);
            }
        } elseif (file_exists($client->path . '/templates/' . $template->element . '/template_thumbnail.png')) {
            $html = HTMLHelper::_('image', (($template->client_id == 0) ? Uri::root(true) : Uri::root(true) . '/administrator/') . '/templates/' . $template->element . '/template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'), [], false, -1);

            if (file_exists($client->path . '/templates/' . $template->element . '/template_preview.png')) {
                $html = '<button type="button" data-bs-target="#' . $template->element . '-Modal" class="thumbnail" data-bs-toggle="modal" title="' . Text::_('COM_TEMPLATES_CLICK_TO_ENLARGE') . '">' . $html . '</button>';
            }
        } else {
            $html = HTMLHelper::_('image', 'template_thumb.svg', Text::_('COM_TEMPLATES_PREVIEW'), ['style' => 'width:200px; height:120px;']);
        }

        return $html;
    }

    /**
     * Renders the html for the modal linked to thumb.
     *
     * @param   string|object  $template  The name of the template or the template object.
     * @param   integer        $clientId  The application client ID the template applies to
     *
     * @return  string  The html string
     *
     * @since   3.4
     *
     * @deprecated  5.0  The argument $template should be object and $clientId will be removed
     */
    public function thumbModal($template, $clientId = 0)
    {
        if (is_string($template)) {
            return HTMLHelper::_('image', 'template_thumbnail.png', Text::_('COM_TEMPLATES_PREVIEW'), [], true, -1);
        }

        $html    = '';
        $thumb   = '';
        $preview = '';
        $client  = ApplicationHelper::getClientInfo($template->client_id);

        if (!isset($template->xmldata)) {
            $template->xmldata = TemplatesHelper::parseXMLTemplateFile($client->id === 0 ? JPATH_ROOT : JPATH_ROOT . '/administrator', $template->name);
        }

        if ((isset($template->xmldata->inheritable) && (bool) $template->xmldata->inheritable) || isset($template->xmldata->parent)) {
            if (isset($template->xmldata->parent) && (string) $template->xmldata->parent !== '') {
                if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_thumbnail.png')) {
                    $thumb = ($template->client_id == 0 ? Uri::root(true) : Uri::root(true) . 'administrator') . 'media/templates/' . $client->name . '/' . $template->element . '/images/template_thumbnail.png';

                    if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_preview.png')) {
                        $preview = ($template->client_id == 0 ? Uri::root(true) : Uri::root(true) . '/administrator') . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_preview.png';
                    }
                } else {
                    $thumb = ($template->client_id == 0 ? Uri::root(true) : Uri::root(true) . 'administrator') . 'media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_thumbnail.png';

                    if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_preview.png')) {
                        $preview = ($template->client_id == 0 ? Uri::root(true) : Uri::root(true) . '/administrator') . '/media/templates/' . $client->name . '/' . (string) $template->xmldata->parent . '/images/template_preview.png';
                    }
                }
            } elseif (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_thumbnail.png')) {
                $thumb = ($template->client_id == 0 ? Uri::root(true) : Uri::root(true) . '/administrator') . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_thumbnail.png';

                if (file_exists(JPATH_ROOT . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_preview.png')) {
                    $preview = Uri::root(true) . '/media/templates/' . $client->name . '/' . $template->element . '/images/template_preview.png';
                }
            }
        } elseif (file_exists($client->path . '/templates/' . $template->element . '/template_thumbnail.png')) {
            $thumb = (($template->client_id == 0) ? Uri::root(true) : Uri::root(true) . 'administrator') . '/templates/' . $template->element . '/template_thumbnail.png';

            if (file_exists($client->path . '/templates/' . $template->element . '/template_preview.png')) {
                $preview = (($template->client_id == 0) ? Uri::root(true) : Uri::root(true) . '/administrator') . '/templates/' . $template->element . '/template_preview.png';
            }
        }

        if ($thumb !== '' && $preview !== '') {
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
                '<div><img src="' . $preview . '" class="mw-100" alt="' . $template->name . '"></div>'
            );
        }

        return $html;
    }
}
