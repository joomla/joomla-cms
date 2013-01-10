<?php
/**
 * @package    Joomla.Administrator
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $doc->getLanguage(); ?>" lang="<?php echo $doc->getLanguage(); ?>" dir="<?php echo $doc->getDirection(); ?>">
	<head>
		<title><?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></title>
		<!-- Add Stylesheets -->
		<link rel="stylesheet" href="../media/system/css/system.css" type="text/css" />
		<link rel="stylesheet" href="../media/jui/css/bootstrap.css" type="text/css" />
		<link rel="stylesheet" href="../media/jui/css/bootstrap-extended.css" type="text/css" />
		<link rel="stylesheet" href="../media/jui/css/bootstrap-responsive.css" type="text/css" />
		<style type="text/css">
		.header {
			background-color: #FFFFFF;
			background-image: linear-gradient(#D9EFFA, #D9EFFA 25%, #FFFFFF);
			background-repeat: no-repeat;
			border-top: 3px solid #0088CC;
			padding: 20px 0;
			text-align: center;
		}
		</style>
		<!--[if lt IE 9]>
		<script src="../media/jui/js/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<!-- Header -->
		<div class="header">
			<img src="../media/jui/img/joomla.png" alt="Joomla" />
			<hr />
		</div>
		<!-- Container -->
		<div class="container">
			<div id="system-message-container">
				<div id="system-message">
					<div class="alert alert-error">
						<h3><?php echo $error; ?></h3>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>