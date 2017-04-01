
jQuery(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
	if (e.relatedTarget) {
		EventBus.dispatch('onDeactivate', this, e.relatedTarget.hash.replace('#attrib-', ''), document.getElementById('media-edit-file'));
	}
	EventBus.dispatch('onActivate', this, e.target.hash.replace('#attrib-', ''), document.getElementById('media-edit-file'));
});