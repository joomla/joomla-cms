function init_moofx(){

	var stretchers = document.getElementsByClassName('section-smenu'); //div that stretches
	var toggles = document.getElementsByClassName('title-smenu'); //h3s where I click on

	//accordion effect
	var smenuAccordion = new fx.Accordion(
	toggles, stretchers, {opacity: true, duration: 400}
	);

	//hash functions
	var found = false;
	toggles.each(function(h3, i){
		var div = Element.find(h3, 'nextSibling'); //element.find is located in prototype.lite
		if (window.location.href.indexOf(h3.title) > 0) {
			smenuAccordion.showThisHideOpen(div);
			found = true;
		}
	});
	if (!found) smenuAccordion.showThisHideOpen(stretchers[0]);
}
