<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

jimport('joomla.plugin.plugin');
jimport( 'joomla.event.plugin' );
jimport('joomla.registry.registry');

require_once __DIR__.'/platform/platform.php';
require_once __DIR__. '/platform/media.php';
require_once __DIR__.'/platform/blog.php';

use HelixUltimate\Platform as Platform;
use HelixUltimate\Media\Media as Media;
use HelixUltimate\Blog\Blog as Blog;

class  plgSystemHelixultimate extends JPlugin
{
    protected $autoloadLanguage = true;

    protected $app;

    public function onAfterDispatch()
    {
        if (!$this->app->isAdmin())
        {
            $activeMenu = $this->app->getMenu()->getActive();

            if (is_null($activeMenu)) $template_style_id = 0;
            else $template_style_id = (int) $activeMenu->template_style_id;

            if ( $template_style_id > 0 )
            {
                JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');
                $style = JTable::getInstance('Style', 'TemplatesTable');
                $style->load($template_style_id);
                if ( !empty($style->template) ) $this->app->setTemplate($style->template, $style->params);
            }
        }
    }

    public function onContentPrepareForm($form, $data)
    {
        $doc = \JFactory::getDocument();
        $plg_path = \JURI::root(true).'/plugins/system/helixultimate';
        \JForm::addFormPath(\JPATH_PLUGINS.'/system/helixultimate/params');

        if ($form->getName() == 'com_menus.item')
        {
            \JHtml::_('jquery.framework');
            \JHtml::_('jquery.ui', array('core', 'more', 'sortable'));

            $doc->addStyleSheet($plg_path.'/assets/css/font-awesome.min.css');
            $doc->addStyleSheet($plg_path.'/assets/css/admin/modal.css');
            $doc->addStyleSheet($plg_path.'/assets/css/admin/menu.generator.css');
            $doc->addScript($plg_path.'/assets/js/admin/modal.js');
            $doc->addScript( $plg_path. '/assets/js/admin/menu.generator.js' );

            $form->loadFile('megamenu', false);
        }

        //Article Post format
        if ($form->getName()=='com_content.article')
        {
            $doc->addStyleSheet($plg_path.'/assets/css/font-awesome.min.css');
            $tpl_path = \JPATH_ROOT . '/templates/' . $this->getTemplateName()->template;

            \JHtml::_('jquery.framework');
            \JHtml::_('jquery.token');
            $doc->addStyleSheet( $plg_path. '/assets/css/admin/blog-options.css' );
            $doc->addScript( $plg_path. '/assets/js/admin/blog-options.js' );

            if (\JFile::exists( $tpl_path . '/blog-options.xml' ))
            {
                \JForm::addFormPath($tpl_path);
            }
            else
            {
                \JForm::addFormPath(\JPATH_PLUGINS . '/system/helixultimate/params');
            }
            $form->loadFile('blog-options', false);
        }
    }

    // Live Update system
    public function onExtensionAfterSave($option, $data)
    {
        if ($option == 'com_templates.style' && !empty($data->id))
        {
            $params = new JRegistry;
            $params->loadString($data->params);

            $email       = $params->get('joomshaper_email');
            $license_key = $params->get('joomshaper_license_key');
            $template    = trim($data->template);

            if(!empty($email) and !empty($license_key) )
            {
                $extra_query = 'joomshaper_email=' . urlencode($email);
                $extra_query .='&amp;joomshaper_license_key=' . urlencode($license_key);

                $db = JFactory::getDbo();
                $fields = array(
                    $db->quoteName('extra_query') . '=' . $db->quote($extra_query),
                    $db->quoteName('last_check_timestamp') . '=0'
                );

                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__update_sites'))
                    ->set($fields)
                    ->where($db->quoteName('name').'='.$db->quote($template));
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    public function onAfterRoute()
    {
        $option     = $this->app->input->get('option','');
        $helix      = $this->app->input->get('helix','');
        $view       = $this->app->input->get('view','');
        $task       = $this->app->input->get('task','');
        $request    = $this->app->input->get('request','');
        $action     = $this->app->input->get('action', '');
        $id         = $this->app->input->get('id',NULL,'INT');

        $doc = JFactory::getDocument();
        if ($this->app->isAdmin())
        {
            if ($option == 'com_ajax' && $helix == 'ultimate')
            {

                if ($task == 'export' && $id) {

                    $template = $this->getTemplateName($id);
    
                    header( 'Content-Description: File Transfer' );
                    header( 'Content-type: application/txt' );
                    header( 'Content-Disposition: attachment; filename="' . $template->template . '_settings_' . date( 'd-m-Y' ) . '.json"' );
                    header( 'Content-Transfer-Encoding: binary' );
                    header( 'Expires: 0' );
                    header( 'Cache-Control: must-revalidate' );
                    header( 'Pragma: public' );
                    echo $template->params;
                    exit;
                }
                else if($request == '')
                {
                    Platform::loadFrameworkSystem();
                }
                
                \JFactory::getApplication()->triggerEvent('onAfterRespond');
                die;
            }
        }

        if($this->app->isSite())
        {
            $option     = $this->app->input->get('option','');
            $helix      = $this->app->input->get('helix','');
            $request    = $this->app->input->get('request','');
            $action     = $this->app->input->get('action','');

            if ($option == 'com_ajax' && $helix == 'ultimate' && $request == 'task' && $action != '')
            {
                switch ($action)
                {
                    case 'upload-blog-image':
                        Blog::upload_image();
                        break;

                    case 'remove-blog-image':
                        Blog::remove_image();
                        break;

                    case 'view-media':
                        Media::getFolders();
                        break;

                    case 'delete-media':
                        Media::deleteMedia();
                        break;

                    case 'upload-media':
                        Media::uploadMedia();
                        break;
                };
            }
        }
    }

    public function onAfterRespond()
    {
        if($this->app->isAdmin()){
            $platform = new Platform;
            $platform->initialize();
        }
    }

    private function getTemplateName($id = '')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__template_styles'));
        $query->where($db->quoteName('client_id') . ' = 0');
        
        if (!$id) {
            $query->where($db->quoteName('home') . ' = 1');
        } else {
            $query->where($db->quoteName('id') . ' = ' . $db->quote( $id ));
        }

        $db->setQuery($query);

        return $db->loadObject();
    }
}
