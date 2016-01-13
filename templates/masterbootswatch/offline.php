<?php 
	/*------------------------------------------------------------------------
# author    Gonzalo Suez
# copyright Copyright © 2012 gsuez.cl. All rights reserved.
# @license  http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website   http://www.gsuez.cl
-------------------------------------------------------------------------*/
	defined( '_JEXEC' ) or die;
	// variables
	$app = JFactory::getApplication();
	$doc = JFactory::getDocument();
	$tpath = $this->baseurl.'/templates/'.$this->template;
	$this->setGenerator(null);
	// load sheets and scripts
	$doc->addStyleSheet($tpath.'/css/offline.css?v=1.0.0');
	$doc->addScript($tpath.'/js/modernizr-2.6.2.js');
	?><!doctype html>
<!--[if IEMobile]><html class="iemobile" lang="<?php  echo $this->language; ?>"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="<?php  echo $this->language; ?>"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="<?php  echo $this->language; ?>"> <![endif]-->
<!--[if gt IE 8]><!-->  <html class="no-js" lang="<?php  echo $this->language; ?>"> <!--<![endif]-->
<head>
  <jdoc:include type="head" />
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" /> <!-- mobile viewport optimized -->
  <link rel="apple-touch-icon-precomposed" href="<?php  echo $tpath; ?>/apple-touch-icon-57x57.png"> <!-- iphone, ipod, android -->
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php  echo $tpath; ?>/apple-touch-icon-72x72.png"> <!-- ipad -->
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php  echo $tpath; ?>/apple-touch-icon-114x114.png"> <!-- iphone retina -->
  <link href="<?php  echo $tpath; ?>/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" /> <!-- favicon -->
</head>
<body>
  <jdoc:include type="message" />
  <div id="frame" class="outline">
    <?php  if ($app->getCfg('offline_image')) : ?>
      <img src="<?php  echo $app->getCfg('offline_image'); ?>" alt="<?php  echo $app->getCfg('sitename'); ?>" />
    <?php  endif; ?>
    <?php  if ($app->getCfg('display_offline_message', 1) == 1 && str_replace(' ', '', $app->getCfg('offline_message')) != ''): ?>
		<p><?php  echo $app->getCfg('offline_message'); ?></p>
    <?php  elseif ($app->getCfg('display_offline_message', 1) == 2 && str_replace(' ', '', JText::_('JOFFLINE_MESSAGE')) != ''): ?>
		<p><?php  echo JText::_('JOFFLINE_MESSAGE'); ?></p>
	<?php  endif; ?>
    <form action="<?php  echo JRoute::_('index.php', true); ?>" method="post" name="login" id="form-login">
      <fieldset class="input">
        <p id="form-login-username">
          <label for="username"><?php  echo JText::_('JGLOBAL_USERNAME'); ?></label><br />
          <input type="text" name="username" id="username" class="inputbox" alt="<?php  echo JText::_('JGLOBAL_USERNAME'); ?>" size="18" />
        </p>
        <p id="form-login-password">
          <label for="passwd"><?php  echo JText::_('JGLOBAL_PASSWORD'); ?></label><br />
          <input type="password" name="password" id="password" class="inputbox" alt="<?php  echo JText::_('JGLOBAL_PASSWORD'); ?>" size="18" />
        </p>
        <p id="form-login-remember">
          <label for="remember"><?php  echo JText::_('JGLOBAL_REMEMBER_ME'); ?></label>
          <input type="checkbox" name="remember" value="yes" alt="<?php  echo JText::_('JGLOBAL_REMEMBER_ME'); ?>" id="remember" />
        </p>
        <p id="form-login-submit">
          <label>&nbsp;</label>
          <input type="submit" name="Submit" class="button" value="<?php  echo JText::_('JLOGIN'); ?>" />
        </p>
      </fieldset>
      <input type="hidden" name="option" value="com_users" />
      <input type="hidden" name="task" value="user.login" />
      <input type="hidden" name="return" value="<?php  echo base64_encode(JURI::base()); ?>" />
      <?php  echo JHTML::_( 'form.token' ); ?>
    </form>
  </div>
</body>
</html>