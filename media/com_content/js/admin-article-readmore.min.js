import{JoomlaEditor as t,JoomlaEditorButton as i}from"editor-api";import a from"joomla.dialog";/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */const r=e=>{const o=e.getValue();o?o&&!o.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)?e.replaceSelection('<hr id="system-readmore">'):a.alert(Joomla.Text._("PLG_READMORE_ALREADY_EXISTS")):e.replaceSelection('<hr id="system-readmore">')};window.insertReadmore=()=>{console.warn('Method window.insertReadmore() is deprecated, use button action "insert-readmore."');const e=t.getActive();if(!e)throw new Error("An active editor are not available");r(e)},i.registerAction("insert-readmore",r);
