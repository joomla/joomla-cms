<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_checkout_page" class="hikashop_checkout_page hikashop_checkout_page_step<?php echo $this->step; ?>">
	<?php
	if(hikashop_level(1)){
		$open_hour = $this->config->get('store_open_hour',0);
		$close_hour = $this->config->get('store_close_hour',0);
		$open_minute = $this->config->get('store_open_minute',0);
		$close_minute = $this->config->get('store_close_minute',0);
		if($open_hour!=$close_hour || $open_minute!=$close_minute){
			function getCurrentDate($format = '%H'){
				if(version_compare(JVERSION,'1.6.0','>=')) $format = str_replace(array('%H','%M'),array('H','i'),$format);
				return (int)JHTML::_('date',time()- date('Z'),$format,null);
			}
			$current_hour = hikashop_getDate(time(),'%H');
			$current_minute = hikashop_getDate(time(),'%M');
			$closed=false;
			if($open_hour<$close_hour || ($open_hour==$close_hour && $open_minute<$close_minute)){
				if($current_hour<$open_hour || ($current_hour==$open_hour && $current_minute<$open_minute)){
					$closed=true;
				}
				if($close_hour<$current_hour || ($current_hour==$close_hour && $close_minute<$current_minute)){
					$closed=true;

				}
			}else{
				$closed=true;

				if($current_hour<$close_hour || ($current_hour==$close_hour && $current_minute<$close_minute)){
					$closed=false;
				}
				if($open_hour<$current_hour || ($current_hour==$open_hour && $open_minute<$current_minute)){
					$closed=false;

				}
			}
			if($closed){
				$app=& JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('THE_STORE_IS_ONLY_OPEN_FROM_X_TO_X',$open_hour.':'.sprintf('%02d', $open_minute),$close_hour.':'.sprintf('%02d', $close_minute)));
				echo '</div>';
				return;
			}
		}
	}

	global $Itemid;
	$checkout_itemid = $this->config->get('checkout_itemid');
	if(!empty($checkout_itemid )){
		$Itemid = $checkout_itemid ;
	}
	$url_itemid='';
	if(!empty($Itemid)){
		$url_itemid='&Itemid='.$Itemid;
	}

	if($this->display_checkout_bar){
		if(HIKASHOP_RESPONSIVE) {
?>
			<div class="hikashop_wizardbar">
				<ul>
<?php
		} else {
?>
			<div id="hikashop_cart_bar" class="hikashop_cart_bar">
<?php
		}

			$already=true;
			if (count($this->steps) > $this->step+1) $link=true;
			foreach($this->steps as $k => $step){
				$step=explode('_',trim($step));
				$step_name = reset($step);
				if($this->display_checkout_bar==2 && $step_name=='end'){
					continue;
				}
				$class = '';
				$badgeClass = '';
				if($k == $this->step){
					$already = false;
					$class .= ' hikashop_cart_step_current';
					$badgeClass = 'info';
				}
				if($already){
					$class .= ' hikashop_cart_step_finished';
					$badgeClass = 'success';
				}

				if(HIKASHOP_RESPONSIVE) {
?>
				<li class="<?php echo trim($class); ?>">
					<span class="badge badge-<?php echo $badgeClass; ?>"><?php echo ($k + 1); ?></span>
<?php
						if($k == $this->step || empty($link)) {
							echo JText::_('HIKASHOP_CHECKOUT_'.strtoupper($step_name));
						} else {
?>
						<a href="<?php echo hikashop_completeLink('checkout&task=step&step='.$k.$url_itemid);?>">
							<?php echo JText::_('HIKASHOP_CHECKOUT_'.strtoupper($step_name));?>
						</a>
<?php
						}
?>
					<span class="hikashop_chevron"></span>
				</li>

<?php
				} else {
?>
				<div class="hikashop_cart_step<?php echo $class;?>">
					<span><?php
						if($k == $this->step || empty($link)){
							echo JText::_('HIKASHOP_CHECKOUT_'.strtoupper($step_name));
						}else{ ?>
						<a href="<?php echo hikashop_completeLink('checkout&task=step&step='.$k.$url_itemid);?>">
							<?php echo JText::_('HIKASHOP_CHECKOUT_'.strtoupper($step_name));?>
						</a>
					<?php }
					?></span>
				</div><?php
				}
			}
			?>
			</div>
<?php
	}
	if(empty($this->noform)){
		?>
		<form action="<?php echo hikashop_completeLink('checkout&task=step&step='.($this->step+1).$url_itemid); ?>" method="post" name="hikashop_checkout_form" enctype="multipart/form-data" onsubmit="if('function' == typeof(hikashopSubmitForm)) { hikashopSubmitForm('hikashop_checkout_form'); return false; } else { return true; }">
		<?php
	}
	$dispatcher = JDispatcher::getInstance();
	$this->nextButton = true;
	foreach($this->layouts as $layout) {
		$layout = trim($layout);
		if($layout == 'end') {
			$this->continueShopping = '';
		}
		if(substr($layout, 0, 4) != 'plg.') {
			$this->setLayout($layout);
			echo $this->loadTemplate();
		} else {
			$html = '';
			$dispatcher->trigger('onCheckoutStepDisplay', array($layout, &$html, &$this));
			if(!empty($html)) {
				echo $html;
			}
		}
	}
	if(empty($this->noform)){
		?>
		<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
		<input type="hidden" name="option" value="com_hikashop"/>
		<input type="hidden" name="ctrl" value="checkout"/>
		<input type="hidden" name="task" value="step"/>
		<input type="hidden" name="previous" value="<?php echo $this->step; ?>"/>
		<input type="hidden" name="step" value="<?php echo $this->step+1; ?>"/>
		<input type="hidden" id="hikashop_validate" name="validate" value="0"/>
		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" name="unique_id" value="[<?php echo md5(uniqid())?>]"/>
		<br style="clear:both"/>
		<?php

		if($this->nextButton)
		{
			if($this->step == (count($this->steps) - 2)) {
				$checkout_next_button = JText::_('CHECKOUT_BUTTON_FINISH');
				if($checkout_next_button == 'CHECKOUT_BUTTON_FINISH')
					$checkout_next_button = JText::_('NEXT');
			} else
				$checkout_next_button = JText::_('NEXT');
			echo $this->cart->displayButton($checkout_next_button,'next',$this->params, hikashop_completeLink('checkout&task=step&step='.$this->step+1),'if(hikashopCheckChangeForm(\'order\',\'hikashop_checkout_form\')){ if(hikashopCheckMethods()){ document.getElementById(\'hikashop_validate\').value=1; this.disabled = true; document.forms[\'hikashop_checkout_form\'].submit();}} return false;','id="hikashop_checkout_next_button"');
			$button = $this->config->get('button_style','normal');
			 	if ($button=='css')
					echo '<input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;"/></input>';
		}
		?>
		</form>
		<?php
		if($this->continueShopping){
			if(strpos($this->continueShopping,'Itemid')===false){
				if(strpos($this->continueShopping,'index.php?')!==false){
					$this->continueShopping.=$url_itemid;
				}
			}
			if(!preg_match('#^https?://#',$this->continueShopping)) $this->continueShopping = JURI::base().ltrim($this->continueShopping,'/');
			echo $this->cart->displayButton(JText::_('CONTINUE_SHOPPING'),'continue_shopping',$this->params,JRoute::_($this->continueShopping),'window.location=\''.JRoute::_($this->continueShopping).'\';return false;','id="hikashop_checkout_shopping_button"');
		}
	}
	?>
</div>
<div class="clear_both"></div>
<?php

if(JRequest::getWord('tmpl','')=='component'){
	exit;
}
