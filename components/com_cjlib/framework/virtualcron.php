<?php
// +----------------------------------------------------------------------+
// | virtualCron 0.1                                                      |
// +----------------------------------------------------------------------+
// | Date: 6 Mar 2007                                                     |
// +----------------------------------------------------------------------+
// | License: LGPL                                                        |
// +----------------------------------------------------------------------+
// | virtualCron is a PHP class, that simulates a cron job,               |
// | in order to execute scripts periodically                             |
// | without a real crontab command.                                      |
// +----------------------------------------------------------------------+
// | Author: Giorgos Tsiledakis <gt(at)corissia(dot)com>                  |
// +----------------------------------------------------------------------+
//==============================================================================================
// To simulate a cron job, we need an absolute time value to estimate the time required to perform some task.
// This class uses the generation or last modification time of a control file on the server to achieve that.
//
// Advantage: no need of crontab commands or solutions like php preprocessing
// Disadvantage: a user access is required to perform some task
//
// Example:
// $vcron=new virtualcron(10,"virtualcron.txt"); // "10" minutes after the last modification of "virtualcron.txt"
// if ($vcron->allowAction()) print "Hello World"; // some action will be allowed
//
// Within the delay period nothing will happen -> allowAction=false
// if the delay period has passed the first user, who runs the script, with activate some action -> allowAction=true.
// All other users will be again within the delay period and nothing will happen.
// That's all
// Use this e.g. to parse RSS Feeds every 30 minutes, to generate your google sitemap once a week etc.
// Of course, prefer a real cron job if your provider allows you to use one
//==============================================================================================
class virtualcron{

	var $controlFile="virtualcron.txt"; // the default url of the control file
	var $minDelay="1"; // the default delay period in minutes

	//==============================================================================================
	// PUBLIC [Constructor]
	// param $minDelay: sets the delay period in minutes (optional)
	// param $conrolFile: sets the control file url (optional). The generation or last modification time
	// of this file is used to estimate the time required to be passed, in order to allow an action.
	// If there is no control file the function will try to generate one.
	//==============================================================================================
	function virtualcron($minDelay=false,$controlFile=false){
		if ($minDelay) $this->minDelay=$minDelay;
		if ($controlFile) $this->controlFile=$controlFile;
		$this->lastExec=0; // it will contain the UNIXTIME of the last action
		$this->nextExec=0; // it will contain the UNIXTIME of the next action
		$this->secToExec=0; // it will contain the time in seconds until of the next action
		if (file_exists($this->controlFile)) $this->check=true;
		else{
			$handle=fopen($this->controlFile, "w");
			if (!$handle) $this->check=false;
			else{
				if (!fwrite($handle,time())) $this->check=false;
				else{
					fclose($handle);
					$this->check=true;
				}
			}
		}
	}
	//==============================================================================================
	// PUBLIC allowAction() [boolean]
	// checks if the current execution time is within the delay period. Example:
	// $vcron=new virtualcron();
	// if ($vcron->allowAction()) ...do something...
	// That's all
	//==============================================================================================
	function allowAction(){
		$now=time();
		if ($this->check) $FT=$this->getFileCreationTime($this->controlFile);
		if ($FT){
			$nextExec=$FT+($this->minDelay*60)-$now;
			if ($nextExec<0){
				$handle=fopen($this->controlFile, "w");
				if (!$handle) return false;
				else{
					if (!fwrite($handle,$now)) return false;
					else{
						fclose($handle);
						$this->lastExec=$now;
						$this->nextExec=$now+($this->minDelay*60);
						$this->secToExec=$this->minDelay*60;
						return true;
					}
				}
			}
			else {
				$this->lastExec=$FT;
				$this->nextExec=$FT+$nextExec;
				$this->secToExec=$nextExec;
				return false;
			}
		}
		else return false;
	}
	//==============================================================================================
	// PRIVATE getFileCreationTime()
	// estimates the generation or last modification time of the control file (UNIXTIME)
	//==============================================================================================
	function getFileCreationTime($filename){
		if (function_exists("filemtime")){
			$FT=filemtime($filename);
		}
		else{
			$FT=false;
		}
		return $FT;
	}

}
?>