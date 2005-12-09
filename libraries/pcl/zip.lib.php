<?php
/**
* @version $Id$
* @package Joomla
*/


// $Id$
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
//  http://www.zend.com/codex.php?id=535&single=1
//	By Eric Mueller <eric@themepark.com>
//
//	http://www.zend.com/codex.php?id=470&single=1
//	by Denis125 <webmaster@atlant.ru>
//
//	A patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
//	date and time of the compressed file
//
//	Official ZIP file format: http://www.pkware.com/appnote.txt

class zipfile {
	var $datasec = array();
	var $ctrl_dir = array();
	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	var $old_offset = 0;

	function unix2DosTime($unixtime = 0) {
		$timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
		if ($timearray['year'] < 1980) {
			$timearray['year']= 1980;
			$timearray['mon'] = 1;
			$timearray['mday']= 1;
			$timearray['hours']	= 0;
			$timearray['minutes'] = 0;
			$timearray['seconds'] = 0;
		}
		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}

	function addFile($data, $name, $time = 0) {
		$name = str_replace('\\', '/', $name);

		$dtime= dechex($this->unix2DosTime($time));
		$hexdtime = '\x' . $dtime[6] . $dtime[7] . '\x' . $dtime[4] . $dtime[5] . '\x' . $dtime[2] . $dtime[3] . '\x' . $dtime[0] . $dtime[1];
		eval('$hexdtime = "' . $hexdtime . '";');

		$fr = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00" . $hexdtime;

		$unc_len = strlen($data);
		$crc = crc32($data);
		$zdata = gzcompress($data);
		$zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
		$c_len	= strlen($zdata);
		$fr .= pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len) . pack('v', strlen($name)) . pack('v', 0) . $name . $zdata . pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len);

		$this -> datasec[] = $fr;
		$new_offset = strlen(implode('', $this->datasec));

		$cdrec = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00" . $hexdtime . pack('V', $crc) . pack('V', $c_len) . pack('V', $unc_len) . pack('v', strlen($name)) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('V', 32) . pack('V', $this -> old_offset );
		$this -> old_offset = $new_offset;
		$cdrec .= $name;
		$this -> ctrl_dir[] = $cdrec;
	}

	function file() {
		$data = implode('', $this -> datasec);
		$ctrldir = implode('', $this -> ctrl_dir);
		return $data . $ctrldir . $this -> eof_ctrl_dir . pack('v', sizeof($this -> ctrl_dir)) .  pack('v', sizeof($this -> ctrl_dir)) .  pack('V', strlen($ctrldir)) . pack('V', strlen($data)) . "\x00\x00";
	}
}
?>