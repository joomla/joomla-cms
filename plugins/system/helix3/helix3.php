<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

jimport('joomla.plugin.plugin');
jimport( 'joomla.event.plugin' );
jimport('joomla.registry.registry');

if(!class_exists('Helix3')) {
  require_once (__DIR__ . '/core/helix3.php');
}

class  plgSystemHelix3 extends JPlugin
{

    protected $autoloadLanguage = true;

    // Copied style
    function onAfterDispatch() {

        if(  !JFactory::getApplication()->isAdmin() ) {

            $activeMenu = JFactory::getApplication()->getMenu()->getActive();

            if(is_null($activeMenu)) $template_style_id = 0;
            else $template_style_id = (int) $activeMenu->template_style_id;
            if( $template_style_id > 0 ){

                JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');
                $style = JTable::getInstance('Style', 'TemplatesTable');
                $style->load($template_style_id);

                if( !empty($style->template) ) JFactory::getApplication()->setTemplate($style->template, $style->params);
            }
        }
    }

    function onContentPrepareForm($form, $data) {

        $doc = JFactory::getDocument();
        $plg_path = JURI::root(true).'/plugins/system/helix3';
        JForm::addFormPath(JPATH_PLUGINS.'/system/helix3/params');

        if ($form->getName()=='com_menus.item') { //Add Helix menu params to the menu item
            JHtml::_('jquery.framework');
            $data = (array)$data;

            if($data['id'] && $data['parent_id'] == 1) {
                JHtml::_('jquery.ui', array('core', 'more', 'sortable'));
                $doc->addScript($plg_path.'/assets/js/jquery-ui.draggable.min.js');
                $doc->addStyleSheet($plg_path.'/assets/css/bootstrap.css');
                $doc->addStyleSheet($plg_path.'/assets/css/font-awesome.min.css');
                $doc->addStyleSheet($plg_path.'/assets/css/modal.css');
                $doc->addStyleSheet($plg_path.'/assets/css/menu.generator.css');
                $doc->addScript($plg_path.'/assets/js/modal.js');
                $doc->addScript( $plg_path. '/assets/js/menu.generator.js' );
                $form->loadFile('menu-parent', false);

            } else {
                $form->loadFile('menu-child', false);
            }

            $form->loadFile('page-title', false);

        }

        //Article Post format
        if ($form->getName()=='com_content.article') {
            JHtml::_('jquery.framework');
            $doc->addStyleSheet($plg_path.'/assets/css/font-awesome.min.css');
            $doc->addScript($plg_path.'/assets/js/post-formats.js');

            $tpl_path = JPATH_ROOT . '/templates/' . $this->getTemplateName();

            if(JFile::exists( $tpl_path . '/post-formats.xml' )) {
                JForm::addFormPath($tpl_path);
            } else {
                JForm::addFormPath(JPATH_PLUGINS . '/system/helix3/params');
            }

            $form->loadFile('post-formats', false);
        }

    }


    // Live Update system
    public function onExtensionAfterSave($option, $data) {

        if ($option == 'com_templates.style' && !empty($data->id)) {

            $params = new JRegistry;
            $params->loadString($data->params);

            $email       = $params->get('joomshaper_email');
            $license_key = $params->get('joomshaper_license_key');
            $template = trim($data->template);

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
        $japps = JFactory::getApplication();

        if ( $japps->isAdmin() )
        {
            $user = JFactory::getUser();

            if( !in_array( 8, $user->groups ) ){
                return false;
            }

            $inputs = JFactory::getApplication()->input;

            $option         = $inputs->get ( 'option', '' );
            $id             = $inputs->get ( 'id', '0', 'INT' );
            $helix3task     = $inputs->get ( 'helix3task' ,'' );

            if ( strtolower( $option ) == 'com_templates' && $id && $helix3task == "export" )
            {
               $db = JFactory::getDbo();
               $query = $db->getQuery(true);

               $query
                    ->select( '*' )
                    ->from( $db->quoteName( '#__template_styles' ) )
                    ->where( $db->quoteName( 'id' ) . ' = ' . $db->quote( $id ) . ' AND ' . $db->quoteName( 'client_id' ) . ' = 0' );

                $db->setQuery( $query );

                $result = $db->loadObject();

                header( 'Content-Description: File Transfer' );
                header( 'Content-type: application/txt' );
                header( 'Content-Disposition: attachment; filename="' . $result->template . '_settings_' . date( 'd-m-Y' ) . '.json"' );
                header( 'Content-Transfer-Encoding: binary' );
                header( 'Expires: 0' );
                header( 'Cache-Control: must-revalidate' );
                header( 'Pragma: public' );

                echo $result->params;

                exit;
            }
        }

    }

    private function getTemplateName()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('template')));
        $query->from($db->quoteName('#__template_styles'));
        $query->where($db->quoteName('client_id') . ' = 0');
        $query->where($db->quoteName('home') . ' = 1');
        $db->setQuery($query);

        return $db->loadObject()->template;
    }

    function onAfterRender() {
      $app = JFactory::getApplication();

  		if ($app->isAdmin())
      {
  			return;
  		}
      $body = JResponse::getBody();
  		$preset = Helix3::Preset();

  		$body = str_replace('{helix_preset}', $preset, $body);

  		JResponse::setBody($body);
    }
}
