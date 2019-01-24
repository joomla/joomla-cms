<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<div class="pull-left">
	<?php echo JHtml::_('templates.thumb', $this->template->element, $this->template->client_id); ?>
	<?php echo JHtml::_('templates.thumbModal', $this->template->element, $this->template->client_id); ?>
</div>
<h2><?php echo ucfirst($this->template->element); ?></h2>
<?php $client = JApplicationHelper::getClientInfo($this->template->client_id); ?>
<p><?php $this->template->xmldata = TemplatesHelper::parseXMLTemplateFile($client->path, $this->template->element);?></p>
<p><?php  echo JText::_($this->template->xmldata->description); ?></p>