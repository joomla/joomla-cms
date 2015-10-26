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
if(!isset($this->element['layout_type']))
	$this->element['layout_type'] = 'inherit';
?>
<div id="hikashop_main_content" class="hk-container-fluid item-cartmodule-interface">
	<!-- module edition -->
	<div id="hikashop_module_backend_page_edition">

		<!-- Main part (Generic options) -->
		<div class="hkc-xl-12 hikashop_module_block hikashop_module_edit_general">
		<?php
		$this->setLayout('options_main');
		echo $this->loadTemplate();

		$this->setLayout('options_price');
		echo $this->loadTemplate();
		?>
		</div>
	</div>
</div>
<?php
$js = "
window.hikashop.ready(function(){
	hkjQuery('#options #hikashop_main_content').prev('.control-group').hide();
});
";
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);
