<?php
require_once('config_tinybrowser.php');
// Set language
if(isset($tinybrowser['language']) && file_exists('langs/'.$tinybrowser['language'].'.php'))
	{
	require_once('langs/'.$tinybrowser['language'].'.php'); 
	}
else
	{
	require_once('langs/en.php'); // Falls back to English
	}
require_once('fns_tinybrowser.php');

// Check session, if it exists
if(session_id() != '')
	{
	if(!isset($_SESSION[$tinybrowser['sessioncheck']]))
		{
		echo TB_DENIED;
		exit;
		}
	}
	
if(!$tinybrowser['allowfolders'])
	{
	echo TB_FODENIED;
	exit;
	}

// Assign request / get / post variables
$validtypes = array('image','media','file');
$typenow = ((isset($_GET['type']) && in_array($_GET['type'],$validtypes)) ? $_GET['type'] : 'image');
$foldernow = str_replace(array('../','..\\','./','.\\'),'',($tinybrowser['allowfolders'] && isset($_REQUEST['folder']) ? urldecode($_REQUEST['folder']) : ''));
$dirpath = $tinybrowser['path'][$typenow];
$passfolder = '&folder='.urlencode($foldernow);
$passfeid = (isset($_GET['feid']) && $_GET['feid']!='' ? '&feid='.$_GET['feid'] : '');



// Assign browsing options
$actionnow = (isset($_POST['editaction']) ? $_POST['editaction'] : 'create' );

// Initalise alert array
$notify = array(
	'type' => array(),
	'message' => array()
);
$createqty = 0;
$deleteqty = 0;
$renameqty = 0;
$errorqty = 0;
	
// Create any child folders with entered name
if(isset($_POST['createfolder']))
	{
	foreach($_POST['createfolder'] as $parent => $newfolder)
		{
		if($newfolder != '')
			{
			$createthisfolder = $tinybrowser['docroot'].$dirpath.urldecode($_POST['actionfolder'][$parent]).clean_filename($newfolder);
			if (!file_exists($createthisfolder) && createfolder($createthisfolder,$tinybrowser['unixpermissions'])) $createqty++; else $errorqty++;
			if($typenow=='image')
			   {
				createfolder($createthisfolder.'/_thumbs/',$tinybrowser['unixpermissions']);
			   }
			}
		}
	}
	
// Delete any checked folders
if(isset($_POST['deletefolder']))
	{
	foreach($_POST['deletefolder'] as $delthis => $val)
		{
		if($typenow=='image')
			{
			$delthisthumbdir = $tinybrowser['docroot'].$dirpath.urldecode($_POST['actionfolder'][$delthis]).'_thumbs/';
			if (is_dir($delthisthumbdir)) rmdir($delthisthumbdir);
			}
		$delthisdir = $tinybrowser['docroot'].$dirpath.urldecode($_POST['actionfolder'][$delthis]);
		if (is_dir($delthisdir) && rmdir($delthisdir)) $deleteqty++; else $errorqty++;
		if($foldernow==urldecode($_POST['actionfolder'][$delthis]))
         {
         $foldernow = '';
         $passfolder = '';
         }
		}

	}
	
// Rename any folders with changed name
if(isset($_POST['renamefolder']))
	{
	foreach($_POST['renamefolder'] as $namethis => $newname)
		{
      $urlparts = explode('/',rtrim(urldecode($_POST['actionfolder'][$namethis]),'/'));
		if(array_pop($urlparts) != $newname)
			{
			$namethisfolderfrom = $tinybrowser['docroot'].$dirpath.urldecode($_POST['actionfolder'][$namethis]);
         $renameurl = implode('/',$urlparts).'/'.clean_filename($newname).'/';
			$namethisfolderto = $tinybrowser['docroot'].$dirpath.$renameurl;
			if (is_dir($namethisfolderfrom) && rename($namethisfolderfrom,$namethisfolderto)) $renameqty++; else $errorqty++;
			if($foldernow==urldecode($_POST['actionfolder'][$namethis]))
            {
            $foldernow = ltrim($renameurl,'/');
            $passfolder = '&folder='.urlencode(ltrim($renameurl,'/'));
            }
			}
		}
	}

// Assign directory structure to array
$dirs=array();
dirtree($dirs,$tinybrowser['filetype'][$typenow],$tinybrowser['docroot'],$tinybrowser['path'][$typenow]);

// generate alert if folders deleted
if($createqty>0)
   {
	$notify['type'][]='success';
	$notify['message'][]=sprintf(TB_MSGCREATE, $createqty);
	}
// generate alert if folders deleted
elseif($deleteqty>0)
   {
	$notify['type'][]='success';
	$notify['message'][]=sprintf(TB_MSGDELETE, $deleteqty);
	}
// generate alert if folders renamed
elseif($renameqty>0)
   {
	$notify['type'][]='success';
	$notify['message'][]=sprintf(TB_MSGRENAME, $renameqty);
	}
	
// generate alert if file errors encountered
if($errorqty>0)
   {
	$notify['type'][]='failure';
	$notify['message'][]=sprintf(TB_MSGEDITERR, $errorqty);
	}
	
