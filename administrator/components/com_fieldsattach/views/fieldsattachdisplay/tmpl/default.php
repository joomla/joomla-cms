<?php
/**
 * @version		$Id: default.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
?>
<table class="adminform">
	<tr>
		<td width="100%" valign="top">
			<div id="cpanel">
			   <h2>WITH CONTENT PLUGIN</h2>
			    <p>It Is automatic and more simple. The extra fields will  displayed after the content article.</p>
	                    <h2>PROGRAMMING FORM</h2>
                            <h3>IMPORTANT!!!</h33>
                            After all, write this line in php view of component.<br /><br />

                            <code>// require helper file<br />
                            JLoader::register('fieldattach',  'components/com_fieldsattach/helpers/fieldattach.php');
                            </code>
                            <br /><br />
                              <h2>FUNCTIONS</h2>

                               
                              <h3>getName::getName($id, $fieldsids)</h3>
                              <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>Value of name of field</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>fieldattach::getValue($id, $fieldsids)</h3>
                              <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>Value of field</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getInput($id, $fieldsids )</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>input HTML tag   <br /></td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getImg($id, $fieldsids, $title=null)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)<br />
                                      $title (alt of image)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>html of image   <br /><code>&lt;img src="pp.png" alt=" " &#34;&gt;</code></td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getSelect($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>html of   select</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getFileDownload($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$articleid(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>html of   select</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getSelectmultiple($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>html of multiple select</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getImageGallery($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>html gallery list</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getImageGallerynyroModal($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$articleid(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>html of gallery, for nyroModal gallery.</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getVimeoVideo($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$articleid(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>video IFRAME</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getYoutubeVideo($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$articleid(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>object video</td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getYoutubeVideonyroModal($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)<br />
                                      $title (alt of image)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>html of youtube  <br /><code>&lt;div class="vervideogallery" /&gt;<br />
                                          &lt;a href="http://www.youtube.com/watch?v='.$result->value.'" class="nyroModal"  &gt;Video&lt;/a&gt;
                                          <br />&lt;div/&gt;
                                          </code>
                                      </td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getVimeoVideo($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id) </td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>Vimeo Iframe
                                        <?php
                                         $orig =' ';
                                         echo  '<code>'.htmlentities($orig).'</code>' ;
                                         ?>
                                      </td>
                                  </tr>
                              </table>
                              <hr />

                              <h3>getYoutubeVideo($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id) </td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>Object Youtube  <br />
                                         <?php
                                         $orig ='';
                                         echo  '<code>'.htmlentities($orig).'</code>' ;
                                         ?>
                                      </td>
                                  </tr>
                              </table>
                              <hr />
                              <h3>getListUnits($articleid, $fieldsids)</h3>
                               <table>
                                  <tr>
                                      <td valign="top">Parameters</td>
                                      <td>$id(Article id)<br />
                                      $fieldsids(field id)</td>
                                  </tr>
                                  <tr>
                                      <td valign="top">Return</td>
                                      <td>html of table list of units<br />
                                         <?php
                                         $orig ='<table>
	<thead>
		<tr>
			<th>Label 1</th>
			<th>Label 2</th>
			<th>Label 3</th>
		</tr>
	</thead>
	<tr>
		<td>Value1</td>
		<td>Value2</td>
		<td>Value3</td>
	</tr>
	<tr>
		<td>Value3</td>
		<td>Value4</td>
		<td>Value5</td>
	</tr>
</table> ';
                                         echo  '<code>'.htmlentities($orig).'</code>' ;
                                         ?>

                                      </td>
                                  </tr>
                              </table>
                              <hr />
                              
			</div>
 
		</td>

		 
	</tr>
</table>
