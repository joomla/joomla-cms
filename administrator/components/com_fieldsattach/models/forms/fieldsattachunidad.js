window.addEvent('domready', function() {
	document.formvalidator.setHandler('greeting',
		function (value) {
			regex=/^[^0-9]+$/;
			return regex.test(value);
	});

        input.addEvents({
		'change':function(){
			alert("ssss");

                    }
        });
});