// count folders
$num_of_folders = (isset($dirs) ? count($dirs) : 0);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TinyBrowser :: <?php echo TB_FOLDERS; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Pragma" content="no-cache" />
<?php
if($passfeid == '' && $tinybrowser['integration']=='tinymce')
	{
	?><link rel="stylesheet" type="text/css" media="all" href="<?php echo $tinybrowser['tinymcecss']; ?>" /><?php 
	}
else
	{
	?><link rel="stylesheet" type="text/css" media="all" href="css/stylefull_tinybrowser.css" /><?php 
	}
?>
<link rel="stylesheet" type="text/css" media="all" href="css/style_tinybrowser.css.php" />
<script language="javascript" type="text/javascript" src="js/tinybrowser.js.php"></script>
</head>
<body onload="rowHighlight();">
<?php
if(count($notify['type'])>0) alert($notify);
form_open('foldertab',false,'folders.php','?type='.$typenow.$passfeid);
?>
<div class="tabs">
<ul>
<li id="browse_tab"><span><a href="tinybrowser.php?type=<?php echo $typenow.$passfolder.$passfeid ; ?>"><?php echo TB_BROWSE; ?></a></span></li>
<?php
if($tinybrowser['allowupload']) 
	{
	?><li id="upload_tab"><span><a href="upload.php?type=<?php echo $typenow.$passfolder.$passfeid ; ?>"><?php echo TB_UPLOAD; ?></a></span></li>
	<?php 
	}
if($tinybrowser['allowfolders'])
	{
   ?><li id="edit_tab"><span><a href="edit.php?type=<?php echo $typenow.$passfolder.$passfeid ; ?>"><?php echo TB_EDIT; ?></a></span></li>
   <?php
   }
?><li id="folders_tab" class="current"><span><a href="folders.php?type=<?php echo $typenow.$passfolder.$passfeid; ?>"><?php echo TB_FOLDERS; ?></a></span></li>
</ul>
</div>
</form>
<div class="panel_wrapper">
<div id="general_panel" class="panel currentmod">
<fieldset>
<legend><?php echo TB_FOLDERS; ?></legend>
<?php
form_open('edit','custom','folders.php','?type='.$typenow.$passfolder.$passfeid);
?>
<div class="pushleft">
<?php

// Assign edit actions based on file type and permissions
$select = array();
if($tinybrowser['allowfolders']) $select[] = array('create',TB_CREATE);
if($tinybrowser['allowdelete']) $select[] = array('delete',TB_DELETE);
if($tinybrowser['allowedit']) $select[] = array('rename',TB_RENAME);

form_select($select,'editaction',TB_ACTION,$actionnow,true);
?></form></div><?php

form_open('actionform','custom','folders.php','?type='.$typenow.$passfolder.$passfeid);

if($actionnow=='move')
   { ?><div class="pushleft"><?php
   form_select($editdirs,'destination',TB_FOLDERDEST,urlencode($foldernow),false);
   ?></div><?php
   } 

switch($actionnow) 
	{
	case 'delete':
		$actionhead = TB_DELETE;
		break;
	case 'rename':
		$actionhead = TB_RENAME;
		break;
	case 'create':
		$actionhead = TB_CREATE;
		break;
	default:
		// do nothing
	}
?><div class="tabularwrapper"><table class="browse"><tr>
<th class="nohvr"><?php echo TB_FOLDERNAME; ?></th>
<th class="nohvr"><?php echo TB_FILES; ?></th>
<th class="nohvr"><?php echo TB_DATE; ?></th>
<th class="nohvr"><?php echo $actionhead; ?></th></tr>
<?php

for($i=0;$i<$num_of_folders;$i++)
	{
	$disable = ($i == 0 ? true : false);
	$alt = (IsOdd($i) ? 'r1' : 'r0');
	echo '<tr class="'.$alt.'">';
	echo '<td>'.$dirs[$i][2].'</td>';
	echo '<td>'.$dirs[$i][4].'</td><td>'.date($tinybrowser['dateformat'],$dirs[$i][5]).'</td>'
	.'<td>';
	form_hidden_input('actionfolder['.$i.']',$dirs[$i][0]);
	switch($actionnow) 
		{
		case 'create':
         echo '&rarr; ';
			form_text_input('createfolder['.$i.']',false,'',30,120);
			break;
		case 'delete':
         $disabledel = ($dirs[$i][4] > 0 ? ' DISABLED' : '');
			if(!$disable) echo '<input class="del" type="checkbox" name="deletefolder['.$i.']" value="1"'.$disabledel.' />';
			break;
		case 'rename':
			if(!$disable) form_text_input('renamefolder['.$i.']',false,$dirs[$i][3],30,120);
			break;
		default:
			// do nothing
		}
	echo "</td></tr>\n";
	}

echo "</table></div>\n".'<div class="pushright">';
if($tinybrowser['allowdelete'] && $tinybrowser['allowedit'])
	{
	form_hidden_input('editaction',$actionnow);
	form_submit_button('commit',$actionhead.' '.TB_FOLDERS,'edit');
	}
?>
</div></fieldset></div></div>
</body>
</html>
