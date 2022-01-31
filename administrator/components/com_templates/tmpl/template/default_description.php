<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;

?>

<div class="clearfix">
	<div class="float-start me-3 text-center">
		<?php echo HTMLHelper::_('templates.thumb', $this->template); ?>
		<?php echo HTMLHelper::_('templates.thumbModal', $this->template); ?>
	</div>
	<h2><?php echo ucfirst($this->template->element); ?></h2>
	<?php $client = ApplicationHelper::getClientInfo($this->template->client_id); ?>
	<p><?php $this->template->xmldata = TemplatesHelper::parseXMLTemplateFile($client->path, $this->template->element); ?></p>
	<p><?php echo Text::_($this->template->xmldata->get('description')); ?></p>
</div>
