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

class WFPreviewPlugin extends WFEditorPlugin
{
    /**
     * Constructor activating the default information of the class.
     */
    public function __construct()
    {
        parent::__construct();

        $request = WFRequest::getInstance();
        // Setup plugin XHR callback functions
        $request->setRequest(array($this, 'showPreview'));

        $this->execute();
    }

    /**
     * Display Preview content.
     */
    public function showPreview()
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        // reset document type
        $document = JFactory::getDocument();
        $document->setType('html');

        // required by module loadposition
        jimport('joomla.application.module.helper');

        // load paramter class
        jimport('joomla.html.parameter');

        // get post data
        $data = $app->input->post->get('data', '', 'RAW');

        // cleanup data
        $data = preg_replace(array('#<!DOCTYPE([^>]+)>#i', '#<(head|title|meta)([^>]*)>([\w\W]+)<\/1>#i', '#<\/?(html|body)([^>]*)>#i'), '', rawurldecode($data));

        // create params registry object
        $params = new JRegistry();
        $params->loadString("");

        // create context
        $context = "";

        $extension_id = $app->input->getInt('extension_id');
        $extension = JTable::getInstance('extension');

        if ($extension->load($extension_id)) {
            $option = $extension->element;

            // process attribs (com_content etc.)
            if ($extension->attribs) {
                $params->loadString($extension->attribs);
            } else {
                $params->loadString($extension->params);
            }

            $context = $option . '.article';
        }

        $article = JTable::getInstance('content');

        $article->id = 0;
        $article->created_by = $user->get('id');
        $article->parameters = new JRegistry();
        $article->text = $data;

        // allow this to be skipped as some plugins can cause FATAL errors.
        if ((bool) $this->getParam('process_content', 1)) {
            $page = 0;

            JPluginHelper::importPlugin('system');
            JPluginHelper::importPlugin('content');

            // load content router
            require_once JPATH_SITE . '/components/com_content/helpers/route.php';

            // set error reporting off to produce empty string on Fatal error
            error_reporting(0);

            $app->triggerEvent('onContentPrepare', array($context, &$article, &$params, $page));
        }

        $this->processURLS($article);

        return $article->text;
    }

    /**
     * Convert URLs.
     *
     * @param object $article Article object
     */
    private function processURLS(&$article)
    {
        $base = JURI::root(true) . '/';
        $buffer = $article->text;

        $protocols = '[a-zA-Z0-9]+:'; //To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
        $regex = '#(src|href|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
        $buffer = preg_replace($regex, "$1=\"$base\$2\"", $buffer);
        $regex = '#(onclick="window.open\(\')(?!/|' . $protocols . '|\#)([^/]+[^\']*?\')#m';
        $buffer = preg_replace($regex, '$1' . $base . '$2', $buffer);

        // ONMOUSEOVER / ONMOUSEOUT
        $regex = '#(onmouseover|onmouseout)="this.src=([\']+)(?!/|' . $protocols . '|\#|\')([^"]+)"#m';
        $buffer = preg_replace($regex, '$1="this.src=$2' . $base . '$3$4"', $buffer);

        // Background image
        $regex = '#style\s*=\s*[\'\"](.*):\s*url\s*\([\'\"]?(?!/|' . $protocols . '|\#)([^\)\'\"]+)[\'\"]?\)#m';
        $buffer = preg_replace($regex, 'style="$1: url(\'' . $base . '$2$3\')', $buffer);

        // OBJECT <field name="xx", value="yy"> -- fix it only inside the <param> tag
        $regex = '#(<param\s+)name\s*=\s*"(movie|src|url)"[^>]\s*value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
        $buffer = preg_replace($regex, '$1name="$2" value="' . $base . '$3"', $buffer);

        // OBJECT <field value="xx", name="yy"> -- fix it only inside the <param> tag
        $regex = '#(<param\s+[^>]*)value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"\s*name\s*=\s*"(movie|src|url)"#m';
        $buffer = preg_replace($regex, '<field value="' . $base . '$2" name="$3"', $buffer);

        // OBJECT data="xx" attribute -- fix it only in the object tag
        $regex = '#(<object\s+[^>]*)data\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
        $buffer = preg_replace($regex, '$1data="' . $base . '$2"$3', $buffer);

        $article->text = $buffer;
    }
}
