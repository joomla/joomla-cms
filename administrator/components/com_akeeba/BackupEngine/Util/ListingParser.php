<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

/**
 * Parses directory listings of the standard UNIX or MS-DOS style, i.e. what is most commonly returned by FTP and SFTP
 * servers running on *NIX and Windows machines.
 *
 * This class is intended to be used with the result RemoteResourceInterface::getRawList, parsing the raw folder listing
 * returned by an (S)FTP server -meant to be read by a human- into something you can programmatically work with. Using
 * RemoteResourceInterface::getWrapperStringFor with DirectoryIterator is generally preferable, if only much slower due
 * to the synchronous nature of remote stat() requests on each iterated element.
 */
class ListingParser
{
	/**
	 * Parse a UNIX- or MS-DOS-style directory listing.
	 *
	 * You get a hash array with entries. Each entry has the following keys:
	 * name:    the file / folder name.
	 * type:    file, dir or link.
	 * target:  link target (when type == link).
	 * user:    owner user, numeric or text. IIS FTP fakes this with the literal string "owner".
	 * group:   owner group, numeric or text. IIS FTP fakes this with the literal string "group".
	 * size:    size in bytes; note that some Linux servers report non-zero sizes for directories.
	 * date:    file creation date, most likely blatantly wrong; see below
	 * perms:   permissions in decimal format. Cast with dec2oct to get the 4 digit permissions string, e.g. 1755
	 *
	 * Important Notes
	 *
	 * Some UNIX systems report a size for directories. Do not assume that something is a directory if it's size is 0,
	 * you will be surprised. Look at the 'type' element instead.
	 *
	 * Dates can be off. UNIX-style directory listings state either the year or the time, not both. If the file was
	 * modified during this year you will get a date with a resolution of 1 minute. If the file was modified on a
	 * different year you'll get a date with a resolution of 1 day. MS-DOS listings always contain the time. Again, the
	 * resolution is 1 minute.
	 *
	 * Most MS-DOS style listings don't list junctions and symlinks. As a result they will be reported as regular
	 * directories / files. This is the case for the IIS FTP server. Other servers may return the raw "dir" command
	 * results (as CMD.EXE would parse it) in which case links and link targets do get reported.
	 *
	 * @param   string  $list   The raw listing
	 * @param   bool    $quick  True to only include name, type, size and link target for each file.
	 *
	 * @return  array
	 */
	public function parseListing($list, $quick = false)
	{
		$res = $this->parseUnixListing($list, $quick);

		if (empty($res))
		{
			$res = $this->parseMSDOSListing($list, $quick);
		}

		return $res;
	}

