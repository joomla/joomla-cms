<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var PrivacyViewDashboard $this */

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/html');

JHtml::_('bootstrap.tooltip');

$totalRequests  = 0;
$activeRequests = 0;

?>
<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
	<div class="row-fluid">
		<div class="span6">
			<div class="well well-small">
				<h3 class="module-title nav-header"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_TOTAL_REQUEST_COUNT'); ?></h3>
				<div class="row-striped">
					<?php if (count($this->requestCounts)) : ?>
						<div class="row-fluid">
							<div class="span5"><strong><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_TYPE'); ?></strong></div>
							<div class="span5"><strong><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_STATUS'); ?></strong></div>
							<div class="span2"><strong><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_COUNT'); ?></strong></div>
						</div>
						<?php foreach ($this->requestCounts as $row) : ?>
							<div class="row-fluid">
								<div class="span5">
									<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_privacy&view=requests&filter[request_type]=' . $row->request_type . '&filter[status]=' . $row->status); ?>" data-original-title="<?php echo JText::_('COM_PRIVACY_DASHBOARD_VIEW_REQUESTS'); ?>">
										<strong><?php echo Text::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $row->request_type); ?></strong>
									</a>
								</div>
								<div class="span5"><?php echo JHtml::_('PrivacyHtml.helper.statusLabel', $row->status); ?></div>
								<div class="span2"><span class="badge badge-info"><?php echo $row->count; ?></span></div>
							</div>
							<?php if (in_array($row->status, array(0, 1))) : ?>
								<?php $activeRequests += $row->count; ?>
							<?php endif; ?>
							<?php $totalRequests += $row->count; ?>
						<?php endforeach; ?>
						<div class="row-fluid">
							<div class="span5"><?php echo Text::plural('COM_PRIVACY_DASHBOARD_BADGE_TOTAL_REQUESTS', $totalRequests); ?></div>
							<div class="span7"><?php echo Text::plural('COM_PRIVACY_DASHBOARD_BADGE_ACTIVE_REQUESTS', $activeRequests); ?></div>
						</div>
					<?php else : ?>
						<div class="row-fluid">
							<div class="span12">
								<div class="alert"><?php echo Text::_('COM_PRIVACY_DASHBOARD_NO_REQUESTS'); ?></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="span6">
			<div class="well well-small">
				<h3 class="module-title nav-header"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_STATUS_CHECK'); ?></h3>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span3"><strong><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_STATUS'); ?></strong></div>
						<div class="span9"><strong><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_CHECK'); ?></strong></div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<?php if ($this->privacyPolicyInfo['published'] && $this->privacyPolicyInfo['articlePublished']) : ?>
								<span class="label label-success">
									<span class="icon-checkbox" aria-hidden="true"></span>
									<?php echo Text::_('JPUBLISHED'); ?>
								</span>
							<?php elseif ($this->privacyPolicyInfo['published'] && !$this->privacyPolicyInfo['articlePublished']) : ?>
								<span class="label label-warning">
									<span class="icon-warning" aria-hidden="true"></span>
									<?php echo Text::_('JUNPUBLISHED'); ?>
								</span>
							<?php else : ?>
								<span class="label label-warning">
									<span class="icon-warning" aria-hidden="true"></span>
									<?php echo Text::_('COM_PRIVACY_STATUS_CHECK_NOT_AVAILABLE'); ?>
								</span>
							<?php endif; ?>
						</div>
						<div class="span9">
							<div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_PRIVACY_POLICY_PUBLISHED'); ?></div>
							<?php if ($this->privacyPolicyInfo['editLink'] !== '') : ?>
								<small><a href="<?php echo $this->privacyPolicyInfo['editLink']; ?>"><?php echo Text::_('COM_PRIVACY_EDIT_PRIVACY_POLICY'); ?></a></small>
							<?php else : ?>
								<?php $link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . $this->privacyConsentPluginId); ?>
								<small><a href="<?php echo $link; ?>"><?php echo Text::_('COM_PRIVACY_EDIT_PRIVACY_CONSENT_PLUGIN'); ?></a></small>
							<?php endif; ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<?php if ($this->requestFormPublished['published'] && $this->requestFormPublished['exists']) : ?>
								<span class="label label-success">
									<span class="icon-checkbox" aria-hidden="true"></span>
									<?php echo Text::_('JPUBLISHED'); ?>
								</span>
							<?php elseif (!$this->requestFormPublished['published'] && $this->requestFormPublished['exists']) : ?>
								<span class="label label-warning">
									<span class="icon-warning" aria-hidden="true"></span>
									<?php echo Text::_('JUNPUBLISHED'); ?>
								</span>
							<?php else : ?>
								<span class="label label-warning">
									<span class="icon-warning" aria-hidden="true"></span>
									<?php echo Text::_('COM_PRIVACY_STATUS_CHECK_NOT_AVAILABLE'); ?>
								</span>
							<?php endif; ?>
						</div>
						<div class="span9">
							<div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_REQUEST_FORM_MENU_ITEM_PUBLISHED'); ?></div>
							<?php if ($this->requestFormPublished['link'] !== '') : ?>
								<small><a href="<?php echo $this->requestFormPublished['link']; ?>"><?php echo $this->requestFormPublished['link']; ?></a></small>
							<?php endif; ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<?php if ($this->numberOfUrgentRequests === 0) : ?>
								<span class="label label-success">
									<span class="icon-checkbox" aria-hidden="true"></span>
									<?php echo Text::_('JNONE'); ?>
								</span>
							<?php else : ?>
								<span class="label label-important">
									<span class="icon-warning" aria-hidden="true"></span>
									<?php echo Text::_('WARNING'); ?>
								</span>
							<?php endif; ?>
						</div>
						<div class="span9">
							<div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_OUTSTANDING_URGENT_REQUESTS'); ?></div>
							<small><?php echo Text::plural('COM_PRIVACY_STATUS_CHECK_OUTSTANDING_URGENT_REQUESTS_DESCRIPTION', $this->urgentRequestDays); ?></small>
							<?php if ($this->numberOfUrgentRequests > 0) : ?>
								<small><a href="<?php echo Route::_('index.php?option=com_privacy&view=requests&filter[status]=1&list[fullordering]=a.requested_at ASC'); ?>"><?php echo JText::_('COM_PRIVACY_SHOW_URGENT_REQUESTS'); ?></a></small>
							<?php endif; ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<?php if ($this->sendMailEnabled) : ?>
								<span class="label label-success">
									<span class="icon-checkbox" aria-hidden="true"></span>
									<?php echo Text::_('JENABLED'); ?>
								</span>
							<?php else : ?>
								<span class="label label-important">
									<span class="icon-warning" aria-hidden="true"></span>
									<?php echo Text::_('JDISABLED'); ?>
								</span>
							<?php endif; ?>
						</div>
						<div class="span9">
							<?php if (!$this->sendMailEnabled) : ?>
								<div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_SENDMAIL_DISABLED'); ?></div>
								<small><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_SENDMAIL_DISABLED_DESCRIPTION'); ?></small>
							<?php else : ?>
								<div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_SENDMAIL_ENABLED'); ?></div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
