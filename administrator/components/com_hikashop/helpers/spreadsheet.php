<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopSpreadsheetHelper {
	var $format;
	var $filename;
	var $separator;
	var $decimal_separator;
	var $currLine;
	var $buffer;
	var $forceQuote;
	var $progressive;
	var $headerSent;

	function __construct() {
		$this->init();
	}

	function init($format = 'csv', $filename = 'export', $sep = ';', $forceQuote = false, $decimal_separator = '.') {
		$this->currLine = -1;
		$this->buffer = '';
		$this->separator = ';';
		$this->filename = $filename;
		$this->forceQuote = $forceQuote;
		$this->progressive = false;
		$this->headerSent = false;

		switch( strtolower($format) ) {
			case 'xls':
				$this->format = 1;
				break;

			default:
			case 'csv':
				$this->format = 0;
				$this->separator = $sep;
				$this->decimal_separator = $decimal_separator;
				break;
		}

		if( empty($this->filename) )
			$this->filename = 'export';

		if( $this->format == 1 )
			$this->filename .= '.xls';
		else
			$this->filename .= '.csv';

		if( $this->format == 1 )
			$this->buffer .= pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
	}

	function send() {
		if(!$this->headerSent) {
			if( $this->format == 1 )
				$this->buffer .= pack("ss", 0x0A, 0x00);

			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment;filename='.$this->filename.' ');
			header('Content-Transfer-Encoding: binary ');
			if(!$this->progressive) {
				header('Content-Length: '.strlen($this->buffer));
			}
			$this->headerSent = true;
		}

		echo $this->buffer;
		$this->buffer = '';

		if(!$this->progressive)
			exit;
	}

	function get() {
		if( $this->format == 1 )
			$this->buffer .= pack('vv',0x000A,0x0000);
		else if($this->format == 2)
			$this->buffer .= '</Table></Worksheet></Workbook>';

		$ret = $this->buffer;
		$this->buffer = '';

		return $ret;
	}

	function flush() {
		if($this->progressive){
			if(!$this->headerSent) {
				$this->send();
			} else {
				echo $this->buffer;
				$this->buffer = '';
			}
		}
	}

	function writeNumber($row, $col, $value, $lastOne) {
		if( $this->format == 1 ) {
			$this->currLine = $row;
			$this->buffer .= pack("sssss", 0x203, 14, $row, $col, 0x0);
			$this->buffer .= pack("d", $value);
		} else {
			if( $this->currLine < $row )
				$this->newLine();
			$this->currLine = $row;

			$floatValue = (float)hikashop_toFloat($value);
			if($floatValue == (int)$floatValue)
				$this->buffer .= (int)$value;
			else
				$this->buffer .= number_format($floatValue, 5, $this->decimal_separator, '');

			if(!$lastOne)
				$this->buffer .= $this->separator;
		}
	}

	function writeText($row, $col, $value, $lastOne) {
		if( $this->format == 1 ) {
			$this->currLine = $row;
			$len = strlen($value);
			$this->buffer .= pack("ssssss", 0x204, 8 + $len, $row, $col, 0x0, $len);
			$this->buffer .= $value;
		} else {
			if( $this->currLine < $row )
				$this->newLine();
			$this->currLine = $row;
			if( empty($value) ) {
				$value = '""';
			} elseif( strpos($value, '"') !== false ) {
				$value = '"' . str_replace('"','""',$value) . '"';
			} elseif( $this->forceQuote || (strpos($value, $this->separator) !== false) || (strpos($value, "\n") !== false) || (trim($value) != $value) ) {
				$value = '"' . $value . '"';
			}
			$this->buffer .= $value;
			if(!$lastOne)
				$this->buffer .= $this->separator;
		}
	}

	function newLine() {
		if( $this->format == 0 ) {
			$this->buffer .= "\r\n";
		}
	}

	function writeLine($data) {
		$i = 0;
		$this->currLine++;
		if( $this->currLine > 0 )
			$this->newLine();
		end($data);
		$last = key($data);
		reset($data);
		foreach($data as $k => $value) {
			$lastOne = false;
			if ($last===$k)
				$lastOne = true;
			if(is_array($value))
				continue;
			if( is_numeric($value) ) {
				$this->writeNumber($this->currLine, $i++, $value, $lastOne);
			} else {
				$this->writeText($this->currLine, $i++, $value, $lastOne);
			}
		}
		if($this->progressive) {
			$this->flush();
		}
	}
}
