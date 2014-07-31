<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.bluestork
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$doc = JFactory::getDocument();

$doc->addStyleSheet('templates/system/css/system.css');
$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');

if ($this->direction == 'rtl') {
	$doc->addStyleSheet('templates/'.$this->template.'/css/template_rtl.css');
}

/** Load specific language related css */
$lang = JFactory::getLanguage();
$file = 'language/'.$lang->getTag().'/'.$lang->getTag().'.css';
if (JFile::exists($file)) {
	$doc->addStyleSheet($file);
}

if ($this->params->get('textBig')) {
	$doc->addStyleSheet('templates/'.$this->template.'/css/textbig.css');
}

if ($this->params->get('highContrast')) {
	$doc->addStyleSheet('templates/'.$this->template.'/css/highcontrast.css');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
	<head>
		<jdoc:include type="head" />
	</head>
	<body class="contentpane">
		<jdoc:include type="message" />
		<jdoc:include type="component" />
	</body>
</html>
