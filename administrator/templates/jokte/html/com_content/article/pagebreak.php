<?php
/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2010 Webnific. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

        <script type="text/javascript">
            function insertPagebreak()
            {
                // Get the pagebreak title
                var title = document.getElementById("title").value;
                if (title != '') {
                    title = "title=\""+title+"\" ";
                }

                // Get the pagebreak toc alias -- not inserting for now
                // don't know which attribute to use...
                var alt = document.getElementById("alt").value;
                if (alt != '') {
                    alt = "alt=\""+alt+"\" ";
                }

                var tag = "<hr class=\"system-pagebreak\" "+title+" "+alt+"/>";

                window.parent.jInsertEditorText(tag, '<?php echo $this->eName; ?>');
                window.parent.SqueezeBox.close();
                return false;
            }
        </script>

        <form>
        <table width="100%" align="center">
            <tr width="40%">
                <td class="key" align="right">
                    <label for="title">
                        <?php echo JText::_( 'COM_CONTENT_PAGEBREAK_TITLE' ); ?>
                    </label>
                </td>
                <td>
                    <input type="text" id="title" name="title" />
                </td>
            </tr>
            <tr width="60%">
                <td class="key" align="right">
                    <label for="alias">
                        <?php echo JText::_( 'COM_CONTENT_PAGEBREAK_TOC' ); ?>
                    </label>
                </td>
                <td>
                    <input type="text" id="alt" name="alt" />
                </td>
            </tr>
        </table>
        </form>
        <button onclick="insertPagebreak();"><?php echo JText::_( 'COM_CONTENT_PAGEBREAK_INSERT_BUTTON' ); ?></button>
