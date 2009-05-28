<?php
require_once("config_tinybrowser.php");

$tbpath = pathinfo($_SERVER['SCRIPT_NAME']);
$tbmain = $tbpath['dirname'].'/tinybrowser.php';
?>

function tinyBrowserPopUp(type,formelementid,folder) {
   tburl = "<?php echo $tbmain; ?>" + "?type=" + type + "&feid=" + formelementid;
   if (folder !== undefined) tburl += "&folder="+folder+"%2F";
   newwindow=window.open(tburl,'tinybrowser','height=<?php echo $tinybrowser['window']['height']+15; ?>,width=<?php echo $tinybrowser['window']['width']+15; ?>,scrollbars=yes,resizable=yes');
   if (window.focus) {newwindow.focus()}
   return false;
}
