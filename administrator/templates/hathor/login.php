<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app  = JFactory::getApplication();
$lang = JFactory::getLanguage();
$doc  = JFactory::getDocument();

// jQuery needed by template.js
JHtml::_('jquery.framework');

JHtml::_('behavior.noframes');

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Load system style CSS
$doc->addStyleSheet($this->baseurl . '/templates/system/css/system.css');

// Loadtemplate CSS
$doc->addStyleSheet($this->baseurl . '/templates/'.$this->template.'/css/template.css');

// Load additional CSS styles for colors
if (!$this->params->get('colourChoice'))
{
	$colour = 'standard';
}
else
{
	$colour = htmlspecialchars($this->params->get('colourChoice'));
}

$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/colour_' . $colour . '.css');

// Load specific language related CSS
$file = 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css';

if (is_file($file))
{
	$doc->addStyleSheet($file);
}

// Load additional CSS styles for rtl sites
if ($this->direction == 'rtl')
{
	$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template_rtl.css');
	$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/colour_' . $colour . '_rtl.css');
}

// Load specific language related CSS
$file = 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css';

if (JFile::exists($file))
{
	$doc->addStyleSheet($file);
}

// Load additional CSS styles for bold Text
if ($this->params->get('boldText'))
{
	$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/boldtext.css');
}

// Logo file
if ($this->params->get('logoFile'))
{
	$logo = JUri::root() . $this->params->get('logoFile');
}
else
{
	$logo = $this->baseurl . '/templates/' . $this->template . '/images/logo.png';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />

<!-- Load additional CSS styles for Internet Explorer -->
<!--[if IE 7]>
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo  $this->template; ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if lt IE 9]>
	<script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script>
<![endif]-->

<!-- Load Template JavaScript -->
<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php  echo  $this->template;  ?>/js/template.js"></script>

</head>
<body id="login-page">
	<div id="containerwrap">
		<!-- Header Logo -->
		<div id="header">
			<h1 class="title"><?php echo $this->params->get('showSiteName') ? $app->get('sitename') . " " . JText::_('JADMINISTRATION') : JText::_('JADMINISTRATION'); ?></h1>
		</div><!-- end header -->
		<!-- Content Area -->
		<div id="content">
			<!-- Beginning of Actual Content -->
			<div id="element-box" class="login">
				<div class="pagetitle"><h2><?php echo JText::_('COM_LOGIN_JOOMLA_ADMINISTRATION_LOGIN'); ?></h2></div>
					<!-- System Messages -->
					<jdoc:include type="message" />
					<div class="login-inst">
					<p><?php echo JText::_('COM_LOGIN_VALID') ?></p>
					<div id="lock"></div>
					<a href="<?php echo JUri::root(); ?>" target="_blank"><?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE'); ?></a>
					</div>
					<!-- Login Component -->
					<div class="login-box">
						<jdoc:include type="component" />
					</div>
				<div class="clr"></div>
			</div><!-- end element-box -->
		<noscript>
			<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
		</noscript>
		</div><!-- end content -->
		<div class="clr"></div>
	</div><!-- end of containerwrap -->
	<!-- Footer -->
	<div id="footer">
		<p class="copyright">
			<?php
			// Fix wrong display of Joomla!® in RTL language
			if (JFactory::getLanguage()->isRtl())
			{
				$joomla = '<a href="https://www.joomla.org" target="_blank">Joomla!</a><sup>&#174;&#x200E;</sup>';
			}
			else
			{
				$joomla = '<a href="https://www.joomla.org" target="_blank">Joomla!</a><sup>&#174;</sup>';
			}
			echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla);
			?>
		</p>
	</div>
</body>
</html>
