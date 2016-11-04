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

// Get additional language strings prefixed with TPL_HATHOR
// @todo: Do we realy need this?
$lang = JFactory::getLanguage();
$lang->load('tpl_hathor', JPATH_ADMINISTRATOR)
|| $lang->load('tpl_hathor', JPATH_ADMINISTRATOR . '/templates/hathor/language');

$app = JFactory::getApplication();

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
<body class="contentpane">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
