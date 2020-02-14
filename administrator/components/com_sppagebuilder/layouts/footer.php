<?php
defined('_JEXEC') or die();

$doc = JFactory::getDocument();
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/common.css' );
?>
<div class="pagebuilder-footer clearfix">
	<div class="sp-pagebuilder-row">
		<div class="col-md-5">
			<div class="copyright-info">
				Designed &amp; Developed with <i class="fa fa-heart"></i> by <a href="http://www.joomshaper.com" target="_blank">JoomShaper</a>
			</div>
		</div>

		<div class="col-md-7">
			<div class="pagebuilder-links">
				<ul>
					<li>
						<a target="_blank" href="https://www.joomshaper.com/documentation/sp-page-builder/sp-page-builder-3">
							Guide
						</a>
					</li>

					<li>
						<a target="_blank" href="https://www.youtube.com/playlist?list=PL43bbfiC0wjhKrnJ1XRhSj3Sla8CJ1Jxd">
							Videos
						</a>
					</li>

					<li>
						<a target="_blank" href="https://www.joomshaper.com/forums/categories/sppagebuilder3">
							Support
						</a>
					</li>

					<li>
						<a target="_blank" href="https://www.facebook.com/groups/JoomlaPageBuilderCommunity/">
							Community
						</a>
					</li>

					<li>
						<a target="_blank" href="https://www.transifex.com/joomshaper/sp-page-builder-3/">
							Find &amp; Help Translate
						</a>
					</li>

					<li>
						<a target="_blank" href="http://extensions.joomla.org/extension/sp-page-builder">
							<img src="<?php echo JURI::base(true) . '/components/com_sppagebuilder/assets/img/joomla.png'; ?>" alt="JED"> Rate on JED
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
