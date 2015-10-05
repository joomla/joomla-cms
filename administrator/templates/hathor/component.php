<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Get additional language strings prefixed with TPL_HATHOR
// @todo: Do we realy need this?
$lang = JFactory::getLanguage();
$lang->load('tpl_hathor', JPATH_ADMINISTRATOR)
|| $lang->load('tpl_hathor', JPATH_ADMINISTRATOR . '/templates/hathor/language');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

// jQuery needed by template.js
JHtml::_('jquery.framework');

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Load system style CSS
$doc->addStyleSheet($this->baseurl . '/templates/system/css/system.css');

// Loadtemplate CSS
$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template.css');

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
$file = 'language/' . $lang->getTag() . '/' . $lang->getTag().'.css';

if (JFile::exists($file))
{
	$doc->addStyleSheet($file);
}

// Load additional CSS styles for bold Text
if ($this->params->get('boldText'))
{
	$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/boldtext.css');
}

// Load template javascript
$doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/template.js', 'text/javascript');

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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
<head>
<jdoc:include type="head" />
<!--[if lt IE 9]>
	<script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script>
<![endif]-->
</head>
<body class="contentpane">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
