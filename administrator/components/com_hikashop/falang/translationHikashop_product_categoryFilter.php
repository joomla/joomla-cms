<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
defined('_JEXEC') or die;

class translationHikashop_product_categoryFilter extends translationFilter
{
	public function __construct ($contentElement){
		$this->filterNullValue="-1";
		$this->filterType="hikashop_product_category";
    $this->filterField =  $contentElement->getFilter("hikashop_product_category");
		parent::__construct($contentElement);
	}

	public function _createFilter(){
		if (!$this->filterField) return "";
		$filter="";
    if (isset($this->filter_value) && strlen($this->filter_value) > 0  && $this->filter_value!=$this->filterNullValue){
			$db = JFactory::getDBO();
			$query = 'SELECT pc.product_id FROM #__hikashop_product_category AS pc WHERE pc.category_id='.$this->filter_value;
      $db->setQuery($query);
      $product_ids = $db->loadObjectList();

      $idstring = '';
      foreach($product_ids as $product_id){
        if (strlen($idstring)>0) $idstring.=',';
        $idstring.=$product_id->product_id;
      }
      $filter = "c.product_id IN($idstring)";
		}
		return $filter;
	}

  function _createfilterHTML(){
    $db = JFactory::getDBO();

    if (!$this->filterField) return "";

    $allCategoryOptions = array();

    $query = 'SELECT DISTINCT c.category_id, c.category_name FROM #__hikashop_category AS c, 
              #__'.$this->tableName.' as p, #__hikashop_product_category AS pc  
              WHERE p.product_id=pc.product_id AND pc.'.$this->filterField.'=c.category_id ORDER BY c.category_name';

    $db->setQuery($query);
    $category_list = $db->loadObjectList();
    foreach($category_list as $k=>$category){
      $categoryOptions[$k] = JHTML::_('select.option', $category->category_id,$category->category_name);
    }

    if (!FALANG_J30) {
      $allCategoryOptions[-1] = JHTML::_('select.option', '-1',JText::_('COM_FALANG_ALL_CATEGORIES') );
    }
    $options = array_merge($allCategoryOptions, $categoryOptions);

    $categoryList=array();

    if (FALANG_J30) {
        $categoryList["title"]= JText::_('COM_FALANG_SELECT_CATEGORY');
        $categoryList["position"] = 'sidebar';
        $categoryList["name"]= 'hikashop_product_category_filter_value';
        $categoryList["type"]= 'hikashop_product_category';
        $categoryList["options"] = $options;
        $categoryList["html"] = JHTML::_('select.genericlist', $options, 'hikashop_product_category_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
    } else {
        $categoryList["title"]= JText::_('COM_FALANG_CATEGORY_FILTER');
        $categoryList["html"] = JHTML::_('select.genericlist', $options, 'hikashop_product_category_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
    }

    return $categoryList;

  }
}
?>
