<?php 
	/*------------------------------------------------------------------------
# author    Gonzalo Suez
# copyright © 2012 gsuez.cl. All rights reserved.
# @license  http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website   http://www.gsuez.cl
-------------------------------------------------------------------------*/
	defined( '_JEXEC' ) or die;
	// variables
	$tpath = $this->baseurl.'/templates/'.$this->template;
	?><!doctype html>
<!--[if IEMobile]><html class="iemobile" lang="<?php  echo $this->language; ?>"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="<?php  echo $this->language; ?>"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="<?php  echo $this->language; ?>"> <![endif]-->
<!--[if gt IE 8]><!-->  <html class="no-js" lang="<?php  echo $this->language; ?>"> <!--<![endif]-->
<head>
  <title><?php  echo $this->error->getCode().' - '.$this->title; ?></title>
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" /> <!-- mobile viewport optimized -->
  <link rel="stylesheet" href="<?php  echo $tpath; ?>/css/error.css?v=1.0.0" type="text/css" />
  <link rel="apple-touch-icon-precomposed" href="<?php  echo $tpath; ?>/apple-touch-icon-57x57.png"> <!-- iphone, ipod, android -->
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php  echo $tpath; ?>/apple-touch-icon-72x72.png"> <!-- ipad -->
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php  echo $tpath; ?>/apple-touch-icon-114x114.png"> <!-- iphone retina -->
  <link href="<?php  echo $tpath; ?>/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" /> <!-- favicon -->
  <script src="<?php  echo $tpath; ?>/js/modernizr-2.6.2.js" type="text/javascript"></script>
</head>
<body>
  <div align="center">
    <div id="error">
      <h1 align="center"><a href="<?php  echo $this->baseurl; ?>/" class="ihrlogo">IhrLogo</a></h1>
      <?php 
	echo $this->error->getCode().' - '.$this->error->getMessage();
	
	if (($this->error->getCode()) == '404') {
		echo '<br />';
		echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND');
	}
	?>
      <p><?php  echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>: 
      <a href="<?php  echo $this->baseurl; ?>/"><?php  echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a>.</p>
      <?php 
	// render module mod_search
	$module = new stdClass();
	$module->module = 'mod_search';
	echo JModuleHelper::renderModule($module);
	?>
    </div>
  </div>
</body>
</html>