// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: fs_permissions.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

function filePermsSave() {
	var f = document.adminForm;
	if (f.filePermsMode0.checked) {
		f.config_fileperms.value = '';
	} else {
		var perms = 0;
		if (f.filePermsUserRead.checked) perms += 400;
		if (f.filePermsUserWrite.checked) perms += 200;
		if (f.filePermsUserExecute.checked) perms += 100;
		if (f.filePermsGroupRead.checked) perms += 40;
		if (f.filePermsGroupWrite.checked) perms += 20;
		if (f.filePermsGroupExecute.checked) perms += 10;
		if (f.filePermsWorldRead.checked) perms += 4;
		if (f.filePermsWorldWrite.checked) perms += 2;
		if (f.filePermsWorldExecute.checked) perms += 1;
		f.config_fileperms.value = '0'+''+perms;
	}
}

function filePermsModeChange(mode) {
	if(document.getElementById) {
		switch (mode) {
			case 0:
				document.getElementById('filePermsValue').style.display = 'none';
				document.getElementById('filePermsTooltip').style.display = '';
				document.getElementById('filePermsFlags').style.display = 'none';
				break;

			default:
				document.getElementById('filePermsValue').style.display = '';
				document.getElementById('filePermsTooltip').style.display = 'none';
				document.getElementById('filePermsFlags').style.display = '';
				break;
		}
	}

	filePermsSave();
}

function dirPermsSave() {
	var f = document.adminForm;
	if (f.dirPermsMode0.checked) {
		f.config_dirperms.value = '';
	} else {
		var perms = 0;
		if (f.dirPermsUserRead.checked) perms += 400;
		if (f.dirPermsUserWrite.checked) perms += 200;
		if (f.dirPermsUserExecute.checked) perms += 100;
		if (f.dirPermsGroupRead.checked) perms += 40;
		if (f.dirPermsGroupWrite.checked) perms += 20;
		if (f.dirPermsGroupExecute.checked) perms += 10;
		if (f.dirPermsWorldRead.checked) perms += 4;
		if (f.dirPermsWorldWrite.checked) perms += 2;
		if (f.dirPermsWorldExecute.checked) perms += 1;
		f.config_dirperms.value = '0'+''+perms;
	}
}

function dirPermsModeChange(mode) {
	if(document.getElementById) {
		switch (mode) {
			case 0:
				document.getElementById('dirPermsValue').style.display = 'none';
				document.getElementById('dirPermsTooltip').style.display = '';
				document.getElementById('dirPermsFlags').style.display = 'none';
				break;
			default:
				document.getElementById('dirPermsValue').style.display = '';
				document.getElementById('dirPermsTooltip').style.display = 'none';
				document.getElementById('dirPermsFlags').style.display = '';
				break;
		}
	}

	dirPermsSave();
}
