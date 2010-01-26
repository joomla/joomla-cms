<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
<jdoc:include type="head" />
</head>
<body>
<table border="1">
	<tr><td colspan="2"><h1>Simple Template</h1></td></tr>
	<?php if($this->countModules('top')) { ?>
	<tr><td colspan="2"><jdoc:include type="modules" name="top" /></td></tr>
	<?php } ?>
	<tr>
		<td><jdoc:include type="modules" name="left" /></td>
		<td>
			<p><jdoc:include type="message" /></p>
			<jdoc:include type="component" />

		</td>
	</tr>
	<?php if($this->countModules('footer')) { ?>
	<tr>
		<td colspan="2"><jdoc:include type="modules" name="footer" style="xhtml"/></td>
	</tr>
	<?php } ?>
</table>
<jdoc:include type="modules" name="debug" />
</body>
</html>