	/**
	 * Parse a UNIX-style directory listing. This is the format produced by ls -la on *NIX systems.
	 *
	 * You get a hash array with entries. Each entry has the following keys:
	 * name: the file / folder name.
	 * type: file, dir or link.
	 * target: link target (when type == link).
	 * user: owner user, numeric or text. IIS FTP fakes this with the literal string "owner".
	 * group: owner group, numeric or text. IIS FTP fakes this with the literal string "group".
	 * size: size in bytes; note that some Linux servers report non-zero sizes for directories.
	 * date: file creation date, most likely blatantly wrong; see below
	 * perms: permissions in decimal format. Cast with dec2oct to get the 4 digit permissions string, e.g. 1755
	 *
	 * @param   string  $list   The raw listing
	 * @param   bool    $quick  True to only include name, type, size and link target for each file.
	 *
	 * @return  array
	 */
	protected function parseUnixListing($list, $quick = false)
	{
		$ret = [];

		$list = str_replace(["\r\n", "\r", "\n\n"], ["\n", "\n", "\n"], $list);
		$list = explode("\n", $list);
		$list = array_map('rtrim', $list);

		foreach ($list as $v)
		{
			$vInfo = preg_split("/[\s]+/", $v, 9);

			if ((is_array($vInfo) || $vInfo instanceof \Countable ? count($vInfo) : 0) != 9)
			{
				continue;
			}

			$entry = [
				'name'   => '',
				'type'   => 'file',
				'target' => '',
				'user'   => '0',
				'group'  => '0',
				'size'   => '0',
				'date'   => '0',
				'perms'  => '0',
			];

			if ($quick)
			{
				$entry = [
					'name'   => '',
					'type'   => 'file',
					'size'   => '0',
					'target' => '',
				];
			}

			// ===== Parse permissions =====
			$permString    = $vInfo[0];
			$permStringLen = strlen($permString);
			$typeBit       = '-';
			$userPerms     = 'r--';
			$groupPerms    = 'r--';
			$otherPerms    = 'r--';

			if ($permStringLen)
			{
				$typeBit = substr($permString, 0, 1);
			}

			switch ($typeBit)
			{
				case "d":
					$entry['type'] = 'dir';
					break;

				case "l":
					$entry['type'] = 'link';
					break;
			}

			// ===== Parse size =====
			$entry['size'] = $vInfo[4];

			if (!$quick)
			{
				if ($permStringLen >= 4)
				{
					$userPerms = substr($permString, 1, 3);
				}

				if ($permStringLen >= 7)
				{
					$groupPerms = substr($permString, 4, 3);
				}

				if ($permStringLen >= 10)
				{
					$otherPerms = substr($permString, 7, 3);
				}

				$bitPart   = 0;
				$permsPart = '';

				[$thisPerms, $thisBit] = $this->textPermsDecode($userPerms);
				$bitPart   += 4 * $thisBit; // SetUID
				$permsPart .= $thisPerms;

				[$thisPerms, $thisBit] = $this->textPermsDecode($groupPerms);
				$bitPart   += 2 * $thisBit; // SetGID
				$permsPart .= $thisPerms;

				[$thisPerms, $thisBit] = $this->textPermsDecode($otherPerms);
				$bitPart   += $thisBit; // Sticky (restricted deletion)
				$permsPart .= $thisPerms;

				$entry['perms'] = octdec($bitPart . $permsPart);

				// ===== Parse ownership =====
				$entry['user']  = $vInfo[2];
				$entry['group'] = $vInfo[3];

				// ===== Parse date =====
				$dateString    = $vInfo[6] . ' ' . $vInfo[5] . ' ' . $vInfo[7];
				$x             = date_create($dateString);
				$entry['date'] = ($x === false) ? 0 : $x->getTimestamp();
			}

			// ===== Parse name =====
			$name = $vInfo[8];

			// Ubuntu (possibly others?) tacks a start when either suid/sgid bits is set
			if (substr($name, -1) == '*')
			{
				$name = substr($name, 0, -1);
			}

			// Link target parsing
			if (strpos($name, '->') !== false)
			{
				[$name, $target] = explode('->', $name);

				$entry['target'] = trim($target);
			}

			$entry['name'] = trim($name);

			// ===== Return the entry =====
			$ret[] = $entry;
		}

		return $ret;
	}

