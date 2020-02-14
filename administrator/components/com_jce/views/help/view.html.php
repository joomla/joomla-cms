<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

class JceViewHelp extends JViewLegacy
{
    public function display($tpl = null)
    {
        $model = $this->getModel();
        $language = $model->getLanguage();
        $lang = JFactory::getLanguage();

        $app = JFactory::getApplication();

        $document = JFactory::getDocument();

        $section = $app->input->getWord('section');
        $category = $app->input->getWord('category');
        $article = $app->input->getWord('article');

        $params = JComponentHelper::getParams('com_jce');

        $registry = new JRegistry($params);
        $url = $registry->get('preferences.help.url', 'https://www.joomlacontenteditor.net');
        $method = $registry->get('preferences.help.method', 'reference');
        $pattern = $registry->get('preferences.help.pattern', '');

        switch ($method) {
            default:
            case 'reference':
                $url .= '/index.php?option=com_content&view=article&tmpl=component&print=1&mode=inline&task=findkey&lang=' . $language . '&keyref=';
                break;
            case 'xml':
                break;
            case 'sef':
                break;
        }

        $this->model = $model;
        
        $key = array();

        if ($section) {
            $key[] = $section;
            if ($category) {
                $key[] = $category;
                if ($article) {
                    $key[] = $article;
                }
            }
        }

        $options = array(
            'url' => $url,
            'key' => $key,
            'pattern' => $pattern,
        );

        JHtml::_('jquery.framework');

        $document->addScript('components/com_jce/media/js/help.min.js');
        $document->addStyleSheet('components/com_jce/media/css/help.min.css');
        $document->addScriptDeclaration('jQuery(document).ready(function($){Wf.Help.init(' . json_encode($options) . ');});');

        parent::display($tpl);
    }
}
