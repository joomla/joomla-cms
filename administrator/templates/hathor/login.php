<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

$app  = JFactory::getApplication();
$lang = JFactory::getLanguage();

// Gets the FrontEnd Main page Uri
$frontEndUri = JUri::getInstance(JUri::root());
$frontEndUri->setScheme(((int) $app->get('force_ssl', 0) === 2) ? 'https' : 'http');

// Output as HTML5
$this->setHtml5(true);

// jQuery needed by template.js
JHtml::_('jquery.framework');

// Add template js
JHtml::_('script', 'template.js', array('version' => 'auto', 'relative' => true));

// Add html5 shiv
JHtml::_('script', 'jui/html5.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Load system style CSS
JHtml::_('stylesheet', 'templates/system/css/system.css', array('version' => 'auto'));

// Loadtemplate CSS
JHtml::_('stylesheet', 'template.css', array('version' => 'auto', 'relative' => true));

// Load additional CSS styles for colors
if (!$this->params->get('colourChoice'))
{
	$colour = 'standard';
}
else
{
	$colour = htmlspecialchars($this->params->get('colourChoice'), ENT_COMPAT, 'UTF-8');
}

JHtml::_('stylesheet', 'colour_' . $colour . '.css', array('version' => 'auto', 'relative' => true));

// Load additional CSS styles for rtl sites
if ($this->direction === 'rtl')
{
	JHtml::_('stylesheet', 'template_rtl.css', array('version' => 'auto', 'relative' => true));
	JHtml::_('stylesheet', 'colour_' . $colour . '_rtl.css', array('version' => 'auto', 'relative' => true));;
}

// Load additional CSS styles for bold Text
if ($this->params->get('boldText'))
{
	JHtml::_('stylesheet', 'boldtext.css', array('version' => 'auto', 'relative' => true));
}

// Load specific language related CSS
JHtml::_('stylesheet', 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto', 'relative' => true));

// Load custom.css
JHtml::_('stylesheet', 'custom.css', array('version' => 'auto', 'relative' => true));

// IE specific
JHtml::_('stylesheet', 'ie8.css', array('version' => 'auto', 'relative' => true, 'conditional' => 'IE 8'));
JHtml::_('stylesheet', 'ie7.css', array('version' => 'auto', 'relative' => true, 'conditional' => 'IE 7'));

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
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
</head>
<body id="login-page">
	<div id="containerwrap">
		<!-- Header Logo -->
		<div id="header">
			<h1 class="title"><?php echo $this->params->get('showSiteName') ? $app->get('sitename') . ' ' . JText::_('JADMINISTRATION') : JText::_('JADMINISTRATION'); ?></h1>
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
					<a href="<?php echo $frontEndUri->toString(); ?>" target="_blank"><?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE'); ?></a>
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
			if ($lang->isRtl())
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
