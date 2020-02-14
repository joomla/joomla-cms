<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

namespace HelixUltimate;

defined ('_JEXEC') or die();

require_once __DIR__.'/options.php';
require_once __DIR__.'/request.php';
require_once __DIR__.'/helper.php';

use HelixUltimate\Helper\Helper as Helper;
use HelixUltimate\Options as Options;

class Platform
{

    protected $app;

    protected $option;

    protected $helix;

    protected $view;

    protected $id;

    protected $request;

    protected $version;

    protected $user = array();

    protected $permission = false;

    public function __construct()
    {
        $this->user = \JFactory::getUser();
        $this->app  = \JFactory::getApplication();
        $input = $this->app->input;
        $this->version    = Helper::getVersion();

        $this->option     = $input->get('option','');
        $this->helix      = $input->get('helix','');
        $this->view       = $input->get('view','');
        $this->id         = $input->get('id',NULL);
        $this->request    = $input->get('request','');
        $this->userTmplEditPermission();
    }


    public function initialize()
    {
        if( $this->option == 'com_ajax' && $this->helix == 'ultimate' && $this->request == 'task')
        {
            $request = new Request;
            $request->initialize();
        }
        elseif( $this->option == 'com_ajax' && $this->helix == 'ultimate' && $this->id && $this->permission)
        {
            $frmkHTML     = $this->frameworkFormHTMLStart();
            $frmkOptions  = new Options();
            $frmkHTML    .= $frmkOptions->renderBuilderSidebar();
            $frmkHTML    .= $this->frameworkFormHTMLEnd();

            echo $frmkHTML;
        }
    }

    private function frameworkFormHTMLStart()
    {
        $htmlView  = '<div id="helix-ultimate">';
        $htmlView .= '<div class="helix-ultimate-sidebar">';
        $htmlView .= '<div class="helix-ultimate-logo">';
        $htmlView .= '<img src="'.\JURI::root(true).'/plugins/system/helixultimate/assets/images/helix-logo.svg" alt="Helix Ultimate by JoomShaper"/>';
        $htmlView .= '</div>';
        $htmlView .= '<a class="action-helix-ultimate-exit" href="'. \JRoute::_('index.php?option=com_templates') .'"><span class="fa fa-times"></span></a>';
        $htmlView .= '<div class="helix-ultimate-options-wrap">';

        return $htmlView;
    }

    private function frameworkFormHTMLEnd()
    {
        $app = \JFactory::getApplication();
        $id = (int) $app->input->get('id', 0, 'INT');
        $style = Helper::getTemplateStyle($id);

        $htmlView  = '</div>';

        $htmlView .= '<div class="helix-ultimate-footer clearfix">';
        $htmlView .= '<div class="helix-ultimate-copyright">Helix Ultimate Framework '. $this->version .'<br />By <a target="_blank" href="https://www.joomshaper.com">JoomShaper</a></div>';
        $htmlView .= '<div class="helix-ultimate-action"><button class="btn btn-primary action-save-template" data-id="'. $this->id .'" data-view="'. $this->view .'"><span class="fa fa-save"></span> Save</button></div>';
        $htmlView .= '</div>';

        $htmlView .= '</div>';

        $htmlView .= '<div class="helix-ultimate-preview">';
        $htmlView .= '<iframe id="helix-ultimate-template-preview" src="'.\JURI::root(true).'/index.php?template='. $style->template .'" style="width: 100%; height: 100%;"></iframe>';
        $htmlView .= '</div>';
        $htmlView .= '</div>';

        return $htmlView;
    }


    private function userTmplEditPermission()
    {
        if ($this->user->id)
        {
            if ($this->user->authorise('core.edit','com_templates'))
            {
                $this->permission = true;
            }
        }
    }

    public static function loadFrameworkSystem()
    {
        $app = \JFactory::getApplication();
        $doc = \JFactory::getDocument();
        $style_id = (int) $app->input->get('id', 0, 'INT');
        $style = Helper::getTemplateStyle($style_id);
        $template = $style->template;
        $helix_plg_url = \JURI::root(true) . '/plugins/system/helixultimate';

        \JFactory::getLanguage()->load('tpl_' . $template, \JPATH_SITE, null, true);;

        $doc->setTitle("Helix Ultimate Framework");
        $doc->addFavicon( $helix_plg_url . '/assets/images/favicon.ico');
        
        $doc->addScriptDeclaration('var helixUltimateStyleId = ' . $style_id . ';');
        
        \JHtml::_('jquery.ui', array('core', 'sortable'));
        \JHtml::_('bootstrap.framework');
        \JHtml::_('behavior.formvalidator');
        \JHtml::_('behavior.keepalive');
        \JHtml::_('formbehavior.chosen', 'select');
        \JHtml::_('behavior.colorpicker');
        \JHtml::_('jquery.token');
        
        
        $doc->addStyleSheet($helix_plg_url.'/assets/css/admin/helix-ultimate.css');
        $doc->addStyleSheet($helix_plg_url.'/assets/css/admin/modal.css');
        $doc->addStyleSheet($helix_plg_url.'/assets/css/font-awesome.min.css');
        
        $doc->addScript( $helix_plg_url.'/assets/js/admin/webfont.js' );
        $doc->addScript( $helix_plg_url.'/assets/js/admin/modal.js' );
        $doc->addScript( $helix_plg_url.'/assets/js/admin/layout.js' );
        $doc->addScript( $helix_plg_url. '/assets/js/admin/media.js' );
        $doc->addScript( $helix_plg_url. '/assets/js/admin/helix-ultimate.js' );

        echo $doc->render(false,[
            'file' => 'component.php',
            'template' => 'HelixUltimate',
        ]);
    }
}
