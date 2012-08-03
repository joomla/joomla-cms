<?php
/**
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/

defined('_JEXEC') or die;
if (!isset($this->error)) {
	$this->error = JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	$this->debug = false;
}

$params = JFactory::getApplication()->getTemplate(true)->params;
$logo =  $params->get('logo');
$errorskin = $params->get('errorskin');
$typobody 	= $params->get('typobody');			
$typoespecial 	= $params->get('typoespecial');	
$skinerrorurl = $this->baseurl.DS.'templates'.DS.$this->template.DS.'css/skins/'.$errorskin;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<title><?php echo $this->error->getCode(); ?> - <?php echo $this->title; ?></title>
	<link rel="stylesheet" href="<?php echo $this->baseurl ;?>/templates/<?php echo $this->template ;?>/css/template.css" type="text/css" />   
	 <link type="text/css" rel="stylesheet" href="<?php echo $skinerrorurl; ?>/color.css" />
	 
	 <?php if ($typobody != "no-google") { ?>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $typobody; ?>" />
<?php } ?>
<?php if ($typoespecial != "no-google") { ?>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $typoespecial; ?>" />
<?php } ?>

<style type="text/css"> 
<?php if ($typobody != "no-google") { ?>
body {font-family: '<?php echo $typobody; ?>',FreeSans,Verdana, Geneva,Helvetica,Arial,Sans-Serif;
} 
<?php } ?>
<?php if ($typoespecial != "no-google") { ?>
#errorprincipal,h1 {
font-family:'<?php echo $typoespecial; ?>',gargi,Verdana, Geneva, sans-serif;}
<?php } ?>
</style> 
</head>
<body>
	<div class="bodyBg" style="width:800px; margin:20px auto";>
		<div id="user1" class="error">
						<?php
								$params = JFactory::getApplication()->getTemplate(true)->params;
								$logo =  $params->get('logo');
							?>

							<?php jimport( 'joomla.application.module.helper' ); ?>

						 <div id="logo">

                                        <?php if ($logo): ?>
                                        <img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($logo); ?>"  alt="<?php echo htmlspecialchars($params->get('sitetitle'));?>" />
                                        <?php endif;?>
                                        <?php if (!$logo ): ?>
                                        <h1>
                                        <?php echo htmlspecialchars($params->get('sitename'));?></h1>
                                        <?php endif; ?>
                                        <small>
                                        <?php echo htmlspecialchars($params->get('siteslogan'));?>
                                        </small>
					</div>
					</div>
		<div id="contenedor">
		<div id="margen">
			<div id="errorprincipal">
				<div class="n-error">
					<h1>
				<?php echo $this->error->getCode(); ?> </h1>
				<span class="mensaje-error">
				<?php echo $this->error->getMessage(); ?>
				</span>
				</div>
				</div> 
			<div id="errorcomentarios">
			<p><strong><?php echo JText::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></strong></p>
				<ol>
					<li><?php echo JText::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></li>
				</ol>
			<p><strong><?php echo JText::_('JERROR_LAYOUT_PLEASE_TRY_ONE_OF_THE_FOLLOWING_PAGES'); ?></strong></p>

				<ul>
					<li><a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></li>
					<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_search" title="<?php echo JText::_('JERROR_LAYOUT_SEARCH_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_SEARCH_PAGE'); ?></a></li>

				</ul>

			<p><?php echo JText::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?>.</p>
			<p><?php echo $this->error->getMessage(); ?></p>
	</div>

			<div id="copyleft-error"style="text-align: center;"><a target="_blank" href="http://juuntos.org"><img border="0" title="Sistema licenciado bajo el espiritu COPYLEFT" alt="Copyleft latinoamericano" src="/jokte/images/copyleft.png"></a>
		<p>JOKTE! es Software Libre distribuido bajo la Licencia GPL. El nombre y el logo de Jokte! se pueden usar sin restricciones.</p>

<p>Jokte! es mantenido por la Comunidad de usuarios <a target="_blank" href="http://juuntos.org">Juuntos.org</a></p>	</div>
			<div id="techinfo">
			
			<p>
				<?php if ($this->debug) :
					echo $this->renderBacktrace();
				endif; ?>
			</p>
			</div>
			</div>
		</div>
		</div>
		
	
</body>
</html>
