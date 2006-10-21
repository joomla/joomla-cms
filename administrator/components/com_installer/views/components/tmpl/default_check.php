<?php 
for ($i=0; $i <= (count($this->result) - 1); $i++) {
	$component = $this->result[$i];
	$files = $component['files'];
	$component = $component['component'];
	if (is_array($files)) {
		echo '<div style="background-color:red;">There are files of '. $component .' that are not original:';
		echo '<table class="adminlist"><tr><th class="title">Filename</th><th class="title">Exists?</th></tr>';
		foreach($files as $file) {
			echo '<tr><td>'. $file['filename'] .'</td><td>'. ($file['exists'] ? 'yes' : 'no') .'</td></tr>';
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