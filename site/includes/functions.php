<?php 

function returnError($array, $val) {
	if (count($array) != 0) {
		$html = '<ul>';

		foreach ($array as $key => $value) {
			$html .= '<li>'. $value .'</li>';
		}

		$html .= '</ul>';

		if ($val == 0) {
			echo $html;
		} else {
			return $html;
		}
		
	} else {
		return true;
	}
	
}

?>