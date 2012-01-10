<?php
$msgtext ='';
jimport('joomla.installer.helper');
$msgtext ='<div style=" font-size:14px; ">UNINSTALL FieldsAttach in content article for Joomla</div>'; 
$msgtext .= '<div style="border:1px dashed #cccccc; font-size:14px; margin:8px 0 8px 0; padding:5px;" >';
$msgtext .= 'Component uninstall FieldsAttach success';
$msgtext .= '</div>';

$db =& JFactory::getDBO();
$sql =  "UPDATE #__extensions  SET enabled = 0 WHERE  folder = 'fieldsattachment'";
$db->setQuery($sql);
$db->query();

$db =& JFactory::getDBO();
$sql =  "UPDATE #__extensions  SET enabled = 0 WHERE  element = 'fieldsattachment'";
$db->setQuery($sql);
$db->query(); 

 
$db =& JFactory::getDBO();

$db->setQuery("select extension_id , name  from #__extensions where folder = 'fieldsattachment'");
$plugins = $db->loadObjectList();
if($plugins)
{
	foreach($plugins as $plugin)
	{
		$plugin_uninstaller = new JInstaller;
		$msgtext .= '<div style="border:1px dashed #cccccc; font-size:14px; margin:8px 0 8px 0; padding:5px;" >';
		if($plugin_uninstaller->uninstall('plugin', $plugin->extension_id))
		    $msgtext .= 'Plugin '.$plugin->name.' uninstall fieldsattachment success <br />';
		else
		    $msgtext .=  'Plugin '.$plugin->name.' uninstall fieldsattachment failed<br />';
		$msgtext .= '</div>'; 
	}
}




//****************** 
$db->setQuery("select extension_id , name  from #__extensions where element = 'fieldsattachment'");
$plugins = $db->loadObjectList();
if($plugins)
{
	foreach($plugins as $plugin)
	{
		$plugin_uninstaller = new JInstaller;
		$msgtext .= '<div style="border:1px dashed #cccccc; font-size:14px; margin:8px 0 8px 0; padding:5px;" >';
		if($plugin_uninstaller->uninstall('plugin', $plugin->extension_id))
		    $msgtext .= 'Plugin '.$plugin->name.' uninstall fieldsattachment success <br />';
		else
		    $msgtext .=  'Plugin '.$plugin->name.' uninstall fieldsattachment failed<br />';
		$msgtext .= '</div>'; 
	}
}

 
?>
<?php echo $msgtext; ?>
   
