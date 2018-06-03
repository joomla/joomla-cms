<?php
namespace Wamania\Snowball\Tests;

class CsvFileIterator implements \Iterator
{
    protected $file;
    protected $key = 0;
    protected $current;

    public function __construct($file)
    {
        if (! ($this->file = fopen($file, 'r'))) {
            die('Can\'t open file '.$this->file)."\n";
        }
    }

    public function __destruct()
    {
        fclose($this->file);
    }

    public function rewind()
    {
        rewind($this->file);
        //$this->current = fgetcsv($this->file, null, "\t");
        $line = fgets($this->file);
        $current = explode(' ', $line);
        $current = array_filter($current);
        $current = array_values($current);
        $current = array_map('trim', $current);
        $this->current = $current;
        $this->key = 0;
    }

    public function valid()
    {
        return !feof($this->file);
    }

    public function key()
    {
        return $this->key;
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        $line = fgets($this->file);
        $current = explode(' ', $line);
        $current = array_filter($current);
        $current = array_values($current);
        $current = array_map('trim', $current);
        $this->current = $current;
        $this->key++;
    }
}