	/**
	 * Parse am MS-DOS-style directory listing. This is the format produced by dir on MS-DOS and Windows systems.
	 *
	 * You get a hash array with entries. Each entry has the following keys:
	 * name: the file / folder name.
	 * type: file, dir or link.
	 * target: link target (when type == link).
	 * user: owner user, numeric or text. IIS FTP fakes this with the literal string "owner".
	 * group: owner group, numeric or text. IIS FTP fakes this with the literal string "group".
	 * size: size in bytes; note that some Linux servers report non-zero sizes for directories.
	 * date: file creation date, most likely blatantly wrong; see below
	 * perms: permissions in decimal format. Cast with dec2oct to get the 4 digit permissions string, e.g. 1755
	 *
	 * @param   string  $list   The raw listing
	 * @param   bool    $quick  True to only include name, type, size and link target for each file.
	 *
	 * @return  array
	 */
	protected function parseMSDOSListing($list, $quick = false)
	{
		$ret = [];

		$list = str_replace(["\r\n", "\r", "\n\n"], ["\n", "\n", "\n"], $list);
		$list = explode("\n", $list);
		$list = array_map('rtrim', $list);

		foreach ($list as $v)
		{
			$vInfo = preg_split("/[\s]+/", $v, 5);

			if ((is_array($vInfo) || $vInfo instanceof \Countable ? count($vInfo) : 0) < 4)
			{
				continue;
			}

			$entry = [
				'name'   => '',
				'type'   => 'file',
				'target' => '',
				'user'   => '0',
				'group'  => '0',
				'size'   => '0',
				'date'   => '0',
				'perms'  => '0',
			];

			if ($quick)
			{
				$entry = [
					'name'   => '',
					'type'   => 'file',
					'size'   => '0',
					'target' => '',
				];
			}

			// The first two fields are date and time
			$dateString = $vInfo[0] . ' ' . $vInfo[1];

			// If position 2 is AM/PM append it and remove it from the list
			if (in_array(strtoupper($vInfo[2]), ['AM', 'PM']))
			{
				$dateString .= ' ' . $vInfo[2];

				// This trick is required to remove the element and fix the indices for the rest of the parsing to work.
				unset ($vInfo[2]);
				$vInfo = array_merge($vInfo);
			}

			if (!$quick)
			{
				$x             = date_create($dateString);
				$entry['date'] = ($x === false) ? 0 : $x->getTimestamp();
			}

			// The third field is either a special type indicator or the file size
			switch (strtoupper($vInfo[2]))
			{
				// Regular directory
				case '<DIR>':
					$entry['type'] = 'dir';
					break;

				// Junction (like a directory symlink, pre-Win7)
				case '<JUNCTION>':
					// File symlink
				case '<SYMLINK>':
					// Directory symlink
				case '<SYMLINKD>':
					$entry['type'] = 'link';
					break;

				default:
					$entry['size'] = (int) $vInfo[2];
					break;
			}

			// And finally the file name. If it's a link it's in the format 'name [target]'
			preg_match('/(.*)[\s]+\[(.*)\]/', $vInfo[3], $matches);

			if (empty($matches))
			{
				$entry['name'] = $vInfo[3];
			}
			else
			{
				$entry['type']   = 'link';
				$entry['name']   = $matches[1];
				$entry['target'] = $matches[2];
			}

			// ===== Return the entry =====
			$ret[] = $entry;
		}

		return $ret;
	}

	/**
	 * Decode a textual permissions representation for a user, group or others to a pair of octal digits (permissions
	 * and flags). For example "r--" is converted to [4, 0], "r-x" to [5, 0], "r-t" to [5, 1]
	 *
	 * @param   string  $perms  The textual permissions representation for a user, group or others
	 *
	 * @return  array  Two octal digits for permissions and flags (suid/sgid/sticky bit)
	 */
	private function textPermsDecode($perms)
	{
		$permBit  = 0;
		$flagBits = 0;

		if (strpos($perms, 'r'))
		{
			$permBit += 4;
		}

		if (strpos($perms, 'w'))
		{
			$permBit += 2;
		}

		/**
		 * Both s and t denote flag set and imply the execute permissions is also granted. For user/groups it's
		 * SetUID/SetGID respectively, for others it's the "sticky" bit (restricted deletion). Since only one of x, s
		 * and t can be present at one time we use an if/elseif block. I don't use a switch because a. I am not 100%
		 * sure that all servers will report the text permissions in rwx order and b. I am not sure that switch and
		 * substr are faster than strpos (and too lazy to benchmark; sorry).
		 */
		if (strpos($perms, 'x'))
		{
			$permBit += 1;
		}
		elseif (strpos($perms, 't'))
		{
			$flagBits += 1;
		}
		elseif (strpos($perms, 's'))
		{
			$flagBits += 1;
		}

		return [$permBit, $flagBits];
	}
}
