<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
<jdoc:include type="head" />

<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/rhuk_milkyway/css/template.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/rhuk_milkyway/css/<?php echo $this->params->get('colorVariation'); ?>.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/rhuk_milkyway/css/<?php echo $this->params->get('backgroundVariation'); ?>_bg.css" type="text/css" />
<!--[if lte IE 6]>
<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/ieonly.css" rel="stylesheet" type="text/css" />
<![endif]-->
<?php if ($this->direction == 'rtl') : ?>
	<link href="<?php echo $this->baseurl ?>/templates/rhuk_milkyway/css/template_rtl.css" rel="stylesheet" type="text/css" />
<?php endif; ?>

</head>
<body id="page_bg" class="color_<?php echo $this->params->get('colorVariation'); ?> bg_<?php echo $this->params->get('backgroundVariation'); ?> width_<?php echo $this->params->get('widthStyle'); ?>">
<a id="up"></a>
<div class="center" align="center">
	<div id="wrapper">
		<div id="wrapper_r">
			<div id="header">
				<div id="header_l">
					<div id="header_r">
						<div id="logo"></div>
						<jdoc:include type="modules" name="top" />
						<jdoc:include type="modules" name="position-12" />

					</div>
				</div>
			</div>

			<div id="tabarea">
				<div id="tabarea_l">
					<div id="tabarea_r">
						<div id="tabmenu">
						<table cellpadding="0" cellspacing="0" class="pill">
							<tr>
								<td class="pill_l">&#160;</td>
								<td class="pill_m">
								<div id="pillmenu">
									<jdoc:include type="modules" name="user3" />
									<jdoc:include type="modules" name="position-1" />
								</div>
								</td>
								<td class="pill_r">&#160;</td>
							</tr>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div id="search">
				<jdoc:include type="modules" name="user4" />
				<jdoc:include type="modules" name="position-0" />
			</div>

			<div id="pathway">
				<jdoc:include type="modules" name="breadcrumb" />
				<jdoc:include type="modules" name="position-2" />
			</div>

			<div class="clr"></div>

			<div id="whitebox">
				<div id="whitebox_t">
					<div id="whitebox_tl">
						<div id="whitebox_tr"></div>
					</div>
				</div>

				<div id="whitebox_m">
					<div id="area">
						<div id="leftcolumn">
						<?php if ($this->countModules('left')
								or $this->countModules('position-7')
						) : ?>
							<jdoc:include type="modules" name="left" style="rounded" />
							<jdoc:include type="modules" name="position-7" style="rounded" />
						<?php endif; ?>
						</div>

						<?php if ($this->countModules('left')
									or $this->countModules('position-7')
						) : ?>
						<div id="maincolumn">
						<?php else: ?>
						<div id="maincolumn_full">
						<?php endif; ?>
							<?php if ($this->countModules('user1')  or  $this->countModules('user2')
							or ($this->countModules('position-9')  or  $this->countModules('position-10') ) ) : ?>
								<table class="nopad user1user2">
									<tr valign="top">
										<?php if ($this->countModules('user1') or $this->countModules('position-9')) : ?>
											<td>
												<jdoc:include type="modules" name="user1" style="xhtml" />
												<jdoc:include type="modules" name="position-9" style="xhtml" />
											</td>
										<?php endif; ?>
										<?php if ($this->countModules('user1') or $this->countModules('position-9')
										and $this->countModules('user2') or $this->countModules('position-10')) : ?>
											<td class="greyline">&#160;</td>
										<?php endif; ?>
										<?php if ($this->countModules('user2') or $this->countModules('position-10')) : ?>
											<td>
												<jdoc:include type="modules" name="user2" style="xhtml" />
												<jdoc:include type="modules" name="position-10" style="xhtml" />
											</td>
										<?php endif; ?>
									</tr>
								</table>

								<div id="maindivider"></div>
							<?php endif; ?>

							<table class="nopad">
								<tr valign="top">
									<td>
										<jdoc:include type="message" />
										<jdoc:include type="component" />
										<jdoc:include type="modules" name="footer" style="xhtml"/>
										<jdoc:include type="modules" name="position-5" style="xhtml" />
										<jdoc:include type="modules" name="position-8"  style="xhtml" />
										<jdoc:include type="modules" name="position-11"  style="xhtml" />
										</td>
									<?php if (($this->countModules('right') or
											$this->countModules('position-3') or
											$this->countModules('position-4')
											)
									and JRequest::getCmd('layout') != 'form') : ?>
										<td class="greyline">&#160;</td>
										<td width="170">
											<jdoc:include type="modules" name="right" style="xhtml"/>
											<jdoc:include type="modules" name="position-3" style="xhtml"/>
											<jdoc:include type="modules" name="position-4" style="xhtml"/>
											</td>
									<?php endif; ?>
								</tr>
							</table>

						</div>
						<div class="clr"></div>
					</div>
					<div class="clr"></div>
				</div>

				<div id="whitebox_b">
					<div id="whitebox_bl">
						<div id="whitebox_br"></div>
					</div>
				</div>
			</div>

			<div id="footerspacer"></div>
		</div>

		<div id="footer">
			<div id="footer_l">
				<div id="footer_r">
					<p id="syndicate">
						<jdoc:include type="modules" name="syndicate" style="xtml" />
						<jdoc:include type="modules" name="position-14" style="xtml" />
					</p>
					<p id="power_by">
						<?php
						$joomla = '<a href="http://www.joomla.org">Joomla!®</a>';
						echo JText::sprintf('TPL_RHUK_MILKYWAY_POWERED', $joomla);
						$XHTML = '<a href="http://validator.w3.org/check/referer">XHTML</a>';
						$CSS = '<a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>';
						echo JText::sprintf('TPL_RHUK_MILKYWAY_VALID', $XHTML, $CSS) ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<jdoc:include type="modules" name="debug" />

</body>
</html>
