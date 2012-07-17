<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.strapped
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		3.0
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$app   = JFactory::getApplication();
$doc   = JFactory::getDocument();
$lang  = JFactory::getLanguage();
$input = $app->input;
$user  = JFactory::getUser();

// Add Stylesheets
$doc->addStyleSheet('../templates/system/css/bootstrap.min.css');
$doc->addStyleSheet('../templates/system/css/bootstrap-extended.css');
$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');
$doc->addStyleSheet('../templates/system/css/bootstrap-responsive.min.css');

// If Right-to-Left
if ($this->direction == 'rtl') :
	$doc->addStyleSheet('../templates/system/css/bootstrap-rtl.css');
endif;

// Load specific language related CSS
$file = 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css';
if (JFile::exists($file)) :
	$doc->addStyleSheet($file);
endif;

$doc->addStyleSheet('../templates/system/css/chosen.css');

// Detecting Active Variables
$option = $input->get('option', '');
$view = $input->get('view', '');
$layout = $input->get('layout', '');
$task = $input->get('task', '');
$itemid = $input->get('Itemid', '');
$sitename = $app->getCfg('sitename');

if($task == "edit" || $layout == "form" )
{
    $fullWidth = 1;
}
else
{
    $fullWidth = 0;
}

$cpanel = false;
if ($option == "com_cpanel")
{
	$cpanel = true;
}

// Adjusting content width
if ($cpanel) :
	$span = "span8";
elseif ($this->countModules('left') && $this->countModules('right')) :
	$span = "span6";
elseif ($this->countModules('left') && !$this->countModules('right')) :
	$span = "span10";
elseif (!$this->countModules('left') && $this->countModules('right')) :
	$span = "span8";
else :
	$span = "span12";
endif;

?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="../templates/system/js/jquery.js"></script>
	<script src="../templates/system/js/bootstrap.min.js"></script>
	<script src="../templates/system/js/chosen.jquery.min.js"></script>
	<script type="text/javascript">
	  jQuery.noConflict();
	</script>
	<jdoc:include type="head" />
</head>

