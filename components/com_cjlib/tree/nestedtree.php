<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 *  Originally developed by Stefan Gabos, modified by Maverick for corejoomla.com
 * 
 *  NestedTree is a PHP class that provides an implementation of the modified preorder tree traversal algorithm making
 *  it easy for you to use MPTT in your PHP applications.
 *
 *  It provides methods for adding nodes anywhere in the tree, deleting nodes, moving and copying nodes around the tree
 *  and methods for retrieving various information about the nodes.
 *
 *  NestedTree uses {@link http://dev.mysql.com/doc/refman/5.0/en/ansi-diff-transactions.html MySQL transactions} making
 *  sure that database integrity is always preserved and that SQL operations execute completely or not at all (in the case
 *  there's a problem with the MySQL server). Also, the library uses a caching mechanism ensuring that the database is
 *  accessed in an optimum way.
 *
 *  The code is heavily commented and generates no warnings/errors/notices when PHP's error reporting level is set to
 *  E_ALL.
 *
 *  Visit {@link http://stefangabos.ro/php-libraries/zebra-mptt/} for more information.
 *
 *  For more resources visit {@link http://stefangabos.ro/}
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @version    2.2 (last revision: January 20, 2012)
 *  @copyright  (c) 2009 - 2012 Stefan Gabos
 *  @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 *  @package    NestedTree
 */

class CjNestedTree
{

	private static $lookup;
	
	private $_reload = false;
	
	private $_error;
	
    /**
     *  Constructor of the class.
     *
     *  <i>Make sure that before you instantiate the class you import or execute the SQL code found in the in the
     *  "install/mptt.sql" file, using the command line or your preferred MySQL manager.</i>
     *
     *  <code>
     *  // include the php file
     *  require 'path/to/NestedTree.php';
     *
     *  // instantiate the class
     *  $mptt = new NestedTree();
     *  </code>
     *
     *  @param  string      $table_name     (Optional) MySQL table name to be used for storing items.
     *
     *                                      Default is <i>mptt</i>
     *
     *  @param  string      $id_column      (Optional) Name of the column that uniquely identifies items in the table
     *
     *                                      Default is <i>id</i>
     *
     *  @param  string      $title_column   (Optional) Name of the column that stores items' names
     *
     *                                      Default is <i>title</i>
     *
     *  @param  string      $left_column    (Optional) Name of the column that stores "left" values
     *
     *                                      Default is <i>lft</i> ("left" is a reserved word in MySQL)
     *
     *  @param  string      $right_column   (Optional) Name of the column that stores "right" values
     *
     *                                      Default is <i>rgt</i> ("right" is a reserved word in MySQL)
     *
     *  @param  string      $parent_column  (Optional) Name of the column that stores the IDs of parent items
     *
     *                                      Default is <i>parent</i>
     *
     *  @return void
     */
    function CjNestedTree($db, $table_name, $extra_columns=array(), $id_column = 'id', $title_column = 'title', 
    	$left_column = 'nleft', $right_column = 'nright', $parent_column = 'parent_id', $order_column = 'norder') {
    	
    	$this->properties = array(
    		'database'		=>	$db,
    		'table_name'    =>  $table_name,
    		'id_column'     =>  $id_column,
    		'title_column'  =>  $title_column,
    		'left_column'   =>  $left_column,
    		'right_column'  =>  $right_column,
    		'parent_column' =>  $parent_column,
    		'extra_fields'	=>	$extra_columns,
    		'order_column'	=>	$order_column
    	);
    }
    
    function reload_tree(){
    	
    	$this->_reload = true;
    }

    /**
     *  Adds a new node as the child of a given parent node.
     *
     *  <code>
     *  // add a new topmost node
     *  $node = $mptt->add(0, 'Main');
     *
     *  // add a child node
     *  $mptt->add($node, 'Child 1');
     *
     *  // add another child node
     *  $mptt->add($node, 'Child 2');
     *
     *  // insert a third child node
     *  // notice the "1" as the last argument, instructing the script to insert the child node
     *  // as the second child node, after "Child 1"
     *  // remember that the trees are 0-based, meaning that the first node in a tree has the index 0!
     *  $mptt->add($node, 'Child 3', 1);
     *
     *  // and finally, insert a fourth child node
     *  // notice the "0" as the last argument, instructing the script to insert the child node
     *  // as the very first child node of the parent node
     *  // remember that the trees are 0-based, meaning that the first node in a tree has the index 0!
     *  $mptt->add($node, 'Child 4', 0);
     *  </code>
     *
     *  @param  integer     $parent     The ID of the parent node.
     *
     *                                  Use "0" to add a topmost node.
     *
     *  @param  string      $title      The title of the node.
     *
     *  @param  integer     $position   (Optional) The position the node will have amongst the {@link $parent}'s
     *                                  children nodes.
     *
     *                                  When {@link $parent} is "0", this refers to the position the node will have
     *                                  amongst the topmost nodes.
     *
     *                                  The values are 0-based, meaning that if you want the node to be inserted as
     *                                  the first in the list of {@link $parent}'s children nodes, you have to use "0".<br>
     *                                  If you want it to be second, use "1" and so on.
     *
     *                                  Default is "0" - the node will be inserted as last of the {@link $parent}'s
     *                                  children nodes.
     *
     *  @return mixed                   Returns the ID of the newly inserted node or FALSE upon error.
     */
    function add($parent, $title, $position = false, $fields=null) {
    
        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // make sure parent ID is an integer
        $parent = (int)$parent;

        // continue only if
        if (

            // we are adding a topmost node OR
            $parent == 0 ||

            // parent node exists in the lookup array
            isset(self::$lookup[$parent])

        ) {

            // get parent's children nodes (no deeper than the first level)
            $children = $this->get_children($parent, true);

            // if node is to be inserted in the default position (as the last of the parent node's children)
            if ($position === false)

                // give a numerical value to the position
                $position = count($children);

            // if a custom position was specified
            else {

                // make sure that position is an integer value
                $position = (int)$position;

                // if position is a bogus number
                if ($position > count($children) || $position < 0)

                    // use the default position (as the last of the parent node's children)
                    $position = count($children);

            }

            // if parent has no children OR the node is to be inserted as the parent node's first child
            if (empty($children) || $position == 0)

                // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
                // the insert, and will need to be updated
                // if parent is not found (meaning that we're inserting a topmost node) set the boundary to 0
                $boundary = isset(self::$lookup[$parent]) ? self::$lookup[$parent][$this->properties['left_column']] : 0;

            // if parent node has children nodes and/or the node needs to be inserted at a specific position
            else {

                // find the child node that currently exists at the position where the new node needs to be inserted to
                // since PHP 5.3 this needs to be done in two steps rather than
                // $children = array_shift(array_slice($children, $position - 1, 1));
                // or PHP will trigger a warning "Strict standards: Only variables should be passed by reference"
                $slice = array_slice($children, $position - 1, 1);
                $children = array_shift($slice);

                // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
                // the insert, and will need to be updated
                $boundary = $children[$this->properties['right_column']];

            }

            // iterate through all the records in the lookup array
            foreach (self::$lookup as $id => $properties) {

                // if the node's "left" value is outside the boundary
                if ($properties[$this->properties['left_column']] > $boundary)

                    // increment it with 2
                    self::$lookup[$id][$this->properties['left_column']] += 2;

                // if the node's "right" value is outside the boundary
                if ($properties[$this->properties['right_column']] > $boundary)

                    // increment it with 2
                    self::$lookup[$id][$this->properties['right_column']] += 2;

            }

            // lock table to prevent other sessions from modifying the data and thus preserving data integrity
            $query = 'LOCK TABLE ' . $this->properties['table_name'] . ' WRITE';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();

            // update the nodes in the database having their "left"/"right" values outside the boundary
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['left_column'] . ' = ' . $this->properties['left_column'] . ' + 2
                WHERE
                    ' . $this->properties['left_column'] . ' > ' . $boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            $query = '

                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['right_column'] . ' = ' . $this->properties['right_column'] . ' + 2
                WHERE
                    ' . $this->properties['right_column'] . ' > ' . $boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // insert the new node into the database
            
            if(count($fields) > 0){
            	
            	foreach($fields as $i=>$field){
            		
            		$fields[$i] = $field === null ? 'null' : $this->properties['database']->quote($field);
            	}
            }
            
            $query = '
                INSERT INTO
                    ' . $this->properties['table_name'] . '
                    (
                        ' . $this->properties['title_column'] . ',
                        ' . $this->properties['left_column'] . ',
                        ' . $this->properties['right_column'] . ',
                        ' . $this->properties['parent_column'] . 
                        (count($this->properties['extra_fields']) > 0 ? ',' . implode(',', $this->properties['extra_fields']) : '' ) . '
                    )
                VALUES
                    (
                        ' . $this->properties['database']->quote($title) . ',
                        ' . ($boundary + 1) . ',
                        ' . ($boundary + 2) . ',
                        ' . $parent . 
                        (count($this->properties['extra_fields']) > 0 ? ',' . implode(',', $fields) : '' ) . '
                    )
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // get the ID of the newly inserted node
            $node_id = $this->properties['database']->insertid();

            // release table lock
            $query = 'UNLOCK TABLES';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // add the node to the lookup array
            self::$lookup[$node_id] = array(
                $this->properties['id_column']      => $node_id,
                $this->properties['title_column']   => $title,
                $this->properties['left_column']    => $boundary + 1,
                $this->properties['right_column']   => $boundary + 2,
                $this->properties['parent_column']  => $parent,
            );

            // reorder the lookup array
            $this->_reorder_lookup_array();

            // return the ID of the newly inserted node
            return $node_id;

        }
        
        // if script gets this far, something must've went wrong so we return false
        $this->_error = $this->properties['database']->getErrorMsg();
        
        return false;

    }

    /**
     *  Creates a copy of a node (including its children nodes) as the children node of a given parent node.
     *
     *  <code>
     *  // insert a topmost node
     *  $node = $mptt->add(0, 'Main');
     *
     *  // add a child node
     *  $child1 = $mptt->add($node, 'Child 1');
     *
     *  // add another child node
     *  $child2 = $mptt->add($node, 'Child 2');
     *
     *  // create a copy of "Child 2" node and put it as "Child 1"'s child
     *  $mptt->copy($child2, $child1);
     *  </code>
     *
     *  @param  integer     $source     The ID of the node we want to copy.
     *
     *                                  Remember that the node will be copied with all its children nodes!
     *
     *  @param  integer     $target     The ID of the node which will become the copy's parent node.
     *
     *                                  Use "0" to create a copy as a topmost node.
     *
     *  @param  integer     $position   (Optional) The position the node will have amongst the {@link $target}'s
     *                                  children nodes.
     *
     *                                  When {@link $target} is "0", this refers to the position the node will have
     *                                  amongst the topmost nodes.
     *
     *                                  The values are 0-based, meaning that if you want the node to be inserted as
     *                                  the first in the list of {@link $parent}'s children nodes, you have to use "0".<br>
     *                                  If you want it to be second, use "1" and so on.
     *
     *                                  Default is "0" - the node will be inserted as last of the {@link $parent}'s
     *                                  children nodes.
     *
     *  @return mixed                   Returns the ID of the newly created copy or FALSE upon error.
     */
    function copy($source, $target, $position = false) {
    
        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // continue only if
        if (

            // source node exists in the lookup array AND
            isset(self::$lookup[$source]) &&

            // target node exists in the lookup array OR is 0 (indicating a topmost node)
            (isset(self::$lookup[$target]) || $target == 0)

        ) {

            // get the source's children nodes (if any)
            $source_children = $this->get_children($source);

            // this array will hold the items we need to copy
            // by default we add the source item to it
            $sources = array(self::$lookup[$source]);

            // the copy's parent will be the target node
            $sources[0][$this->properties['parent_column']] = $target;

            // iterate through source node's children
            foreach ($source_children as $child)

                // save them for later use
                $sources[] = self::$lookup[$child[$this->properties['id_column']]];

            // the value with which items outside the boundary set below, are to be updated with
            $source_rl_difference =

                self::$lookup[$source][$this->properties['right_column']] -

                self::$lookup[$source][$this->properties['left_column']]

                + 1;

            // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
            // the insert, and will need to be updated
            $source_boundary = self::$lookup[$source][$this->properties['left_column']];

            // get target node's children (no deeper than the first level)
            $target_children = $this->get_children($target, true);

            // if copy is to be inserted in the default position (as the last of the target node's children)
            if ($position === false)

                // give a numerical value to the position
                $position = count($target_children);

            // if a custom position was specified
            else {

                // make sure given position is an integer value
                $position = (int)$position;

                // if position is a bogus number
                if ($position > count($target_children) || $position < 0)

                    // use the default position (the last of the target node's children)
                    $position = count($target_children);

            }

            // we are about to do an insert and some nodes need to be updated first

            // if target has no children nodes OR the copy is to be inserted as the target node's first child node
            if (empty($target_children) || $position == 0)

                // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
                // the insert, and will need to be updated
                // if parent is not found (meaning that we're inserting a topmost node) set the boundary to 0
                $target_boundary = isset(self::$lookup[$target]) ? self::$lookup[$target][$this->properties['left_column']] : 0;

            // if target has children nodes and/or the copy needs to be inserted at a specific position
            else {

                // find the target's child node that currently exists at the position where the new node needs to be inserted to
                // since PHP 5.3 this needs to be done in two steps rather than
                // $target_children = array_shift(array_slice($target_children, $position - 1, 1));
                // or PHP will trigger a warning "Strict standards: Only variables should be passed by reference"
                $slice = array_slice($target_children, $position - 1, 1);
                $target_children = array_shift($slice);

                // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
                // the insert, and will need to be updated
                $target_boundary = $target_children[$this->properties['right_column']];

            }

            // iterate through the nodes in the lookup array
            foreach (self::$lookup as $id => $properties) {

                // if the "left" value of node is outside the boundary
                if ($properties[$this->properties['left_column']] > $target_boundary)

                    // increment it
                    self::$lookup[$id][$this->properties['left_column']] += $source_rl_difference;

                // if the "right" value of node is outside the boundary
                if ($properties[$this->properties['right_column']] > $target_boundary)

                    // increment it
                    self::$lookup[$id][$this->properties['right_column']] += $source_rl_difference;

            }

            // lock table to prevent other sessions from modifying the data and thus preserving data integrity
            $query = 'LOCK TABLE ' . $this->properties['table_name'] . ' WRITE';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // update the nodes in the database having their "left"/"right" values outside the boundary
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['left_column'] . ' = ' . $this->properties['left_column'] . ' + ' . $source_rl_difference . '
                WHERE
                    ' . $this->properties['left_column'] . ' > ' . $target_boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['right_column'] . ' = ' . $this->properties['right_column'] . ' + ' . $source_rl_difference . '
                WHERE
                    ' . $this->properties['right_column'] . ' > ' . $target_boundary . '

            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // finally, the nodes that are to be inserted need to have their "left" and "right" values updated
            $shift = $target_boundary - $source_boundary + 1;

            // iterate through the nodes that are to be inserted
            foreach ($sources as $id => $properties) {

                // update "left" value
                $properties[$this->properties['left_column']] += $shift;

                // update "right" value
                $properties[$this->properties['right_column']] += $shift;

                // insert into the database
                $query = '
                    INSERT INTO
                        ' . $this->properties['table_name'] . '
                        (
                            ' . $this->properties['title_column'] . ',
                            ' . $this->properties['left_column'] . ',
                            ' . $this->properties['right_column'] . ',
                            ' . $this->properties['parent_column'] . '
                        )
                    VALUES
                        (
                            ' . $this->properties['database']->quote($properties[$this->properties['title_column']]) . ',
                            ' . $properties[$this->properties['left_column']] . ',
                            ' . $properties[$this->properties['right_column']] . ',
                            ' . $properties[$this->properties['parent_column']] . '
                        )
                ';
                $this->properties['database']->setQuery($query);
                $this->properties['database']->query();
                
                // get the ID of the newly inserted node
                $node_id = $this->properties['database']->insertid();

                // update the node's properties with the ID
                $properties[$this->properties['id_column']] = $node_id;

                // update the array of inserted items
                $sources[$id] = $properties;

            }

            // release table lock
            $query = 'UNLOCK TABLES';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // at this point, we have the nodes in the database but we need to also update the lookup array

            $parents = array();

            // iterate through the inserted nodes
            foreach ($sources as $id => $properties) {

                // if the node has any parents
                if (count($parents) > 0)

                    // iterate through the array of parent nodes
                    while ($parents[count($parents) - 1]['right'] < $properties[$this->properties['right_column']])

                        // and remove those which are not parents of the current node
                        array_pop($parents);

                // if there are any parents left
                if (count($parents) > 0)

                    // the last node in the $parents array is the current node's parent
                    $properties[$this->properties['parent_column']] = $parents[count($parents) - 1]['id'];

                // update the lookup array
                self::$lookup[$properties[$this->properties['id_column']]] = $properties;

                // add current node to the stack
                $parents[] = array(

                    'id'    =>  $properties[$this->properties['id_column']],
                    'right' =>  $properties[$this->properties['right_column']]

                );

            }
            
            // reorder the lookup array
            $this->_reorder_lookup_array();

            // return the ID of the copy
            return $sources[0][$this->properties['id_column']];

        }

        // if scripts gets this far, return false as something must've went wrong
        return false;

    }

    /**
     *  Deletes a node, including the node's children nodes.
     *
     *  <code>
     *  // add a topmost node
     *  $node = $mptt->add(0, 'Main');
     *
     *  // add child node
     *  $child1 = $mptt->add($node, 'Child 1');
     *
     *  // add another child node
     *  $child2 = $mptt->add($node, 'Child 2');
     *
     *  // delete the "Child 2" node
     *  $mptt->delete($child2);
     *  </code>
     *
     *  @param  integer     $node       The ID of the node to be deleted.
     *
     *  @return boolean                 TRUE on success or FALSE upon error.
     */
    function delete($node) {

        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // continue only if target node exists in the lookup array
        if (isset(self::$lookup[$node])) {

            // get target node's children nodes (if any)
            $children = $this->get_children($node);

            // iterate through target node's children nodes
            foreach ($children as $child)

                // remove node from the lookup array
                unset(self::$lookup[$child[$this->properties['id_column']]]);

            // lock table to prevent other sessions from modifying the data and thus preserving data integrity
            $query = 'LOCK TABLE ' . $this->properties['table_name'] . ' WRITE';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // also remove nodes from the database
            $query  = '
                DELETE
                FROM
                    ' . $this->properties['table_name'] . '
                WHERE
                    ' . $this->properties['left_column'] . ' >= ' . self::$lookup[$node][$this->properties['left_column']] . ' AND
                    ' . $this->properties['right_column'] . ' <= ' . self::$lookup[$node][$this->properties['right_column']] . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // the value with which items outside the boundary set below, are to be updated with
            $target_rl_difference =

                self::$lookup[$node][$this->properties['right_column']] -

                self::$lookup[$node][$this->properties['left_column']]

                + 1;

            // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
            // the insert, and will need to be updated
            $boundary = self::$lookup[$node][$this->properties['left_column']];

            // remove the target node from the lookup array
            unset(self::$lookup[$node]);

            // iterate through nodes in the lookup array
            foreach (self::$lookup as $id => $properties) {

                // if the "left" value of node is outside the boundary
                if (self::$lookup[$id][$this->properties['left_column']] > $boundary)

                    // decrement it
                    self::$lookup[$id][$this->properties['left_column']] -= $target_rl_difference;

                // if the "right" value of node is outside the boundary
                if (self::$lookup[$id][$this->properties['right_column']] > $boundary)

                    // decrement it
                    self::$lookup[$id][$this->properties['right_column']] -= $target_rl_difference;

            }

            // update the nodes in the database having their "left"/"right" values outside the boundary
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['left_column'] . ' = ' . $this->properties['left_column'] . ' - ' . $target_rl_difference . '
                WHERE
                    ' . $this->properties['left_column'] . ' > ' . $boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['right_column'] . ' = ' . $this->properties['right_column'] . ' - ' . $target_rl_difference . '
                WHERE
                    ' . $this->properties['right_column'] . ' > ' . $boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // release table lock
            $query = 'UNLOCK TABLES';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // return true as everything went well
            return true;

        }

        // if script gets this far, something must've went wrong so we return false
        return false;

    }

    /**
     *  Returns an unidimensional (flat) array with the children nodes of a given parent node.
     *
     *  <i>For a multi-dimensional array use the {@link get_tree()} method.</i>
     *
     *  @param  integer     $parent             (Optional) The ID of the node for which to return children nodes.
     *
     *                                          Default is "0" - return all the nodes.
     *
     *  @param  boolean     $children_only      (Optional) Set this to TRUE to return only the node's direct children nodes
     *                                          (and no children nodes of children nodes of children nodes...)
     *
     *                                          Default is FALSE
     *
     *  @return array                           Returns an unidimensional array with the children nodes of the given
     *                                          parent node.
     */
    function get_children($parent = 0, $children_only = false, $language = '*') {

        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // if parent node exists in the lookup array OR we're looking for the topmost nodes
        if (isset(self::$lookup[$parent]) || $parent === 0) {

            $children = array();

            // get the keys in the lookup array
            $keys = array_keys(self::$lookup);
            
            if($parent === 0){
            	
            	foreach ($keys as $item){
            		
            		if(self::$lookup[$item][$this->properties['parent_column']] == 0){
            			
            			$parent = $item;
            			break;
            		}
            	}
            }

            // iterate through the available keys
            foreach ($keys as $item)

                // if
                if (

                    // node's "left" is higher than parent node's "left" (or, if parent is 0, if it is higher than 0)
                    self::$lookup[$item][$this->properties['left_column']] > ($parent !== 0 ? self::$lookup[$parent][$this->properties['left_column']] : 0) &&

                    // node's "left" is smaller than parent node's "right" (or, if parent is 0, if it is smaller than PHP's maximum integer value)
                    self::$lookup[$item][$this->properties['left_column']] < ($parent !== 0 ? self::$lookup[$parent][$this->properties['right_column']] : PHP_INT_MAX) &&

                    // if we only need the first level children, check if children node's parent node is the parent given as argument
                    (!$children_only || ($children_only && self::$lookup[$item][$this->properties['parent_column']] == $parent)) && 
                		
                    // check language 
            		($language == '*' || !isset(self::$lookup[$item]['language']) || self::$lookup[$item]['language'] == '*' || self::$lookup[$item]['language'] == $language)
                )

                    // save to array
                    $children[self::$lookup[$item][$this->properties['id_column']]] = self::$lookup[$item];

            // return children nodes
            return $children;

        }

        // if script gets this far, return false as something must've went wrong
        return false;

    }

    /**
     *  Returns the number of direct children nodes that a given node has (excluding children nodes of children nodes of
     *  children nodes and so on)
     *
     *  @param  integer     $node               The ID of the node for which to return the number of direct children nodes.
     *
     *  @return integer                         Returns the number of direct children nodes that a given node has, or
     *                                          FALSE on error.
     *
     *                                          <i>Since this method may return both "0" and FALSE, make sure you use ===
     *                                          to verify the returned result!</i>
     */
    function get_children_count($node) {

        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // if node exists in the lookup array
        if (isset(self::$lookup[$node])) {

            $result = 0;

            // iterate through all the records in the lookup array
            foreach (self::$lookup as $id => $properties)

                // if node is a direct children of the parent node
                if (self::$lookup[$id][$this->properties['parent_column']] == $node)

                    // increment the number of direct children
                    $result++;

            // return the number of direct children nodes
            return $result;

        }

        // if script gets this far, return false as something must've went wrong
        return false;

    }

    /**
     *  Returns the number of total children nodes that a given node has, including children nodes of children nodes of
     *  children nodes and so on.
     *
     *  @param  integer     $node               The ID of the node for which to return the total number of descendant nodes.
     *
     *  @return integer                         Returns the number of total children nodes that a given node has, or
     *                                          FALSE on error.
     *
     *                                          <i>Since this method may return both "0" and FALSE, make sure you use ===
     *                                          to verify the returned result!</i>
     */
    function get_descendants_count($node) {

        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // if parent node exists in the lookup array
        if (isset(self::$lookup[$node]))

            // return the total number of descendant nodes
            return (self::$lookup[$node][$this->properties['right_column']] - self::$lookup[$node][$this->properties['left_column']] - 1) / 2;

        // if script gets this far, return false as something must've went wrong
        return false;

    }

    /**
     *  Returns information about the node's direct parent node.
     *
     *  If node given as argument has a direct parent node, return an array containing the parent node's properties. If
     *  node given as argument is a topmost node, return 0.
     *
     *  @param  integer     $node               The ID of a node for which to return the direct parent node's properties.
     *
     *  @return mixed                           If node given as argument has a direct parent node, returns an array
     *                                          containing the parent node's properties. If node given as argument is a
     *                                          topmost node, returns 0.
     *
     *                                          <i>Since this method may return both "0" and FALSE, make sure you use ===
     *                                          to verify the returned result!</>
     */
    function get_parent($node) {

        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // if node exists in the lookup array
        if (isset(self::$lookup[$node]))

            // if node has a parent node, return the parent node's properties
            // also, return 0 if the node is a topmost node
            return isset(self::$lookup[$node][$this->properties['parent_column']]) ? self::$lookup[$node][$this->properties['parent_column']] : 0;

        // if script gets this far, return false as something must've went wrong
        return false;

    }

    /**
     *  Returns an unidimensional (flat) array with the path to the given node (including the node itself).
     *
     *  @param  integer     $node               The ID of a node for which to return the path.
     *
     *  @return array                           Returns an unidimensional array with the path to the given node.
     */
    function get_path($node) {

        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        $parents = array();

        // if node exists in the lookup array
        if (isset(self::$lookup[$node])) {

            // iterate through all the nodes in the lookup array
            foreach (self::$lookup as $id => $properties) {

                // if
                if (

                    // node is a parent node
                    $properties[$this->properties['left_column']] < self::$lookup[$node][$this->properties['left_column']] &&

                    $properties[$this->properties['right_column']] > self::$lookup[$node][$this->properties['right_column']]

                ) {

                    // save the parent node's information
                    $parents[$properties[$this->properties['id_column']]] = $properties;
                }
            }

	        // add also the node given as argument
	        $parents[] = self::$lookup[$node];
        }

        // return the path to the node
        return $parents;

    }
    
    /**
     * @deprecated
     */
    function get_selectables($node = 0, $separator = '--', $include_root=false, $include_stats=false) {
    	
    	return $this->get_indented_nodes($node, $separator, $include_root, array());
    }

    /**
     *  Returns an array of children nodes of a node given as argument, indented and ready to be used in a &lt;select&gt;
     *  control.
     *
     *  @param  integer     $node       (Optional) The ID of a node for which to fetch its children nodes and return
     *                                  the node and its children as an array, indented and ready to be used in a &lt;select&gt;
     *                                  control.
     *
     *                                  Default is "0" - the generated array contains *all* the available nodes.
     *
     *  @param  string      $separator  A string to indent the nodes by.
     *
     *                                  Default is " &rarr; "
     *
     *  @return array                   Returns an array of children nodes of a node given as argument, indented and ready
     *                                  to be used in a <select> control.
     */
    function get_indented_nodes($node = 0, $separator = '--', $include_root = false, $stat_fields = array(), $language = '*') {
    
        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // continue only if
        if (

            // parent node exists in the lookup array OR is 0 (indicating topmost node)
            isset(self::$lookup[$node]) || $node == 0

        ) {
        	
        	// get the keys in the lookup array
        	$keys = array_keys(self::$lookup);
        	
        	if($node === 0){
        		 
        		foreach ($keys as $item){
        	
        			if(self::$lookup[$item][$this->properties['parent_column']] == 0){
        				 
        				$node = $item;
        				break;
        			}
        		}
        	}
        	 
            // the resulting array and a temporary array
            $result = $parents = array();

            // get node's children nodes
            $children = $this->get_children($node, false, $language);
            
            // if node is not 0
            if ($node != 0)

                // prepend the item itself to the list
                array_unshift($children, self::$lookup[$node]);
                
            // iterate through the nodes
            foreach ($children as $id => $child) {

                // if we find a topmost node
                if ($child[$this->properties['parent_column']] == 0) {

                    // if the $categories variable is set, save the categories we have so far
                    if (isset($nodes)) $result += $nodes;

                    // reset the categories and parents arrays
                    $nodes = $parents = array();

                }

                // if the node has any parents
                if (count($parents) > 0){

                	$parent_keys = array_keys($parents);

                    // iterate through the array of parent nodes
                    while (null != $parent_keys && (array_pop($parent_keys) < $child[$this->properties['right_column']])){

                        // and remove parents that are not parents of current node
                        array_pop($parents);
                        $parent_keys = array_keys($parents);
                    }
                }
                
                // add node to the stack of nodes
                $nodes[$child[$this->properties['id_column']]] = (!empty($parents) ? str_repeat($separator, $include_root ? count($parents) : count($parents) - 1) : '') . ' ' . $child[$this->properties['title_column']];

                // stats for polls
                if(!empty($stat_fields)){

                	if(count($stat_fields) == 1){
                	
                		$nodes[$child[$this->properties['id_column']]] .= ' ( '.$child[$stat_fields[0]].' )';
                	} else if(count($stat_fields) == 2){
                		
                		$nodes[$child[$this->properties['id_column']]] .= ' ( '.$child[$stat_fields[0]].' / '.$child[$stat_fields[1]].' )';
                	}
                }
                
                // add node to the stack of parents
                $parents[$child[$this->properties['right_column']]] = $child[$this->properties['title_column']];

            }

            // may not be set when there are no nodes at all
            if (isset($nodes))

                // finalize the result
                $result += $nodes;

            // remove root node.
            if(!$include_root){
            	
	            reset($result);
	            unset($result[key($result)]);
            }
            
            // return the resulting array
            return $result;
            
        }
        
        // if the script gets this far, return false as something must've went wrong
        return false;

    }

    /**
     *  Returns a multi dimensional array with all the descendant nodes (including children nodes of children nodes of
     *  children nodes and so on) of a given node.
     *
     *  @param  integer     $node               (Optional) The ID of the node for which to return all descendant nodes
     *                                          as a multi-dimensional array.
     *
     *                                          Default is "0" - return all the nodes.
     *
     *  @return array                           Returns a multi dimensional array with all the descendant nodes (including
     *                                          children nodes of children nodes of children nodes and so on) of a given
     *                                          node.
     */
    function get_tree($node = 0, $language = '*') {

        // get direct children nodes
        $result = $this->get_children($node, true, $language);

        // iterate through the direct children nodes
        foreach ($result as $id => $properties)

            // for each child node create a "children" property
            // and get the node's children nodes, recursively
            $result[$id]['children'] = $this->get_tree($id, $language);

        // return the array
        return $result;

    }

    /**
     *  Moves a node, including node's children nodes, as the children of a target node.
     *
     *  <code>
     *  // insert a topmost node
     *  $node = $mptt->add(0, 'Main');
     *
     *  // add a child node
     *  $child1 = $mptt->add($node, 'Child 1');
     *
     *  // add another child node
     *  $child2 = $mptt->add($node, 'Child 2');
     *
     *  // move "Child 2" node to be the first of "Main"'s children nodes
     *  $mptt->move($child2, $node, 0);
     *  </code>
     *
     *  @param  integer     $source     The ID of the node that needs to be moved.
     *
     *  @param  integer     $target     The ID of the node where {@link $source} node needs to be moved to. Use "0" if
     *                                  the node does not need a parent node (making it a topmost node).
     *
     *  @param  integer     $position   (Optional) The position the node will have amongst the {@link $parent}'s
     *                                  children nodes.
     *
     *                                  When {@link $parent} is "0", this refers to the position the node will have
     *                                  amongst the topmost nodes.
     *
     *                                  The values are 0-based, meaning that if you want the node to be inserted as
     *                                  the first in the list of {@link $parent}'s children nodes, you have to use "0".<br>
     *                                  If you want it to be second, use "1" and so on.
     *
     *                                  Default is "0" - the node will be inserted as last of the {@link $parent}'s
     *                                  children nodes.
     *
     *  @return boolean                 TRUE on success or FALSE upon error
     */
    function move($source, $target, $position = false) {

        // lazy connection: touch the database only when the data is required for the first time and not at object instantiation
        $this->_init();

        // continue only if
        if (

            // source node exists in the lookup array AND
            isset(self::$lookup[$source]) &&

            // target node exists in the lookup array OR is 0 (indicating a topmost node)
            (isset(self::$lookup[$target]) || $target == 0) &&
            
            // target node is not a child node of the source node (that would cause infinite loop)
            !in_array($target, array_keys($this->get_children($source)))
            
        ) {
        
            // the source's parent node's ID becomes the target node's ID
            self::$lookup[$source][$this->properties['parent_column']] = $target;

            // get source node's children nodes (if any)
            $source_children = $this->get_children($source);

            // this array will hold the nodes we need to move
            // by default we add the source node to it
            $sources = array(self::$lookup[$source]);

            // iterate through source node's children
            foreach ($source_children as $child) {

                // save them for later use
                $sources[] = self::$lookup[$child[$this->properties['id_column']]];

                // for now, remove them from the lookup array
                unset(self::$lookup[$child[$this->properties['id_column']]]);

            }

            // the value with which nodes outside the boundary set below, are to be updated with
            $source_rl_difference =

                self::$lookup[$source][$this->properties['right_column']] -

                self::$lookup[$source][$this->properties['left_column']]

                + 1;

            // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
            // the insert, and will need to be updated
            $source_boundary = self::$lookup[$source][$this->properties['left_column']];

            // lock table to prevent other sessions from modifying the data and thus preserving data integrity
            $query = 'LOCK TABLE ' . $this->properties['table_name'] . ' WRITE';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // we'll multiply the "left" and "right" values of the nodes we're about to move with "-1", in order to
            // prevent the values being changed further in the script
            $query = '

                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['left_column'] . ' = ' . $this->properties['left_column'] . ' * -1,
                    ' . $this->properties['right_column'] . ' = ' . $this->properties['right_column'] . ' * -1
                WHERE
                    ' . $this->properties['left_column'] . ' >= ' . self::$lookup[$source][$this->properties['left_column']] . ' AND
                    ' . $this->properties['right_column'] . ' <= ' . self::$lookup[$source][$this->properties['right_column']] . '

            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // remove the source node from the list
            unset(self::$lookup[$source]);

            // iterate through the remaining nodes in the lookup array
            foreach (self::$lookup as $id=>$properties) {

                // if the "left" value of node is outside the boundary
                if (self::$lookup[$id][$this->properties['left_column']] > $source_boundary)

                    // decrement it
                    self::$lookup[$id][$this->properties['left_column']] -= $source_rl_difference;

                // if the "right" value of item is outside the boundary
                if (self::$lookup[$id][$this->properties['right_column']] > $source_boundary)

                    // decrement it
                    self::$lookup[$id][$this->properties['right_column']] -= $source_rl_difference;

            }

            // update the nodes in the database having their "left"/"right" values outside the boundary
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['left_column'] . ' = ' . $this->properties['left_column'] . ' - ' . $source_rl_difference . '
                WHERE
                    ' . $this->properties['left_column'] . ' > ' . $source_boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['right_column'] . ' = ' . $this->properties['right_column'] . ' - ' . $source_rl_difference . '
                WHERE
                    ' . $this->properties['right_column'] . ' > ' . $source_boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // get children nodes of target node (first level only)
            $target_children = $this->get_children((int)$target, true);
            
            // if node is to be inserted in the default position (as the last of target node's children nodes)
            if ($position === false)

                // give a numerical value to the position
                $position = count($target_children);

            // if a custom position was specified
            else {

                // make sure given position is an integer value
                $position = (int)$position;

                // if position is a bogus number
                if ($position > count($target_children) || $position < 0)

                    // use the default position (as the last of the target node's children)
                    $position = count($target_children);

            }

            // because of the insert, some nodes need to have their "left" and/or "right" values adjusted

            // if target node has no children nodes OR the node is to be inserted as the target node's first child node
            if (empty($target_children) || $position == 0)

                // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
                // the insert, and will need to be updated
                // if parent is not found (meaning that we're inserting a topmost node) set the boundary to 0
                $target_boundary = isset(self::$lookup[$target]) ? self::$lookup[$target][$this->properties['left_column']] : 0;

            // if target has any children nodes and/or the node needs to be inserted at a specific position
            else {
            
                // find the target's child node that currently exists at the position where the new node needs to be inserted to
                // since PHP 5.3 this needs to be done in two steps rather than
                // $target_children = array_shift(array_slice($target_children, $position - 1, 1));
                // or PHP will trigger a warning "Strict standards: Only variables should be passed by reference"
                $slice = array_slice($target_children, $position - 1, 1);
                $target_children = array_shift($slice);

                // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
                // the insert, and will need to be updated
                $target_boundary = $target_children[$this->properties['right_column']];

            }

            // iterate through the records in the lookup array
            foreach (self::$lookup as $id => $properties) {

                // if the "left" value of node is outside the boundary
                if ($properties[$this->properties['left_column']] > $target_boundary)

                    // increment it
                    self::$lookup[$id][$this->properties['left_column']] += $source_rl_difference;

                // if the "left" value of node is outside the boundary
                if ($properties[$this->properties['right_column']] > $target_boundary)

                    // increment it
                    self::$lookup[$id][$this->properties['right_column']] += $source_rl_difference;

            }

            // update the nodes in the database having their "left"/"right" values outside the boundary
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['left_column'] . ' = ' . $this->properties['left_column'] . ' + ' . $source_rl_difference . '
                WHERE
                    ' . $this->properties['left_column'] . ' > ' . $target_boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['right_column'] . ' = ' . $this->properties['right_column'] . ' + ' . $source_rl_difference . '
                WHERE
                    ' . $this->properties['right_column'] . ' > ' . $target_boundary . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // finally, the nodes that are to be inserted need to have their "left" and "right" values updated
            $shift = $target_boundary - $source_boundary + 1;

            // iterate through the nodes to be inserted
            foreach ($sources as $properties) {

                // update "left" value
                $properties[$this->properties['left_column']] += $shift;

                // update "right" value
                $properties[$this->properties['right_column']] += $shift;

                // add the item to our lookup array
                self::$lookup[$properties[$this->properties['id_column']]] = $properties;

            }

            // also update the entries in the database
            // (notice that we're subtracting rather than adding and that finally we multiply by -1 so that the values
            // turn positive again)
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['left_column'] . ' = (' . $this->properties['left_column'] . ' - ' . $shift . ') * -1,
                    ' . $this->properties['right_column'] . ' = (' . $this->properties['right_column'] . ' - ' . $shift . ') * -1
                WHERE
                    ' . $this->properties['left_column'] . ' < 0
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // finally, update the parent of the source node
            $query = '
                UPDATE
                    ' . $this->properties['table_name'] . '
                SET
                    ' . $this->properties['parent_column'] . ' = ' . $target . '
                WHERE
                    ' . $this->properties['id_column'] . ' = ' . $source . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // release table lock
            $query = 'UNLOCK TABLES';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            // reorder the lookup array
            $this->_reorder_lookup_array();

            // return true as everything went well
            return true;

        }

        // if scripts gets this far, return false as something must've went wrong
        return false;

    }
    
    /**
     *  Reads the data from the MySQL table and creates a lookup array. Searches will be done in the lookup array
     *  rather than always querying the database.
     *
     *  @return void
     *
     *  @access private
     */
    function _init() {
    
        // if the results are not already cached
        if (!isset(self::$lookup) || $this->_reload) {
    
            // fetch data from the database
            $query = '
                SELECT
                    *
                FROM
                    ' . $this->properties['table_name'] . '
                ORDER BY
                    ' . $this->properties['left_column'] . '
            ';
            $this->properties['database']->setQuery($query);
            $this->properties['database']->query();
            
            self::$lookup = $this->properties['database']->loadAssocList($this->properties['id_column']);
        }

    }

    /**
     *  Updates the lookup array after inserts and deletes.
     *
     *  @return void
     *
     *  @access private
     */
    function _reorder_lookup_array() {

        // re-order the lookup array

        // iterate through the nodes in the lookup array
        foreach (self::$lookup as $properties)

            // create a new array with the name of "left" column, having the values from the "left" column
            ${$this->properties['left_column']}[] = $properties[$this->properties['left_column']];

        // order the array by the left column
        // in the ordering process, the keys are lost
        array_multisort(${$this->properties['left_column']}, SORT_ASC, self::$lookup);

        $tmp = array();

        // iterate through the existing nodes
        foreach (self::$lookup as $properties)

            // and save them to a different array, this time with the correct ID
            $tmp[$properties[$this->properties['id_column']]] = $properties;

        // the updated lookup array
        self::$lookup = $tmp;

    }
    
    /**
     * Gets the node specified by the id of the node
     * 
     * @param int $node
     * @return Ambigous <unknown, multitype:number string unknown , multitype:unknown , number, NULL>|boolean
     */
    function get_node($node){
    	
    	// lazy connection: touch the database only when the data is required for the first time and not at object instantiation
    	$this->_init();
    	
    	if ( isset(self::$lookup[$node]) ) {
    		
    		return self::$lookup[$node];
    	}
    	
    	return false;
    	
    }
    
    function movedown($id){
    	 
    	$query = '
    		select 
    			'.$this->properties['id_column'].', '.$this->properties['parent_column'].', '.$this->properties['order_column'].' 
    		from 
    			'.$this->properties['table_name'].' 
    		where 
    			'.$this->properties['id_column'].'='.$id;
    	
    	$this->properties['database']->setQuery($query);
    	$source = $this->properties['database']->loadAssoc();
    	 
    	$query = '
    		select 
    			id, '.$this->properties['parent_column'].', '.$this->properties['order_column'].' 
    		from 
    			'.$this->properties['table_name'].' 
    		where 
    			'.$this->properties['parent_column'].'='.$source[$this->properties['parent_column']].' and '.$this->properties['order_column'].' > '.$source[$this->properties['order_column']].' 
    		order by 
    			'.$this->properties['order_column'].' 
    		limit 
    			1';
    	
    	$this->properties['database']->setQuery($query);
    	$target = $this->properties['database']->loadAssoc();
    	 
    	if($target){
    
    		$query = '
    			update 
    				'.$this->properties['table_name'].' 
    			set 
    				'.$this->properties['order_column'].'='.$source[$this->properties['order_column']].' 
    			where 
    				'.$this->properties['id_column'].'='.$target[$this->properties['id_column']];
    		
    		$this->properties['database']->setQuery($query);
    
    		if($this->properties['database']->query()){
    			 
    			$query = '
    				update 
    					'.$this->properties['table_name'].' 
    				set 
    					'.$this->properties['order_column'].'='.$target[$this->properties['order_column']].' 
    				where 
    					'.$this->properties['id_column'].'='.$source[$this->properties['id_column']];
    			
    			$this->properties['database']->setQuery($query);
    			
    			if($this->properties['database']->query()){
    			
    				return $this->rebuild();
    			}
    		}else{
    			
    			return false;
    		}
    	}else{
    
    		return false;
    	}
    	
    	return false;
    }
    
    function moveup($id){
    	 
    	$query = '
    		select 
    			'.$this->properties['id_column'].', '.$this->properties['parent_column'].', '.$this->properties['order_column'].' 
    		from 
    			'.$this->properties['table_name'].' 
    		where 
    			'.$this->properties['id_column'].'='.$id;
    	
    	$this->properties['database']->setQuery($query);
    	$source = $this->properties['database']->loadAssoc();
    	 
    	$query = '
    		select 
    			id, '.$this->properties['parent_column'].', '.$this->properties['order_column'].' 
    		from 
    			'.$this->properties['table_name'].' 
    		where 
    			'.$this->properties['parent_column'].'='.$source[$this->properties['parent_column']].' and '.$this->properties['order_column'].' < '.$source[$this->properties['order_column']].' 
    		order by 
    			'.$this->properties['order_column'].' desc 
    		limit 
    			1';
    	
    	$this->properties['database']->setQuery($query);
    	$target = $this->properties['database']->loadAssoc();
    	 
    	if($target){
    
    		$query = '
    			update 
    				'.$this->properties['table_name'].' 
    			set 
    				'.$this->properties['order_column'].'='.$source[$this->properties['order_column']].' 
    			where 
    				'.$this->properties['id_column'].'='.$target[$this->properties['id_column']];
    		
    		$this->properties['database']->setQuery($query);
    
    		if($this->properties['database']->query()){
    			 
    			$query = '
    				update 
    					'.$this->properties['table_name'].' 
    				set 
    					'.$this->properties['order_column'].'='.$target[$this->properties['order_column']].' 
    				where 
    					'.$this->properties['id_column'].'='.$source[$this->properties['id_column']];
    			
    			$this->properties['database']->setQuery($query);
    			 
    			if($this->properties['database']->query()){
    
    				return $this->rebuild();
    			}
    		}else{
    			 
    			return false;
    		}
    	}else{
    
    		return false;
    	}
    	
    	return false;
    }
    
    /**
     * Rebuild the tree by recreating order, left and right columns
     * 
     * @return boolean
     */
    function rebuild($skip_ordering = false){
    	
    	$query = '
    		SELECT 
    			' . $this->properties['id_column'] . ' 
    		FROM 
    			' . $this->properties['table_name'] . ' 
    		WHERE 
    			' . $this->properties['parent_column'].' = 0 
    		ORDER BY 
    			' . $this->properties['left_column'] . ' ASC';
    	
    	$this->properties['database']->setQuery($query);
    	$parent = $this->properties['database']->loadResult();
    	
    	$this->_rebuild_tree($parent, 1);
    	
    	if( !$skip_ordering )
    	{
	    	$query = '
	    		SELECT
	    			' . $this->properties['id_column'] .', ' . $this->properties['parent_column'] . ', ' . $this->properties['order_column'] . ' 
	    		FROM 
	    			' . $this->properties['table_name'] . ' 
	    		ORDER BY
	    			' . $this->properties['parent_column'] . ', ' . $this->properties['order_column'] .' ASC';
	    	
	    	$this->properties['database']->setQuery($query);
	    	$categories = $this->properties['database']->loadObjectList();
	    
	    	if(!empty($categories)){
	    		
	    		$parent = -1;
	    		$norder = 0;
	    		
	    		foreach ($categories as $category){
	    			
	    			$parent_column = $this->properties['parent_column'];
	    			
	    			if($category->$parent_column != $parent){
	    				
	    				$parent = $category->$parent_column;
	    				$norder = 1;
	    			}
	    			
	    			$query = '
	    				UPDATE 
	    					' . $this->properties['table_name'] . ' 
	    				SET
	    					' . $this->properties['order_column'] . ' = ' . $norder . ' 
	    				WHERE 
	    					' . $this->properties['id_column'] . ' = ' . $category->id;
	    			
	    			$this->properties['database']->setQuery($query);
	    			
	    			if(!$this->properties['database']->query()){
	    				
	    				return false;
	    			}
	    			
	    			$norder++;
	    		}
	    	}else{
	    		
	    		return false;
	    	}
    	}
    	    	
    	return true;
    }
    
    function get_tree_list($nodes, $fields = null, $nlevel = 0){
    	
    	$content = '<ul class="cat-list">';
    	
    	foreach($nodes as $node){

    		$value = CJFunctions::escape($node['title']);

    		if(!empty($fields['stat_field'])){
    			
    			$value = $value . ' <small class="muted">( ' . $node[$fields['stat_field']];
    			
    			if(!empty($fields['stat_field2'])){
    				
    				$value = $value . ' / ' . $node[$fields['stat_field2']];
    			}
    			
    			$value = $value . ' )</small>';
    		}
    		
    		$title = '';
    		
    		if(!empty($fields['stat_field2'])){
    			
    			$title = !empty($fields['title']) ? JText::sprintf($fields['title'], $node[$fields['stat_field']], $node[$fields['stat_field2']]) : '';
    		} else if(!empty($fields['stat_field'])){
    			
    			$title = !empty($fields['title']) ? JText::sprintf($fields['title'], $node[$fields['stat_field']]) : '';
    		}
    		
    		$attribs = array('class'=>'tooltip-hover', 'title'=>$title);
    		$content = $content . '<li rel="'.$node['id'].'">';
    		$content = $content . JHtml::link(JRoute::_($fields['url'].'&id='.$node['id'].':'.$node['alias'].$fields['itemid']), $value, $attribs);
    		
    		if(!empty($node['children'])) {
    			 
    			$content = $content . $this->get_tree_list($node['children'], $fields, $nlevel + 1);
    		}
    		
    		$content = $content . '</li>';
    	}
    	
    	$content = $content . '</ul>';
    	
    	return $content;
    }
    
    /**
     * Function to get the tree table listing. A fields parameter should be passed with the list of columns need to be rendered. Each column of this array should be an associative array with the following fields.<br/>
     * <strong>header:</strong>header title of the column<br/>
     * <strong>id:</strong> flag to indicate if the id need to include in the category url<br/>
     * <strong>src:</strong> base url of the category edit function<br/>
     * <strong>type:</strong> type of the column - category, text, link, slink, up, down<br/>
     * <strong>align:</strong> in case of text type field, alignment<br/>
     * <strong>image-X:</strong> alt images (image-0 and image-1) for flag fields
     * <strong>src-X:</strong> alt srcs of flag fields
     * <strong>value:</strong> value of the item, if not to include title<br/>
     * 
     * @param string $content the return content
     * @param array $nodes list of category nodes
     * @param array $fields extra fields to be added to table.
     * @param unknown_type $nlevel
     * @return string
     */
    function get_tree_table($content, $nodes, $fields = array(), $nlevel = 0){
    	
    	$num = 0;
    	static $row_num;
    	
    	foreach($nodes as $node){
    		
    		$row_num++;
    		
    		$content = $content . '<tr class="row'.($row_num % 2).'">';
    		$content = $content . '<td>' . ($row_num) . '</td>';
    		
    		foreach ($fields as $field){
    			
    			switch ($field['type']){
    				
    				case 'category':
    					
    					$url = $field['src'] . ($field['id'] ? '&id=' . $node[$this->properties['id_column']] . ':' . $node['alias'] : '');
    					
    					$content = $content . '<td>' . str_repeat('&hellip; ', $nlevel) . JHtml::link($url, CJFunctions::escape($node[$this->properties['title_column']])) . '</td>';
    					
    					break;
    				
    				case 'text':
    					
    					$content = $content . '<td align="' . $field['align'] . '">' . CJFunctions::escape($node[$field['name']]) . '</td>';
    					break;
    					
    				case 'link':
    					
    					$url = $field['src'] . ($field['id'] ? '&id=' . $node[$this->properties['id_column']] . ':' . $node['alias'] : '');
    					$value = $field['value'] != null ? $field['value'] : ( $field['value'] == null ? $field['header'] : $node[$field['title_column']] );
    					
    					$content = $content . '<td align="' . $field['align'] . '"><a href="' . $url . '">' . $value . '</a></td>';
    					break;
    					
					case 'slink':
    							
						$url = $field['src-' . $node[$field['name']]] . ($field['id'] ? '&id=' . $node[$this->properties['id_column']] . ':' . $node['alias'] : '');
						$class = !empty($field['class']) ? 'class="'.$field['class'].'"' : '';
						
						$content = $content . '<td align="' . $field['align'] . '">';
						$content = $content . '<a '.$class.' href="' . $url . '">' . $field['image-' . $node[$field['name']]] . '</a></td>';
    					break;
    							
    				case 'up':
    					
    					$url = $field['src'] . ($field['id'] ? '&id=' . $node[$this->properties['id_column']] . ':' . $node['alias'] : '');

    					if( $num > 0 ){
    					
    						$attribs = !empty($field['attribs']) ? $field['attribs'] : array();
    						$content = $content . '<td align="' . $field['align'] . '">'.JHtml::link(JRoute::_($url), $field['value'], $attribs).'</td>';
    					} else {
    						
    						$content = $content . '<td></td>';
    					}
    					
    					break;
    					
    				case 'down':
    					
    					$url = $field['src'] . ($field['id'] ? '&id=' . $node[$this->properties['id_column']] . ':' . $node['alias'] : '');
    					
    					if($num < count($nodes) - 1){
    							
    						$attribs = !empty($field['attribs']) ? $field['attribs'] : array();
    						$content = $content . '<td align="' . $field['align'] . '">'.JHtml::link(JRoute::_($url), $field['value'], $attribs).'</a></td>';
    					} else {
    					
    						$content = $content . '<td></td>';
    					}
    						
    					break;
    			}
    			
    		}
    		
    		$content = $content . '</tr>';
    		
    		if(!empty($node['children'])) {
    			
    			$content = $content . $this->get_tree_table('', $node['children'], $fields, $nlevel + 1);
    		}
    		
    		$num++;
    	}
    	
    	return $content;;
    }
    
    /**
     * Updates specific field of the category tree. The field name should be passed as parameter <code>$update_column</code> while the items list table name also passed as a parameter.
     * 
     * @param string $items_table items list table
     * @param string $update_column column name to be updated
     * @return boolean true if success false otherwise.
     */
    function update_category_counts($items_table, $update_column, $catid_column='catid'){
    	
    	$query = '
    		SELECT 
    			parent.'.$this->properties['id_column'].', COUNT(items.'.$this->properties['id_column'].') as item_count
    		FROM 
    			'.$this->properties['table_name'].' AS node ,
    			'.$this->properties['table_name'].' AS parent,
    			'.$items_table.' AS items
    		WHERE 
    			node.nleft BETWEEN parent.nleft AND parent.nright 
    			AND node.id = items.'.$catid_column.'
    			AND items.published = 1
    		GROUP BY 
    			parent.id
    		ORDER BY 
    			node.nleft;
    	';
    	
    	$this->properties['database']->setQuery($query);
    	$nodes = $this->properties['database']->loadAssocList();
    	$updated_ids = array();
    	
    	if(!empty($nodes)){
    		
    		foreach ($nodes as $node) {
    			
    			$query = '
    				update 
    					'.$this->properties['table_name'].' 
    				set 
    					'.$update_column.' = '.$node['item_count'].' 
    				where 
    					'.$this->properties['id_column'].' = '.$node[$this->properties['id_column']];
    			
    			$this->properties['database']->setQuery($query);
    			
    			if(!$this->properties['database']->query()){
    				
    				return false;
    			}
    			
    			$updated_ids[] = $node[$this->properties['id_column']];
    		}
    	}
    	
    	if(!empty($updated_ids)){
    		
	    	$query = '
	    			update
	    				'.$this->properties['table_name'].'
	    			set 
	    				'.$update_column.' = 0
	    			where
	    				'.$this->properties['id_column'].' not in ('.implode(',', $updated_ids).')';
	    	
	    	$this->properties['database']->setQuery($query);
	    	$this->properties['database']->query();
    	}
    	    	
    	return true;
    }
    
    function get_error_msg(){
    	
    	return $this->_error;
    }
    
    /**
     * Rebuild nleft and nright values
     * 
     * @param int $parent
     * @param int $left
     */
    private function _rebuild_tree($parent, $left){
    	
    	$right = $left+1;
    	
    	$query = '
    		SELECT
    			' . $this->properties['id_column'] . ' 
    		FROM
    			' . $this->properties['table_name'] . ' 
    		WHERE 
    			' . $this->properties['parent_column'] .' = ' . $parent.' 
    		ORDER BY ' . $this->properties['order_column'] . ' ASC';
    	
    	$this->properties['database']->setQuery($query);
    	$nodes = $this->properties['database']->loadColumn();
    	
    	if(count($nodes)){
    		
    		foreach ($nodes as $node) {
    			
    			$right = $this->_rebuild_tree($node, $right);
    		}
    	}
    
    	$query = '
    		UPDATE
    			' . $this->properties['table_name'] . ' 
    		SET
    			' . $this->properties['left_column'] . ' = ' . $left . ', ' . $this->properties['right_column'] . ' = ' . $right . '
    		WHERE
    			' . $this->properties['id_column'] . ' = ' . $parent;
    	
    	$this->properties['database']->setQuery($query);
    	$this->properties['database']->query();
    	
    	return $right + 1;
    }
    
    public function udpate_level($level_column = 'level')
    {
    	$max_rows = 500;
    	$query = 'select count(*) from '.$this->properties['table_name'];
    	$this->properties['database']->setQuery($query);
    	$count = (int) $this->properties['database']->loadResult();
    	
    	try 
    	{
	    	// update in batches
	    	for ($i = 0; $i < ceil($count / $max_rows); $i++)
	    	{
    	
		    	$query = '
		    	SELECT 
		    			node.'.$this->properties['id_column'].', (COUNT(parent.'.$this->properties['id_column'].') - 1) AS depth
		    	FROM 
		    		' . $this->properties['table_name'] . ' AS node,
		    		' . $this->properties['table_name'] . ' AS parent
		    	WHERE 
		    		node.'.$this->properties['left_column'].' BETWEEN parent.'.$this->properties['left_column'].' AND parent.'.$this->properties['right_column'].'
		    	GROUP BY 
		    		node.'.$this->properties['id_column'].'
		    	ORDER BY 
		    		node.'.$this->properties['left_column'];
		    	
		    	$this->properties['database']->setQuery($query, $i * $max_rows, $max_rows);
		    	$categories = $this->properties['database']->loadObjectList();
	    		
	    		if ( empty($categories) )
	    		{
	    			return false;
	    		}
	    		
	    		$updates = array();
	    		foreach ($categories as $category)
		    	{
		    		$updates[] = 'WHEN '.$category->id.' THEN '.$category->depth;
		    	}
		    	
		    	if( !empty($updates) )
		    	{
			    	$query = 'UPDATE ' . $this->properties['table_name'] . ' SET '.$level_column.' = CASE id '.implode(' ', $updates).' END';
			    	$this->properties['database']->setQuery($query);
			    	$this->properties['database']->execute();
		    	}
	    	}
    	}
    	catch (Exception $e){}
    }
}

?>