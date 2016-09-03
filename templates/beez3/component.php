<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$color = $this->params->get('templatecolor');

// Output as HTML5
$this->setHtml5(true);

$this->addStyleSheet($this->baseurl . '/templates/system/css/system.css');
$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template.css', 'text/css', 'screen');
$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/position.css', 'text/css', 'screen');
$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/layout.css', 'text/css', 'screen');
$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/print.css', 'text/css', 'print');

$files = JHtml::_('stylesheet', 'templates/' . $this->template . '/css/general.css', null, false, true);

if ($files)
{
	if (!is_array($files))
	{
		$files = array($files);
	}

	foreach ($files as $file)
	{
		$this->addStyleSheet($file);
	}
}

$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/' . htmlspecialchars($color, ENT_COMPAT, 'UTF-8') . '.css');

if ($this->direction == 'rtl')
{
	$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template_rtl.css');

	if (file_exists(JPATH_SITE . '/templates/' . $this->template . '/css/' . htmlspecialchars($color, ENT_COMPAT, 'UTF-8') . '_rtl.css'))
	{
		$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/' . htmlspecialchars($color, ENT_COMPAT, 'UTF-8') . '_rtl.css');
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<!--[if lte IE 6]><link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/ieonly.css" rel="stylesheet" /><![endif]-->
	<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body class="contentpane">
	<div id="all">
		<div id="main">
			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>
	</div>
</body>
</html>
