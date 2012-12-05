window.addEvent('domready', function () {
	em = document.id('extraction_method');
	if (em) {
		em.addEvent('change', function () {
			if(em.value == 'direct') {
				document.id('row_ftp_hostname').style.display = 'none';
				document.id('row_ftp_port').style.display = 'none';
				document.id('row_ftp_username').style.display = 'none';
				document.id('row_ftp_password').style.display = 'none';
				document.id('row_ftp_directory').style.display = 'none';
			} else {
				document.id('row_ftp_hostname').style.display = 'table-row';
				document.id('row_ftp_port').style.display = 'table-row';
				document.id('row_ftp_username').style.display = 'table-row';
				document.id('row_ftp_password').style.display = 'table-row';
				document.id('row_ftp_directory').style.display = 'table-row';
			}
		});
	}
	
	$$('button.submit').addEvent('click', function() {
		$$('div.download_message').setStyle('display', 'block');
		var el = $$('div.joomlaupdate_spinner');
		el.set('spinner', {class: 'joomlaupdate_spinner'});
		el.spin();
	})
	
	$$('#enable_update').addEvent('click', function(el) {
		if (this.checked){
			$$('button.submit').set('disabled','');
			$$('button.submit').setStyle('opacity',1);
			$$('#dontrecom').setStyle('display','block');
		}else{
			$$('button.submit').set('disabled','disabled');
			$$('button.submit').setStyle('opacity',0.5);
			$$('#dontrecom').setStyle('display','none');
		}
	});	
});
