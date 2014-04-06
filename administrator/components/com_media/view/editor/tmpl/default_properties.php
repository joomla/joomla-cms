<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

?>

<p class="well well-small lead">

<?php 

echo 'Created By: ' . $this->item->core_created_user_id . '<br />';
echo 'Created On: ' . $this->item->core_created_time . '<br />';
echo '<br />';
echo 'Modified By: ' . $this->item->core_modified_user_id . '<br />';
echo 'Modified On: ' . $this->item->core_modified_time . '<br />';
echo '<br />';
echo 'Title: ' . $this->item->core_title . '<br />';
echo 'Alias: ' . $this->item->core_alias . '<br />';
echo 'Body: ' . $this->item->core_body . '<br />';
echo '<br />';
print_r($this->item);
?>


</p>