<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Evaluates to true iif all predicates given as constructor parameters evaluate
 * to true
 *
 * PHP versions 4 and 5
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330,Boston,MA 02111-1307 USA
 *
 * @category   File Formats
 * @package    File_Archive
 * @author     Vincent Lascaux <vincentlascaux@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL
 * @version    CVS: $Id: And.php,v 1.8 2005/04/21 10:01:46 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Predicate.php";
jimport('pear.File.Archive.Predicate');

/**
 * Evaluates to true iif all predicates given as constructor parameters evaluate
 * to true
 *
 * Example:
 *  new File_Archive_Predicate_And($pred1, $pred2, $pred3)
 *
 * @see File_Archive_Predicate, File_Archive_Reader_Filter
 */
class File_Archive_Predicate_And extends File_Archive_Predicate
{
    /**
     * @var Array List of File_Archive_Predicate objects given as an argument
     * @access private
     */
    var $preds;

    /**
     * Build the predicate using the optional File_Archive_Predicates given as
     * arguments
     *
     * Example:
     *   new File_Archive_Predicate_And($pred1, $pred2, $pred3)
     */
    function File_Archive_Predicate_And()
    {
        $this->preds = func_get_args();
    }

    /**
     * Add a new predicate to the list
     *
     * @param File_Archive_Predicate The predicate to add
     */
    function addPredicate($pred)
    {
        $this->preds[] = $pred;
    }
    /**
     * @see File_Archive_Predicate::isTrue()
     */
    function isTrue(&$source)
    {
        foreach ($this->preds as $p) {
            if (!$p->isTrue($source)) {
                return false;
            }
        }
        return true;
    }
}

?>