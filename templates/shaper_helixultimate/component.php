<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$theme_url = URI::base(true) . '/templates/'. $this->template;
$app = Factory::getApplication();
$option = $app->input->get('option', '', 'STRING');

$body_class = htmlspecialchars(str_replace('_', '-', $option));
$body_class .= ' view-' . htmlspecialchars($app->input->get('view', '', 'STRING'));
$body_class .= ' layout-' . htmlspecialchars($app->input->get('layout', 'default', 'STRING'));
$body_class .= ' task-' . htmlspecialchars($app->input->get('task', 'none', 'STRING'));

?>
<!doctype html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($favicon = $this->params->get('favicon')) : ?>
      <link rel="icon" href="<?php echo URI::base(true) . '/' . $favicon; ?>" />
    <?php else: ?>
      <link rel="icon" href="<?php echo $theme_url .'/images/favicon.ico'; ?>" />
      <!-- Apple Touch Icon (reuse 192px icon.png) -->
      <link rel="apple-touch-icon" href="<?php echo URI::base(true) . '/' . $favicon; ?>">

    <?php endif; ?>

    <jdoc:include type="head" />

    <?php if($option != 'com_sppagebuilder') : ?>
        <?php if(file_exists( \JPATH_THEMES . '/' . $this->template . '/css/bootstrap.min.css' )) : ?>
        <link href="<?php echo $theme_url . '/css/bootstrap.min.css'; ?>" rel="stylesheet">
        <?php else: ?>
        <link href="<?php echo URI::base(true) . '/plugins/system/helixultimate/css/bootstrap.min.css'; ?>" rel="stylesheet">
        <?php endif; ?>
        <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
    <?php endif; ?>
    
  </head>

  <body class="contentpane <?php echo $body_class; ?>">
    <jdoc:include type="component" />
  </body>
</html>
