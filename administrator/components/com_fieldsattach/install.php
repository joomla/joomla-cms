<?php
defined('JPATH_PLATFORM') or die;
jimport('joomla.installer.helper');
//TEXT *****************************************************************************
?>
<div style=" font-size:12px; margin:0px 0 0px 0; padding:5px; position:relative; float:right;"><div>Powered by Percha.com</div></div>
<h2>FIELDSATTACHMENT COMPONENT fOR JOOMLA</h2>
<div style=" font-size:17px; margin:8px 0 8px 0; padding:5px;">Thanks for install the fieldsattach component.</div>
<?php 
//INSTALL THE PLUGINS *******************************************************************************
$installer = new JInstaller();
//$installer->_overwrite = true; 
$pkg_path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fieldsattach'.DS.'extensions'.DS;
$pkgs = array( 
		'input.zip'=>'Plugin fieldsattachment Input' ,
		'link.zip'=>'Plugin fieldsattachment Link' ,
		'checkbox.zip'=>'Plugin fieldsattachment Checbox' ,
		'file.zip'=>'Plugin fieldsattachment File' ,
		'image.zip'=>'Plugin fieldsattachment image' ,
		'imagegallery.zip'=>'Plugin fieldsattachment imagegallery' ,
		'listunits.zip'=>'Plugin fieldsattachment listunits' ,
		'select.zip'=>'Plugin fieldsattachment select' , 
		'selectmultiple.zip'=>'Plugin fieldsattachment select multiple' , 
		'textarea.zip'=>'Plugin fieldsattachment textarea' ,
		'vimeo.zip'=>'Plugin fieldsattachment vimeo' ,
		'youtube.zip'=>'Plugin fieldsattachment youtube' ,  
	  	'content_fieldsattachment.zip'=>'Plugin Content FieldsAttachment',
		'system_fieldsattachment.zip'=>'Plugin System FieldsAttachment',
		'search_fieldsattachment.zip'=>'Plugin Search FieldsAttachment'
		
             );

foreach( $pkgs as $pkg => $pkgname ):
  $package = JInstallerHelper::unpack( $pkg_path.$pkg );
  if( $installer->install( $package['dir'] ) )
  {
    $msgcolor = "#009900";
    $msgtext  = "$pkgname successfully installed.";

	//ACTIVE IT
	 
  }
  else
  {
    $msgcolor = "#880000";
    $msgtext  = "ERROR: Could not install the $pkgname. Please install manually.";
  }

//ACTIVE THE PLUGINS *******************************************************************************

$db =& JFactory::getDBO();
$sql =  "UPDATE #__extensions  SET enabled = 1 WHERE  element = 'fieldsattachment'";
$db->setQuery($sql);
$db->query(); 

$db =& JFactory::getDBO();
$sql =  "UPDATE #__extensions  SET enabled = 1 WHERE  folder = 'fieldsattachment'";
$db->setQuery($sql);
$db->query();

  ?>
  <div style="border:1px dashed <?php echo $msgcolor; ?>; font-size:14px; margin:8px 0 8px 0; padding:5px;" ><?php echo $msgtext; ?></div>   
<?php
JInstallerHelper::cleanupInstall( $pkg_path.$pkg, $package['dir'] ); 
endforeach; 



?> 
<div style="border:1px dashed #009900;  font-size:14px; margin:8px 0 8px 0; padding:5px;">Component install</div>
<div style="border:1px dashed #F00; background-color:#f33; color:#FFF;  font-size:17px; margin:8px 0 8px 0; padding:5px;">Component install OK</div>
