<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.acorn
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;
require_once '/templates/' . $this->template . '/framework/init.php';
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html class="no-js lt-ie9" lang="<?php echo $htmlLang; ?>">
<![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="<?php echo $htmlLang; ?>">
<!--<![endif]-->
<head>
	<?php include_once 'templates/' . $this->template . '/framework/head_include.php'; ?>
</head>
<body id="main" class=site
"<?php echo $bodyclass . " " . $pageclass . " " . $loggedin . " " . $rtl_detection; ?>">
<div class="wrapper">
	<jdoc:include type="message"/>
	<jdoc:include type="component"/>
</div>
</body>
</html>
