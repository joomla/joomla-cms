<? 
/**
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $this->language; ?>" lang="<?= $this->language; ?>" dir="<?= $this->direction; ?>">
<head>
	<title>500 - {SITENAME}</title>
	<base href="{BASE_HREF}" />
	<link rel="stylesheet" href="templates/_system/css/error.css" type="text/css" />
</head>
<body>
	<div align="center">
		<div id="outline">
		<div id="errorboxoutline">
			<div id="errorboxheader">500 - An error has occured</div>
			<div id="errorboxbody">
			<p>An error has occurred while processing your request.</p>
			<p><strong>Please try one of the following pages:</strong></p>
			<p>
				<ul>
					<li><a href="index.php" title="Go to the home page">Home Page</a></li>
				</ul>
			</p>
			<p>If difficulties persist, please contact the system administrator of this site.</p>
			<div id="techinfo">
			<p>{MESSAGE}</p>
			<p>{BACKTRACE}</p>
			</div>
			</div>
		</div>
		</div>
	</div>
</body>
</html>