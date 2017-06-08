var tour = new Shepherd.Tour({
	defaults: {
		classes: 'shepherd-theme-arrows'
	}
});

tour.addStep('Step1', {
	title: 'Title 1',
	text: 'Text 1 ...',
	attachTo: {element: '#toolbar', on: 'bottom'},
	buttons: [{
		text  : 'Next',
		action: tour.next
	}, {
		text  : 'End',
		action: tour.cancel
	}]
});

tour.addStep('Step2', {
	title: 'Title 2',
	text: 'Text 2 ...',
	attachTo: {element: '#sidebar', on: 'right'},
	buttons: [{
		text  : 'Next',
		action: tour.next
	}, {
		text  : 'End',
		action: tour.cancel
	}]
});

tour.addStep('Step3', {
	title: 'Title 3',
	text: 'Text 3 ...',
	attachTo: {element: '#config-document', on: 'bottom'},
	buttons: [{
		text  : 'Next',
		action: tour.next
	}, {
		text  : 'End',
		action: tour.cancel
	}]
});
