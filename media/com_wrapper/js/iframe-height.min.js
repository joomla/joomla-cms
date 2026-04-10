/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */((o,n)=>{o.iFrameHeight=t=>{const l="contentDocument"in t?t.contentDocument:t.contentWindow.document,e=parseInt(l.body.scrollHeight,10);n.all?n.all&&t.id&&(n.all[t.id].style.height=`${parseInt(e,10)+20}px`):t.style.height=`${parseInt(e,10)+60}px`}})(window,document);