<body class="site <?php echo $option . " view-" . $view . " layout-" . $layout . " task-" . $task . " itemid-" . $itemid . " ";?>" data-spy="scroll" data-target=".subhead" data-offset="87">
	<!-- Top Navigation -->
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid"> <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </a> <a class="brand" href="<?php echo $this->baseurl; ?>"><?php echo $sitename; ?></a>
				<div class="nav-collapse">
					<jdoc:include type="modules" name="menu" style="none" />
					<ul class="nav pull-right">
						<li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo JText::_('TPL_STRAPPED_SETTINGS');?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<?php if($user->authorise('core.admin')):?>
									<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_config"><?php echo JText::_('TPL_STRAPPED_GLOBAL_CONFIGURATION');?></a></li>
									<li class="divider"></li>
									<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_admin&view=sysinfo"><?php echo JText::_('TPL_STRAPPED_SYSTEM_INFORMATION');?></a></li>
								<?php endif;?>
								<?php if($user->authorise('core.manage', 'com_cache')):?>
									 <li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_cache"><?php echo JText::_('TPL_STRAPPED_CLEAR_CACHE');?></a></li>
								<?php endif;?>
								<?php if($user->authorise('core.admin', 'com_checkin')):?>
									<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_checkin"><?php echo JText::_('TPL_STRAPPED_GLOBAL_CHECK_IN');?></a></li>
								<?php endif;?>
								<?php if($user->authorise('core.manage', 'com_installer')):?>
									 <li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_installer"><?php echo JText::_('TPL_STRAPPED_INSTALLER');?></a></li>
								<?php endif;?>
							</ul>
						</li>
						<li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $user->username; ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li class=""><a href="index.php?option=com_admin&task=profile.edit&id=<?php echo $user->id;?>"><?php echo JText::_('TPL_STRAPPED_EDIT_ACCOUNT');?></a></li>
								<li class="divider"></li>
								<li class=""><a href="<?php echo JRoute::_('index.php?option=com_login&task=logout&'. JSession::getFormToken() .'=1');?>"><?php echo JText::_('TPL_STRAPPED_LOGOUT');?></a></li>
							</ul>
						</li>
					</ul>
				</div>
				<!--/.nav-collapse -->
			</div>
		</div>
	</div>
	<!-- Header -->
	<div class="header">
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span2 container-logo">
					<a class="logo" href="<?php echo $this->baseurl; ?>"><img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/images/logo.png" alt="<?php echo $sitename; ?>" /></a>
				</div>
				<div class="span7">
					<h1 class="page-title"><?php echo JHtml::_('string.truncate', $app->JComponentTitle, 40, false, false);?></h1>
				</div>
				<div class="span3">
					<jdoc:include type="modules" name="searchload" style="none" />
					<!-- placeholder search, remove once we have an admin search -->
					<form class="navbar-search pull-right">
		      			<input type="text" class="search-query" placeholder="Search" style="margin: 0 auto;" data-provide="typeahead" data-items="10" data-source="[&quot;Alabama&quot;,&quot;Alaska&quot;,&quot;Arizona&quot;,&quot;Arkansas&quot;,&quot;California&quot;,&quot;Colorado&quot;,&quot;Connecticut&quot;,&quot;Delaware&quot;,&quot;Florida&quot;,&quot;Georgia&quot;,&quot;Hawaii&quot;,&quot;Idaho&quot;,&quot;Illinois&quot;,&quot;Indiana&quot;,&quot;Iowa&quot;,&quot;Kansas&quot;,&quot;Kentucky&quot;,&quot;Louisiana&quot;,&quot;Maine&quot;,&quot;Maryland&quot;,&quot;Massachusetts&quot;,&quot;Michigan&quot;,&quot;Minnesota&quot;,&quot;Mississippi&quot;,&quot;Missouri&quot;,&quot;Montana&quot;,&quot;Nebraska&quot;,&quot;Nevada&quot;,&quot;New Hampshire&quot;,&quot;New Jersey&quot;,&quot;New Mexico&quot;,&quot;New York&quot;,&quot;North Dakota&quot;,&quot;North Carolina&quot;,&quot;Ohio&quot;,&quot;Oklahoma&quot;,&quot;Oregon&quot;,&quot;Pennsylvania&quot;,&quot;Rhode Island&quot;,&quot;South Carolina&quot;,&quot;South Dakota&quot;,&quot;Tennessee&quot;,&quot;Texas&quot;,&quot;Utah&quot;,&quot;Vermont&quot;,&quot;Virginia&quot;,&quot;Washington&quot;,&quot;West Virginia&quot;,&quot;Wisconsin&quot;,&quot;Wyoming&quot;]">
		      		</form>
		      		<!-- placeholder search, remove once we have an admin search -->
				</div>
			</div>
		</div>
	</div>
	<!-- Subheader -->
	<a class="btn btn-subhead" data-toggle="collapse" data-target=".subhead-collapse"><?php echo JText::_('TPL_STRAPPED_TOOLBAR');?> <i class="icon-wrench"></i></a>
	<div class="subhead-collapse">
		<div class="subhead">
			<div class="container-fluid">
				<div class="container-collapse"></div>
				<div class="row-fluid">
					<div class="span12">
						<jdoc:include type="modules" name="toolbar" style="no" />
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- container-fluid -->
	<div class="container-fluid">
		<div class="row-fluid">
			<?php if (($this->countModules('left')) && $cpanel): ?>
			<!-- Begin Sidebar -->
			<div id="sidebar" class="span2">
				<div class="sidebar-nav">
					<jdoc:include type="modules" name="left" style="no" />
				</div>
			</div>
			<!-- End Sidebar -->
			<?php endif; ?>
			<div id="content" class="<?php echo $span;?>">
				<!-- Begin Content -->
				<jdoc:include type="modules" name="top" style="xhtml" />
				<jdoc:include type="message" />
				<jdoc:include type="component" />
				<jdoc:include type="modules" name="bottom" style="xhtml" />
				<!-- End Content -->
			</div>
			<?php if (($this->countModules('right')) || $cpanel) : ?>
			<div id="aside" class="span4">
				<!-- Begin Right Sidebar -->
				<?php
				/* Load cpanel modules */
				if ($cpanel):?>
					<jdoc:include type="modules" name="icon" style="well" />
				<?php endif;?>
				<jdoc:include type="modules" name="right" style="xhtml" />
				<!-- End Right Sidebar -->
			</div>
			<?php endif; ?>
		</div>
		<hr />
		<?php if (!$this->countModules('status')): ?>
			<div class="footer">
				<p>&copy; <?php echo $sitename; ?> <?php echo date('Y');?></p>
			</div>
		<?php endif; ?>
	</div>
	<?php if ($this->countModules('status')): ?>
	<!-- Begin Status Module -->
	<div id="status" class="navbar navbar-fixed-bottom hidden-phone">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<p>&copy; <?php echo $sitename; ?> <?php echo date('Y');?></p>
			</div>
			<jdoc:include type="modules" name="status" style="no" />
		</div>
	</div>
	<!-- End Status Module -->
	<?php endif; ?>
	<jdoc:include type="modules" name="debug" style="none" />
	<script>
		(function($){
			$('*[rel=tooltip]').tooltip()
			$('.tip-bottom').tooltip({placement: "bottom"})
			$('*[rel=popover]').popover()

			// fix sub nav on scroll
		    var $win = $(window)
		      , $nav = $('.subhead')
		      , navTop = $('.subhead').length && $('.subhead').offset().top - 40
		      , isFixed = 0

		    processScroll()

		    // hack sad times - holdover until rewrite for 2.1
		    $nav.on('click', function () {
		      if (!isFixed) setTimeout(function () {  $win.scrollTop($win.scrollTop() - 47) }, 10)
		    })

		    $win.on('scroll', processScroll)

		    function processScroll() {
		      var i, scrollTop = $win.scrollTop()
		      if (scrollTop >= navTop && !isFixed) {
		        isFixed = 1
		        $nav.addClass('subhead-fixed')
		      } else if (scrollTop <= navTop && isFixed) {
		        isFixed = 0
		        $nav.removeClass('subhead-fixed')
		      }
		    }

		    // Chosen select boxes
		    $("select").chosen({disable_search_threshold : 10 });

		    // Turn radios into btn-group
		    $('.radio.btn-group label').addClass('btn')
		    $(".btn-group label:not(.active)").click(function(){
		        var label = $(this);
		        var input = $('#' + label.attr('for'));

		        if (!input.prop('checked')){
		            label.closest('.btn-group').find("label").removeClass('active btn-primary');
		            label.addClass('active btn-primary');
		            input.prop('checked', true);
		        }
		    });
		    $(".btn-group input[checked=checked]").each(function(){
		        $("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
		    });
	    })(jQuery);
	</script>
</body>
</html>
