<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.application.component.modeladmin');

class SppagebuilderModelPage extends JModelAdmin {

    public function getTable($type = 'Page', $prefix = 'SppagebuilderTable', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true) {
        $form = $this->loadForm('com_sppagebuilder.page', 'page',array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        $jinput = JFactory::getApplication()->input;

    		$id = $jinput->get('id', 0);

    		// Determine correct permissions to check.
    		if ($this->getState('page.id'))
    		{
    			$id = $this->getState('page.id');

    			// Existing record. Can only edit in selected categories.
    			$form->setFieldAttribute('catid', 'action', 'core.edit');

    			// Existing record. Can only edit own pages in selected categories.
    			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
    		}
    		else
    		{
    			// New record. Can only create in selected categories.
    			$form->setFieldAttribute('catid', 'action', 'core.create');
    		}

    		$user = JFactory::getUser();

            // Modify the form based on Edit State access controls.
    		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_sppagebuilder.page.' . (int) $id))
    			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_sppagebuilder')))
    		{
    			// Disable fields for display.
    			$form->setFieldAttribute('published', 'disabled', 'true');

    			// Disable fields while saving.
    			// The controller has already verified this is an page you can edit.
    			$form->setFieldAttribute('published', 'filter', 'unset');
    		}

        return $form;
    }

    public function getItem($pk = NULL) {
        if ($item = parent::getItem($pk))
		{
            $app = JApplication::getInstance('site');
            $router = $app->getRouter();
            $item = parent::getItem($pk);
            // get menu id
            $Itemid = SppagebuilderHelper::getMenuId($item->id);
            // Get item language code
            $lang_code = (isset($item->language) && $item->language && explode('-',$item->language)[0])? explode('-',$item->language)[0] : '';
            // check language filter plugin is enable or not
            $enable_lang_filter = JPluginHelper::getPlugin('system', 'languagefilter');
            // get joomla config
            $conf = JFactory::getConfig();

            // Preview URL
            $item->link = 'index.php?option=com_sppagebuilder&task=page.edit&id=' . $item->id;
            $preview = 'index.php?option=com_sppagebuilder&view=page&id=' . $item->id . $Itemid;
            $sefURI = str_replace('/administrator', '', $router->build($preview));
            if( $lang_code && $lang_code !== '*' && $enable_lang_filter && $conf->get('sef') ){
                $sefURI = str_replace('/index.php/', '/index.php/' . $lang_code . '/', $sefURI);
            } elseif($lang_code && $lang_code !== '*') {
                $sefURI = $sefURI . '&lang=' . $lang_code;
            }
            $item->preview = $sefURI;

            // Frontend Editing URL
            $front_link = 'index.php?option=com_sppagebuilder&view=form&tmpl=componenet&layout=edit&id=' . $item->id . $Itemid;
            $sefURI = str_replace('/administrator', '', $router->build($front_link));

            if( $lang_code && $lang_code !== '*' && $enable_lang_filter && $conf->get('sef') ){
                $sefURI = str_replace('/index.php/', '/index.php/' . $lang_code . '/', $sefURI);
            } elseif($lang_code && $lang_code !== '*') {
                $sefURI = $sefURI . '&lang=' . $lang_code;
            }
            $item->frontend_edit = $sefURI;
        }

        return $item;
        
    }

    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_sppagebuilder.edit.page.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_sppagebuilder.page', $data);

        return $data;
    }

    protected function canEditState($item) {
        return \JFactory::getUser()->authorise('core.edit.state', 'com_sppagebuilder.page.' . $item->id);
	}

    public function save($data) {
        $app = JFactory::getApplication();
        if ($app->input->get('task') == 'save2copy') {
            $data['title'] = $this->pageGenerateNewTitle( $data['title'] );
        }

        $data['created_by'] = $this->checkExistingUser($data['created_by']);

        parent::save($data);
        return true;
    }

    protected function checkExistingUser($id) {
        $currentUser = JFactory::getUser();
        $user_id = $currentUser->id;

        if($id) {
            $user = JFactory::getUser($id);
            if($user->id) {
                $user_id = $id;
            }
        }

        return $user_id;
    }

    public static function pageGenerateNewTitle($title ) {
        $pageTable = JTable::getInstance('Page', 'SppagebuilderTable');

        while( $pageTable->load(array('title'=>$title)) ) {
            $m = null;
            if (preg_match('#\((\d+)\)$#', $title, $m)) {
                $title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
            } else {
                $title .= ' (2)';
            }
        }

        return $title;
    }

    public static function getPageInfoById($pageId){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select( array('a.*') );
		$query->from($db->quoteName('#__sppagebuilder', 'a'));
		$query->where($db->quoteName('a.id')." = ".$db->quote($pageId));
		$db->setQuery($query);
		$result = $db->loadObject();
		
		return $result;
	}

    public function getMySections() {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      $query->select($db->quoteName(array('id', 'title', 'section')));
      $query->from($db->quoteName('#__sppagebuilder_sections'));
      //$query->where($db->quoteName('profile_key') . ' LIKE '. $db->quote('\'custom.%\''));
      $query->order('id ASC');
      $db->setQuery($query);
      $results = $db->loadObjectList();
      return json_encode($results);
    }

    public function deleteSection($id){
      $db = JFactory::getDbo();

      $query = $db->getQuery(true);

      // delete all custom keys for user 1001.
      $conditions = array(
          $db->quoteName('id') . ' = '.$id
      );

      $query->delete($db->quoteName('#__sppagebuilder_sections'));
      $query->where($conditions);

      $db->setQuery($query);

      return $db->execute();
    }

    public function saveSection($title, $section){
      $db = JFactory::getDbo();
      $user = JFactory::getUser();
      $obj = new stdClass();
      $obj->title = $title;
      $obj->section = $section;
      $obj->created = JFactory::getDate()->toSql();
      $obj->created_by = $user->get('id');

      $db->insertObject('#__sppagebuilder_sections', $obj);

      return $db->insertid();
    }
}
