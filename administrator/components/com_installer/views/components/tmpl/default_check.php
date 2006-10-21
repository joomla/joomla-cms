<?php 
for ($i=0; $i <= count($this->result) - 1; $i++) {
	$component = $this->result[$i];
	$files = $component['files'];
	$component = $component['component'];
	if (is_array($files)) {
		echo '<div style="background-color:red;">There are files of '. $component .' that are not original:';
		echo '<table><th><td>Filename</td><td>Exists?</td></th>';
		foreach($files as $file) {
			echo '<tr><td>'. $file['filename'] .'</td><td>'. $file['exists'] .'</td></tr>';
		}
		echo '</table></div>';		
	} else {
		if ($files == true) {
			echo '<div style="background-color:green;">All files for '. $component .' are okay.</div>';
		} else {
			echo '<div style="background-color:red;">Could not find file for '. $component .'!</div>';
		}
	}

}
?>