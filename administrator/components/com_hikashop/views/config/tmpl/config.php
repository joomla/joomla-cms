<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=config" method="post"  name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" id="config_form_task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_('form.token');

	$configTabs = array(
		'config_main' => array('MAIN', 'main'),
		'config_checkout' => array('CHECKOUT', 'checkout'),
		'config_display' => array('DISPLAY', 'display'),
		'config_features' => array('HIKA_FEATURES', 'features'),
		'config_plugins' => array('PLUGINS', 'plugins'),
		'config_languages' => array('LANGUAGES', 'languages')
	);

	if(hikashop_level(2)){
		$configTabs['config_acl'] = array('ACCESS_LEVEL', 'acl');
	}

	if(hikashop_level(1)){
		$configTabs['config_cron'] = array('CRON', 'cron');
	}

	$options = array(
		'startOffset' => $this->default_tab,
		'useCookie' => true
	);
	if(!HIKASHOP_J30) {
		$options['onActive'] = 'function(title, description) {
			description.setStyle("display", "block");
			title.addClass("open").removeClass("closed");
			if(title.getAttribute("class").indexOf("config_") >= 0)
				myHash = title.getAttribute("class").replace("tabs","").replace("open","").replace("config_","").replace(/^\s*|\s*$/g, "");
			else
				myHash = title.getAttribute("id").replace("config_","").replace(/^\s*|\s*$/g, "");
			if(window.location.hash.substr(1, myHash.length) != myHash)
				window.location.hash = myHash;
		}';
	}
	echo $this->tabs->start('config_tab', $options);
	foreach($configTabs as $pane => $paneOpt) {
		echo $this->tabs->panel(JText::_($paneOpt[0]), $pane);
		$this->setLayout($paneOpt[1]);
		echo $this->loadTemplate();
	}
	echo $this->tabs->end();
?>
	<div style="clear:both;" class="clr"></div>
</form>
<script>
var configWatcher = {
	currentHRef : '',
	init: function(){
		var t = this;
		setInterval( function(){ t.periodical(); }, 50 );
<?php
	if(HIKASHOP_BACK_RESPONSIVE) {
?>
		jQuery("ul.nav-remember").each(function(nav){
			var id = jQuery(this).attr("id");
			jQuery("#" + id + " a[data-toggle=\"tab\"]").on("shown", function (e) {
				var myHash = jQuery(this).attr("id").replace("config_","").replace("_tablink","");
				if(window.location.hash.substr(1, myHash.length) != myHash)
					window.location.hash = myHash;
			});
		});
<?php
	}
?>
	},
	periodical: function() {
		var href = window.location.hash.substring(1);
		if( href != this.currentHRef ) {
			this.currentHRef = href;
			this.switchAndScroll(href);
		}
	},
	switchAndScroll: function(hash) {
		if(hash.length == 0)
			return;
		if(hash.indexOf('_') < 0) {
			var tabName = hash;
			hash = '';
		} else {
			var tabName = hash.substr(0, hash.indexOf('_'));
		}
<?php
	if(HIKASHOP_BACK_RESPONSIVE) {
?>
		jQuery("#config_"+tabName+"_tablink").tab("show");
		this.scrollToCust( hash );
<?php
	} else {
?>
		var childrens = $('config_tab').getChildren('dt'), elt = 0, j = 0;
		for (var i = 0; i < childrens.length; i++){
			var children = childrens[i];
			if(children.hasClass('tabs') || children.id.substr(0, children.id.indexOf('_'))){
				if(children.hasClass('config_'+tabName) || children.id == 'config_'+tabName){
					children.addClass('open').removeClass('closed');
					elt = j;
				}else{
					children.addClass('closed').removeClass('open');
				}
				j++;
			}
		}

		var tabsContent = $('config_tab').getNext('div');
		var tabChildrens = tabsContent.getChildren('dd');
		for (var i = 0; i < tabChildrens.length; i++){
			var childContent = tabChildrens[i];
			if(i == elt){
				childContent.style.display = 'block';
			}else{
				childContent.style.display = 'none';
			}
		}

		var d = document, elem = d.getElementById(hash);
		if(elem)
			window.scrollTo(0, elem.offsetTop +230);
		else
			window.scrollTo(0, 0);
<?php
	}
?>
	},
	scrollToCust: function(name) {
		var d = document, elem = d.getElementById(name);
		if( !elem ) { window.scrollTo(0, 0); return; }
		var topPos = elem.offsetTop + 100;
		window.scrollTo(0, topPos);
	}
}
window.hikashop.ready( function(){ configWatcher.init(); });
</script>
<?php if(!HIKASHOP_BACK_RESPONSIVE) {
hikashop_loadJsLib('jquery');
?>
<script type="text/javascript">
!function($) {
$(function() {
	var navIds = ['#menu_main', '#menu_checkout', '#menu_display', '#menu_features'];
	var saveIds = ['#menu-save-button-main', '#menu-save-button-checkout', '#menu-save-button-display', '#menu-save-button-features'];
	var scrollIds = ['#menu-scrolltop-main', '#menu-scrolltop-checkout', '#menu-scrolltop-display', '#menu-scrolltop-features'];
	$win = $(window), $body = $('body'), navTop = $('#adminForm').offset().top + 10, isFixed = 0;

	$win.scroll(processScroll);
	processScroll();

	function processScroll() {
		var scrollTop = $win.scrollTop();
		if(scrollTop >= navTop && !isFixed) {
			isFixed = 1;
			for(var i = 0; i < navIds.length; i++){
				$(navIds[i]).addClass('navmenu-fixed');
				$(saveIds[i]).removeClass('menu-save-button');
				$(scrollIds[i]).removeClass('menu-scrolltop');
			}

		} else if(scrollTop <= navTop && isFixed) {
			isFixed = 0;
			for(var i = 0; i < navIds.length; i++){
				$(navIds[i]).removeClass('navmenu-fixed');
				$(saveIds[i]).addClass('menu-save-button');
				$(scrollIds[i]).addClass('menu-scrolltop');
			}
		}
	}
})}(window.jQuery);
</script>
<?php } ?>
