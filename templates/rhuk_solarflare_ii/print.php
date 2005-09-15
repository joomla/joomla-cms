<?php
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// xml prolog
echo '<?xml version="1.0" encoding="'. $_LANG->iso() .'"?' .'>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta  >
<head>
<?php mosShowHead_print(); ?>
<meta name="robots" content="index, follow" />
</head>
<body class="contentpane">

<div style="font-weight: bold; font-size: 15px; text-align: center;">
	PRINTED VERSION
</div>
<div style="text-align: center;">
	<?php echo $mosConfig_live_site; ?>
</div>

<?php mosMainBody(); ?>

<span style="font-weight: bold;">
	Printed From:
</span>
<?php echo @$_SERVER['HTTP_REFERER']; ?>

<br/><br/>

<?php mosFS::load( 'includes/footer.php' ); ?>
</body>
</html>