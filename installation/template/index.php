<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

// Output as HTML5
$this->setHtml5(true);

// Load the JavaScript behaviors
JHtml::_('bootstrap.framework');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.core');
JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');

// Add installation js
JHtml::_('script', 'installation/template/js/installation.js', array('version' => 'auto'));

// Add html5 shiv
JHtml::_('script', 'jui/html5.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

// Add Stylesheets
JHtml::_('bootstrap.loadCss', true, $this->direction);
JHtml::_('stylesheet', 'installation/template/css/template.css', array('version' => 'auto'));

// Load JavaScript message titles
JText::script('ERROR');
JText::script('WARNING');
JText::script('NOTICE');
JText::script('MESSAGE');

// Add strings for JavaScript error translations.
JText::script('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT');
JText::script('JLIB_JS_AJAX_ERROR_NO_CONTENT');
JText::script('JLIB_JS_AJAX_ERROR_OTHER');
JText::script('JLIB_JS_AJAX_ERROR_PARSE');
JText::script('JLIB_JS_AJAX_ERROR_TIMEOUT');

// Load the JavaScript translated messages
JText::script('INSTL_PROCESS_BUSY');
JText::script('INSTL_FTP_SETTINGS_CORRECT');

// Add script options
$this->addScriptOptions('system.installation', array('url' => JRoute::_('index.php')));
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<jdoc:include type="head" />
		<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
	</head>
	<body data-basepath="<?php echo JUri::root(true); ?>">
		<!-- Header -->
		<div class="header">
			<img src="<?php echo $this->baseurl; ?>/template/images/joomla.png" alt="Joomla" />
			<hr />
			<h5>
				<?php // Fix wrong display of Joomla!® in RTL language ?>
				<?php $joomla  = '<a href="https://www.joomla.org" target="_blank">Joomla!</a><sup>' . (JFactory::getLanguage()->isRtl() ? '&#x200E;' : '') . '</sup>'; ?>
				<?php $license = '<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank">' . JText::_('INSTL_GNU_GPL_LICENSE') . '</a>'; ?>
				<?php echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla, $license); ?>
			</h5>
		</div>
		<!-- Container -->
		<div class="container">
			<jdoc:include type="message" />
			<div id="javascript-warning">
				<noscript>
					<div class="alert alert-error">
						<?php echo JText::_('INSTL_WARNJAVASCRIPT'); ?>
					</div>
				</noscript>
			</div>
			<div id="container-installation">
				<jdoc:include type="component" />
			</div>
			<hr />
		</div>
	</body>
</html>
