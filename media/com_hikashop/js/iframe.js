/**
 * @package    HikaShop for Joomla!
 * @version    2.6.0
 * @author     hikashop.com
 * @copyright  (C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
function HikashopGetDocHeight(doc){
		var docHt = 0, sh, oh;
		if (doc.height) docHt = doc.height;else if (doc.body){
			if (doc.body.scrollHeight) docHt = sh = doc.body.scrollHeight;
			if (doc.body.offsetHeight) docHt = oh = doc.body.offsetHeight;if (sh && oh) docHt = Math.max(sh, oh);
		}
		return docHt;
	}


function HikashopSetIframeHeight(iframeName){
		var iframeWin = window.frames[iframeName];
		var iframeEl = document.getElementById? document.getElementById(iframeName): document.all? document.all[iframeName]: null;
		if ( iframeEl && iframeWin ){
			iframeEl.style.height = "auto";
			var docHt = this.HikashopGetDocHeight(iframeWin.document);
			iframeEl.style.height = docHt + 30 + "px";
		}
	}
