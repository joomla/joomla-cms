<?php
/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2011 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$app = JFactory::getApplication();
if (!isset($this->params)) {$this->params = $app->getTemplate(true)->params;};

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
<head>

    <jdoc:include type="head" />

    <link href="templates/<?php echo $this->template ?>/css/template.min.css" rel="stylesheet" type="text/css" />

</head>
<body id="front" class="error">
    <div id="content" class="error-box">
            <h1><?php echo $this->error->getCode() ?> - <?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED') ?></h1>
                <p><?php echo $this->error->getMessage(); ?></p>
                <a href="index.php"><?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT') ?></a>
                <?php if ($this->debug) : ?>
                <p><?php echo $this->renderBacktrace();?></p>
                <?php endif; ?>
        <noscript>
            <?php echo  JText::_('WARNJAVASCRIPT') ?>
        </noscript>
    </div><!-- /#content-->
</div>
</body>
</html>
