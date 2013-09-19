<?php
    protected function listDirectoryTree($array)
    {
        echo "<ul class='nav nav-list directory-tree'>";
        ksort($array, SORT_STRING);
        foreach($array as $key => $value)
        {
            if(is_array($value))
            {
                if(stristr($this->fileName,$key) != false)
                {
                    $class = "folder show";
                }
                else
                {
                    $class = "folder";
                }
                echo "<li class='" . $class . "'>";
                echo "<a class='folder-url' href=''><i class='icon-folder-close'>&nbsp;";
                $explodeArray = explode('/',$key);
                echo end($explodeArray);
                echo "</i></a>";
                $this->listDirectoryTree($value);
                echo "</li>";
            }

            elseif(is_object($value))
            {
                echo "<li>";
                echo "<a class='file' href='" . JRoute::_('index.php?option=com_templates&view=template&id=' . $this->id . '&file=' . $value->id) . "'>";
                echo "<i class='icon-file'>&nbsp;" . $value->name . "</i>";
                echo "</a>";
                echo "</li>";
            }

        }

        echo "</ul>";

    }
