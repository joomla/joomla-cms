<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
?>
<style>
.phpversion-container{
  max-width: 960px;
  margin: 0 auto;
}
.sppb-callout {
    padding: 10px 20px;
    margin: 20px 0;
    border: 1px solid transparent;
    border-left-width: 5px;
    border-radius: 3px;
}

.sppb-callout-danger {
    border-left-color: #ce4844;
}

.sppb-callout-danger h4 {
    color: #ce4844;
}
</style>
<div class="phpversion-container">
    <div class="sppb-callout sppb-callout-danger" id="callout-progress-animation-css3">
      <h4>Your current PHP version <?php echo PHP_VERSION; ?> is too old for SP Page Builder</h4>
      <p>We are strongly recommended to use PHP <strong><?php echo $required_min_php_version; ?></strong> or higher. Please contact your web hosting provider's support/server administrator for help.</p>
    </div>
</div>
