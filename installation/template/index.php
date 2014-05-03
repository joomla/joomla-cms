<?php
/**
 * @version		$Id$
 * @package		Jokte.Installation
 * @copyright	Copyleft 2012 - 2014 Comunidad Juuntos y Jokte.org
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Add Stylesheets
$doc->addStyleSheet('../media/system/css/system.css');
$doc->addStyleSheet('template/css/template.css');

if ($this->direction == 'rtl') {
	$doc->addStyleSheet('template/css/template_rtl.css');
}

// Load the JavaScript behaviors
JHtml::_('behavior.framework', true);
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('stylesheet', 'http://fonts.googleapis.com/css?family=Ubuntu', true, false, false, false);
JHtml::_('script', 'installation/template/js/installation.js', true, false, false, false);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<jdoc:include type="head" />
	<!--[if IE 7]>
		<link href="template/css/ie7.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<script type="text/javascript">
		window.addEvent('domready', function() {
		window.Install = new Installation('rightpad', '<?php echo JURI::current(); ?>');
		Locale.define('<?php echo JFactory::getLanguage()->getTag(); ?>', 'installation', {
		sampleDataLoaded: '<?php echo JText::_('INSTL_SITE_SAMPLE_LOADED', true); ?>'
		});
		Locale.use('<?php echo JFactory::getLanguage()->getTag(); ?>');
	});
	</script>
</head>
<body>
	<div id="header">
		<h1 class=""><?php echo JText::_('INSTL_INSTALLATION') ?></h1>
		<h3><?php echo JText::_('INSTL_CMS_NAME') . ' ' . VJOKTE; ?> </h3>
		<h2><?php echo JText::_('INSTL_LATAM'); ?> | </h2>
	</div>
	
	<jdoc:include type="message" />
	
	<div id="content-box">
		<div id="content-pad">
			<div id="stepbar">
				<?php JHTML::_('behavior.modal'); ?>
				<p>
					<a rel="{handler: 'iframe', size: {x: 750, y: 600}}" href="gpl.html" target="_blank" class="modal"><?php echo JText::_('INSTL_CMS_GPL'); ?></a>
				</p>
				<p>
					<a rel="{handler: 'iframe', size: {x: 750, y: 600}}" href="contrasoc.html" target="_blank" class="modal"><?php echo JText::_('INSTL_CMS_AGREEMENT'); ?></a>
				</p>
				<p class="stepBarrHigthLight">
					<a rel="{handler: 'iframe', size: {x: 750, y: 600}}" href="http://juuntos.org/foro.html" target="_blank" class="modal"><?php echo JText::_('INSTL_HELP'); ?></a>
				</p>
				<p class="stepBarrLnk">
					<a href="http://jokte.org" target="_blank">Jokte.org</a>
				</p>
				<p class="stepBarrLnk">
					<a href="http://juuntos.net" target="_blank">Juuntos.net</a>
				</p>
			</div>
			<div id="warning">
				<noscript>
					<div id="javascript-warning">
						<?php echo JText::_('INSTL_WARNJAVASCRIPT'); ?>
					</div>
				</noscript>
			</div>
			<div id="right">
				<div id="rightpad">
					<jdoc:include type="installation" />
				</div>
			</div>
			<div class="clr"></div>
		</div>
	</div>
	<div id="Jkfooter" class="Jkadmin">
		<p class="copyleft">
			<a href="http://www.jokte.org" target="_blank" title="Jokte! 100% Latinoamericano y Libre">
				<?php echo JText::_('INSTL_CMS_URL'); ?>
			</a> 
			<?php echo JText::_('INSTL_CMS_SLOGAN'); ?> 
			<a href="http://juuntos.net" target="_blank" title="Comunidad Latinaomericana de Tecnología Web">Comunidad Juuntos </a>  
		</p>
	</div>
</body>
</html>
