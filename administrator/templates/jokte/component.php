<?php
/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2011 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
<head>
	<jdoc:include type="head" />
	<link href="templates/<?php echo $this->template ?>/css/libs/template.reset.css" rel="stylesheet">
	<link href="templates/<?php echo $this->template ?>/css/libs/template.buttons.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/libs/template.forms.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/libs/template.icons.css" rel="stylesheet">
    <link href="templates/<?php echo  $this->template ?>/css/template.css" rel="stylesheet" />
</head>
<body class="contentpane">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
