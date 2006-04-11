

function init_moofx()
{
	var sliders  = document.getElementsByClassName('moofx-slider'); 	//div that stretches
	var togglers = document.getElementsByClassName('moofx-toggler'); 	//h3s where I click on

	var slide = new fx.Accordion(togglers, sliders, {opacity: true, duration: 400});
}