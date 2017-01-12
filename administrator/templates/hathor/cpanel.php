<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

$app   = JFactory::getApplication();
$lang  = JFactory::getLanguage();

// Output as HTML5
$this->setHtml5(true);

// Add template js
JHtml::_('script', 'template.js', array('version' => 'auto', 'relative' => true));

// Add html5 shiv
JHtml::_('script', 'jui/html5.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Load system style CSS
JHtml::_('stylesheet', 'templates/system/css/system.css', array('version' => 'auto'));

// Load template CSS
JHtml::_('stylesheet', 'template.css', array('version' => 'auto', 'relative' => true));

// Load additional CSS styles for colors
if (!$this->params->get('colourChoice'))
{
	$colour = 'standard';
}
else
{
	$colour = htmlspecialchars($this->params->get('colourChoice'));
}

JHtml::_('stylesheet', 'colour_' . $colour . '.css', array('version' => 'auto', 'relative' => true));

// Load additional CSS styles for rtl sites
if ($this->direction === 'rtl')
{
	JHtml::_('stylesheet', 'template_rtl.css', array('version' => 'auto', 'relative' => true));
	JHtml::_('stylesheet', 'colour_' . $colour . '_rtl.css', array('version' => 'auto', 'relative' => true));
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
<body id="minwidth" class="cpanel-page">
<div id="containerwrap">
	<!-- Header Logo -->
	<div id="header">
		<!-- Site Title and Skip to Content -->
		<div class="title-ua">
			<h1 class="title"><?php echo $this->params->get('showSiteName') ? $app->get('sitename') . ' ' . JText::_('JADMINISTRATION') : JText::_('JADMINISTRATION'); ?></h1>
			<div id="skiplinkholder"><p><a id="skiplink" href="#skiptarget"><?php echo JText::_('TPL_HATHOR_SKIP_TO_MAIN_CONTENT'); ?></a></p></div>
      	</div>
	</div><!-- end header -->
	<!-- Main Menu Navigation -->
	<div id="nav">
		<div id="module-menu">
			<h2 class="element-invisible"><?php echo JText::_('TPL_HATHOR_MAIN_MENU'); ?></h2>
			<jdoc:include type="modules" name="menu" />
		</div>
		<div class="clr"></div>
	</div><!-- end nav -->
	<!-- Status Module -->
	<div id="module-status">
		<jdoc:include type="modules" name="status"/>
	</div>
	<!-- Content Area -->
	<div id="content">
		<!-- Component Title -->
		<jdoc:include type="modules" name="title" />
		<!-- System Messages -->
		<jdoc:include type="message" />
		<!-- Sub Menu Navigation -->
		<div id="no-submenu"></div>
   		<div class="clr"></div>
		<!-- Beginning of Actual Content -->
		<div id="element-box">
			<p id="skiptargetholder"><a id="skiptarget" class="skip" tabindex="-1"></a></p>
				<div class="adminform">
					<!-- Display the Quick Icon Shortcuts -->
					<div class="cpanel-icons">
						<jdoc:include type="modules" name="icon" />
					</div>
					<!-- Display Admin Information Panels -->
					<div class="cpanel-component">
						<jdoc:include type="component" />
					</div>
				</div>
				<div class="clr"></div>
		</div><!-- end element-box -->
		<noscript>
			<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
		</noscript>
		<div class="clr"></div>
	</div><!-- end content -->
		<div class="clr"></div>
	</div><!-- end containerwrap -->
	<!-- Footer -->
	<div id="footer">
		<jdoc:include type="modules" name="footer" style="none"  />
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
