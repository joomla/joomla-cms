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

class plgButtonHikashopproduct extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	function onDisplay($name, $asset='', $author='') {
		$extension = JRequest::getCmd('option');
		if(!in_array($extension, array('com_content', 'com_tz_portfolio', 'com_k2')))
			return;
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		if(version_compare(JVERSION,'1.6.0','<')){
			global $mainframe;
			$params = JComponentHelper::getParams('com_media');
			$acl = JFactory::getACL();
			switch ($params->get('allowed_media_usergroup'))
			{
				case '1':
					$acl->addACL( 'com_media', 'upload', 'users', 'publisher' );
					break;
				case '2':
					$acl->addACL( 'com_media', 'upload', 'users', 'publisher' );
					$acl->addACL( 'com_media', 'upload', 'users', 'editor' );
					break;
				case '3':
					$acl->addACL( 'com_media', 'upload', 'users', 'publisher' );
					$acl->addACL( 'com_media', 'upload', 'users', 'editor' );
					$acl->addACL( 'com_media', 'upload', 'users', 'author' );
					break;
				case '4':
					$acl->addACL( 'com_media', 'upload', 'users', 'publisher' );
					$acl->addACL( 'com_media', 'upload', 'users', 'editor' );
					$acl->addACL( 'com_media', 'upload', 'users', 'author' );
					$acl->addACL( 'com_media', 'upload', 'users', 'registered' );
					break;
			}

			$user = JFactory::getUser();
			if (!$user->authorize( 'com_media', 'popup' )) {
				return;
			}
			$doc 		= JFactory::getDocument();
			$template 	= $mainframe->getTemplate();

			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('editors-xtd','hikashopproduct');
			$link = 'index.php?option=com_hikashop&amp;ctrl=plugins&amp;task=trigger&amp;function=productDisplay&amp;tmpl=component&amp;cid='.$plugin->id.'&amp;'.hikashop_getFormToken().'=1';
			JHtml::_('behavior.modal');
			$button = new JObject;
			$button->set('modal', true);
			$button->set('link', $link);
			$button->set('text', JText::_('PRODUCT'));
			$button->set('name', 'hikashopproduct');
			$button->set('options', "{handler: 'iframe', size: {x: 800, y: 450}}");
			$doc = JFactory::getDocument();

			if(!HIKASHOP_J30)
				JHTML::_('behavior.mootools');
			else
				JHTML::_('behavior.framework');
			$img_name = 'hikashopproduct.png';
			$path = '../plugins/editors-xtd/'.$img_name;
			$doc->addStyleDeclaration('.button2-left .hikashopproduct {background: url('.$path.') 100% 0 no-repeat; }');

			return $button;
		}
		else{
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_media');
			$user = JFactory::getUser();




			if ($asset == ''){
				$asset = $extension;
			}
			if (	$user->authorise('core.edit', $asset)
				||	$user->authorise('core.create', $asset)
				||	(count($user->getAuthorisedCategories($asset, 'core.create')) > 0)
				||	($user->authorise('core.edit.own', $asset) && $author == $user->id)
				||	(count($user->getAuthorisedCategories($extension, 'core.edit')) > 0)
				||	(count($user->getAuthorisedCategories($extension, 'core.edit.own')) > 0 && $author == $user->id)
			){
				$pluginsClass = hikashop_get('class.plugins');
				$plugin = $pluginsClass->getByName('editors-xtd','hikashopproduct');

				$link = 'index.php?option=com_hikashop&amp;ctrl=plugins&amp;task=trigger&amp;function=productDisplay&amp;editor_name='.urlencode($name).'&amp;tmpl=component&amp;cid='.$plugin->extension_id.'&amp;'.hikashop_getFormToken().'=1';
				JHtml::_('behavior.modal');
				$button = new JObject;
				$button->set('modal', true);
				$button->set('link', $link);
				$button->set('text', JText::_('PRODUCT'));
				$button->set('class', 'btn');
				$button->set('name', 'hikashopproduct');
				$button->set('options', "{handler: 'iframe', size: {x: 800, y: 450}}");
				$doc = JFactory::getDocument();

				if(!HIKASHOP_J30)
					JHTML::_('behavior.mootools');
				else
					JHTML::_('behavior.framework');
				$img_name = 'hikashopproduct.png';
				$path = '../plugins/editors-xtd/hikashopproduct/'.$img_name;
				$doc->addStyleDeclaration('.button2-left .hikashopproduct {background: url('.$path.') 100% 0 no-repeat; }');

				return $button;
			}
			else{
				return false;
			}
		}
	}
	function productDisplay(){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$editor_name = JRequest::getString('editor_name', 'jform_articletext');

		$pageInfo = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( "com_content.productbutton.search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = $app->getUserStateFromRequest( 'com_content.productbutton.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( 'com_content.productbutton.limitstart', 'limitstart', 0, 'int' );
		if((JRequest::getVar('search')!=$app->getUserState('com_content.productbutton.search')) || (JRequest::getVar('limit')!=$app->getUserState('com_content.productbutton.limit'))){
			$pageInfo->limit->start = 0;
			$app->setUserState('com_content.productbutton.limitstart',0);
		}

		$Select = 'SELECT * FROM '. hikashop_table('product');
		$Where = ' WHERE product_type=\'main\' AND product_access=\'all\' AND product_published=1 ';
		$orderBY = ' ORDER BY product_id ASC';
		$searchMap = array('product_name','product_code','product_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower(trim($pageInfo->search)),true).'%\'';
			$filter = '('.implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal".')';
			$filters[] =  $filter;
		}
		if(is_array($filters) && count($filters)){
			$filters = ' AND '.implode(' AND ',$filters);
		}else{
			$filters = '';
		}
		$db->setQuery($Select . $Where . $filters . $orderBY,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$products = $db->loadObjectList();
		$db->setQuery('SELECT COUNT(product_id) FROM '. hikashop_table('product').' WHERE product_type=\'main\' AND product_access=\'all\' AND product_published=1'. $filters );
		$nbrow = $db->loadResult();
		$db->setQuery('SELECT * FROM '. hikashop_table('price') .' ORDER BY price_product_id ASC');
		$prices = $db->loadObjectList();
		if(HIKASHOP_J30) {
			$pagination = hikashop_get('helper.pagination', $nbrow, $pageInfo->limit->start, $pageInfo->limit->value);
		} else {
			jimport('joomla.html.pagination');
			$pagination = new JPagination($nbrow, $pageInfo->limit->start, $pageInfo->limit->value);
		}

		$scriptV1 = "function insertTag(tag){ window.parent.jInsertEditorText(tag,'text'); return true;}";
		$scriptV2 = "function insertTag(tag){ window.parent.jInsertEditorText(tag,'".str_replace(array('\\','\''), array('\\\\', '\\\''), $editor_name)."'); return true;}";
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		if(version_compare(JVERSION,'1.6.0','<')) $doc->addScriptDeclaration( $scriptV1 );
		else $doc->addScriptDeclaration( $scriptV2 );

		$config =& hikashop_config();
		$pricetaxType = hikashop_get('type.pricetax');
		$discountDisplayType = hikashop_get('type.discount_display');
?>
	<script language="JavaScript" type="text/javascript">
		function divhidder(){
			if (document.getElementById('price').checked) {
				document.getElementById('Priceopt').style.visibility = 'visible';
			}
			else {
				document.getElementById('Priceopt').style.visibility = 'hidden';
			}
		}
		function checkSelect(){
			form = document.getElementById('adminForm');
			inputs = form.getElementsByTagName('input');
			nbbox = 0;
			nbboxOk = 0;
			nbboxProd = 0;
			for(i=0 ; i < inputs.length ; i++){
				if(inputs[i].type == 'checkbox' && inputs[i].checked==true){
					nbbox++;
				}
			}
			for(i=0 ; i < inputs.length ; i++){
				if(inputs[i].type == 'checkbox' && inputs[i].checked==true){
					nbboxOk++;
					if(inputs[i].id.match(/product_checkbox.*/)){
						if (nbboxProd == 0)
							document.getElementById('product_insert').value = '{product ';
						nbboxProd++;
						document.getElementById('product_insert').value = document.getElementById('product_insert').value +  inputs[i].name;
						if(nbbox > nbboxOk){
							document.getElementById('product_insert').value = document.getElementById('product_insert').value + '|';
						}
					}
				}
			}
			if( nbboxProd > 0 )
			{
				if(document.getElementById('name').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|name';
				}
				if(document.getElementById('cart').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|cart';
				}
				if(document.getElementById('quantityfield').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|quantityfield';
				}
				if(document.getElementById('description').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|description';
				}
				if(document.getElementById('picture').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|picture';
				}
				if(document.getElementById('link').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|link';
				}
				if(document.getElementById('border').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|border';
				}
				if(document.getElementById('badge').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|badge';
				}
				if(document.getElementById('menuid').value.length != 0){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|menuid:' + document.getElementById('menuid').value;
				}
				if(document.getElementById('pricedisc').value==1 && document.getElementById('price').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|pricedis1';
				}
				if(document.getElementById('pricedisc').value==2 && document.getElementById('price').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|pricedis2';
				}
				if(document.getElementById('pricedisc').value==3 && document.getElementById('price').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|pricedis3';
				}
				if(document.getElementById('pricetax').value==1 && document.getElementById('price').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|pricetax1';
				}
				if(document.getElementById('pricetax').value==2 && document.getElementById('price').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|pricetax2';
				}
				if(document.getElementById('pricedisc').value==0 && document.getElementById('pricetax').value==0 && document.getElementById('price').checked==true){
					document.getElementById('product_insert').value =document.getElementById('product_insert').value +  '|price';
				}
				document.getElementById('product_insert').value=document.getElementById('product_insert').value + '}';
			}
			if(document.getElementById('name').checked==false
			&& document.getElementById('price').checked==false
			&& document.getElementById('cart').checked==false
			&& document.getElementById('description').checked==false
			&& document.getElementById('picture').checked==false){
				document.getElementById('product_insert').value='';
			}
		}
		function checkAllBox(){
			var checkAll = document.getElementById('checkAll');
			var toCheck = document.getElementById('ToCheck').getElementsByTagName('input');
			for (i = 0 ; i < toCheck.length ; i++) {
				if (toCheck[i].type == 'checkbox') {
					if(checkAll.checked == true){
						toCheck[i].checked = true;
					}else{
						toCheck[i].checked = false;
					}
				}
			}
		}
	</script>
	<form action="<?php echo hikashop_currentURL();?>" method="POST" name="adminForm" id="adminForm">
		<table class="hikashop_no_border">
			<tr>
				<td width="100%">
					<?php echo JText::_( 'FILTER' ); ?>:
					<input type="text" name="search" id="hikashop_search" value="<?php echo hikashop_getEscaped($pageInfo->search);?>" class="inputbox" onchange="document.adminForm.submit();" />
					<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
					<button class="btn" onclick="document.getElementById('hikashop_search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
				</td>
			</tr>
		</table>
		<fieldset>
			<legend>OPTIONS</legend>
			<div id="productInsertOptions">
				<input type="checkbox" name="name" id="name" value="1" checked/><?php echo JText::_( 'HIKA_NAME' );?>
				<input type="checkbox" name="description" id="description" value="1" checked/><?php echo JText::_( 'PRODUCT_DESCRIPTION' );?>
				<input type="checkbox" name="cart" id="cart" value="1" <?php if(!empty($_REQUEST['cart'])) echo 'checked'; ?> /><?php echo JText::_( 'HIKASHOP_CHECKOUT_CART' );?>
				<input type="checkbox" name="quantity" id="quantityfield" value="1" <?php if(!empty($_REQUEST['quantityfield'])) echo 'checked'; ?> /><?php echo JText::_( 'HIKA_QUANTITY_FIELD' );?>
				<input type="checkbox" name="picture" id="picture" value="1" <?php if(!empty($_REQUEST['picture'])) echo 'checked'; ?>/><?php echo JText::_( 'HIKA_IMAGE' );?>
				<input type="checkbox" name="link" id="link" value="1" <?php if(!empty($_REQUEST['link'])) echo 'checked'; ?>/><?php echo JText::_( 'LINK_TO_PRODUCT_PAGE' );?>
				<input type="checkbox" name="border" id="border" value="1" <?php if(!empty($_REQUEST['border'])) echo 'checked'; ?> /><?php echo JText::_( 'ITEM_BOX_BORDER' );?>
				<input type="checkbox" name="badge" id="badge" value="1" <?php if(!empty($_REQUEST['badge'])) echo 'checked'; ?> /><?php echo JText::_( 'HIKA_BADGE' );?>
				<br/>
				Menu ID : <input type="text" name="menuid" id="menuid"  <?php if(!empty($_REQUEST['menuid'])) echo 'value="'.$_REQUEST['menuid'].'"';?> />
				<input type="checkbox" name="pricetax" id="pricetax" value="<?php echo $config->get('price_with_tax');?>" hidden/>
				<br/>
				<input type="checkbox" name="price" id="price" value="1" checked onclick="divhidder()"/><?php echo JText::_('DISPLAY_PRICE');?>
				<br/>
				<div id="Priceopt">
				<tr id="show_discount_line">
					<td class="key" valign="top">
						<?php echo JText::_('SHOW_DISCOUNTED_PRICE');?>
					</td>
					<td>
						<?php
						$default_params = $config->get('default_params');
						echo $discountDisplayType->display( 'pricedisc' ,3); ?>
					</td>
				</tr>
				<div>
				</div>
		</fieldset>
			<fieldset>
			<table class="adminlist table table-striped" cellpadding="1" width="100%">
				<thead>
					<tr>
						<th class="title titlenum">
							<?php echo JText::_('HIKA_NUM'); ?>
						</th>
						<th class="title titlebox">
							<input type="checkbox" name="checkAll" id="checkAll" value="" onclick="checkAllBox();"/>
						</th>
						<th class="title">
							<?php echo JText::_('HIKA_NAME'); ?>
						</th>
						<th class="title">
							<?php echo JText::_('PRODUCT_PRICE'); ?>
						</th>
						<th class="title">
							<?php echo JText::_('PRODUCT_QUANTITY'); ?>
						</th>
						<th class="title">
							<?php echo'ID'; ?>
						</th>
					</tr>
				</thead>
				<tbody id="ToCheck">
					<?php
						$i = 0;
						$row ='';
						$currencyClass = hikashop_get('class.currency');
						$currencies=new stdClass();
						$currency_symbol='';
						foreach($products as $product){
							$i++;
							$row.= '<tr><td class="title titlenum">';
							$row.= $i;
							$row.='</td><td class="title titlebox"><input type="checkbox" id="product_checkbox'.$product->product_id.'" name="'.$product->product_id;
							$row.='" value=""/></td><td class="center">';
							$row.=$product->product_name;
							$row.='</td><td class="center">';
							foreach($prices as $price){
								if($price->price_product_id==$product->product_id){
									$row.= $price->price_value;
									$currency = $currencyClass->getCurrencies($price->price_currency_id,$currencies);
									foreach($currency as $currrencie){
										if($price->price_currency_id == $currrencie->currency_id){
											$currency_symbol = $currrencie->currency_symbol;
										}
									}
									$row.=' ' .$currency_symbol;
								}
							}
							$row.='</td><td class="center">';
							if($product->product_quantity > -1) $row.=$product->product_quantity;
							else $row.= JText::_('UNLIMITED');
							$row.='</td><td class="center">';
							$row.=$product->product_id;
							$row.='</td></tr>';
						}
						echo $row;
					?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="7">
							<?php echo $pagination->getListFooter(); ?>
							<?php echo $pagination->getResultsCounter(); ?>
						</td>
					</tr>
				</tfoot>
			</table>
		</fieldset>
		<input type="hidden" name="product_insert" id="product_insert" value="" />
		<button class="btn" onclick="checkSelect(); insertTag(document.getElementById('product_insert').value); window.parent.SqueezeBox.close();"><?php echo JText::_( 'HIKA_INSERT' ); ?></button>
		<?php global $Itemid; ?>
		<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
		<?php echo JHTML::_( 'form.token' );
	}
}
