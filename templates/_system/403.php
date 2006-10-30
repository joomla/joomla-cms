<?php
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
	<title>403 - {SITENAME}</title>
	<base href="{BASE_HREF}" />
	<link rel="stylesheet" href="templates/_system/css/error.css" type="text/css" />
</head>
<body>
	<div align="center">
		<div id="outline">
		<div id="errorboxoutline">
			<div id="errorboxheader">403 - Access denied</div>
			<div id="errorboxbody">
			<p><strong>You may not be able to visit this page because of:</strong></p>
				<ol>
					<li>An <strong>out-of-date bookmark/favourite</strong></li>
					<li>A search engine that has an <strong>out-of-date listing for this site</strong></li>
					<li>A <strong>mis-typed address</strong></li>
					<li>You have <strong>no access</strong> to this page</li>
					<li>The requested resource was <strong>not found</strong></li>
				</ol>
			<p><strong>Please try one of the following pages:</strong></p>
			<p>
				<ul>
					<li><a href="index.php" title="Go to the home page">Home Page</a></li>
				</ul>
			</p>
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