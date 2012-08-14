/*
	SMS-MMS.php
	by James Thoburn http://jthoburn.com
	
	Dual licensed under the MIT or GPL Version 2 licenses.
	
*/

function parseSMS_MMS($message) {
	
	//get individual line content
	$lineContent = explode("\n",$message);
	
	$headers = array();
	$sectionHeaders = array();
	$sections = array();
	$section = 0;
	
	//ignore empty lines at the beginning of the message
	$iterator = 0;
	while($iterator < count($lineContent) && trim($lineContent[$iterator]) == '')
		$iterator++;
	
	
	//get headers
	
	$lastHeader = null;
	
	while($iterator < count($lineContent) && trim($lineContent[$iterator]) != '') {
		$found = preg_match("/^([A-Z][A-Za-z\-]*):\s?(.*)/",$lineContent[$iterator],$matches);
		if($found) {
			if(isset($headers[$matches[1]]))
				$headers[$matches[1]] .= ' '.$matches[2];
			else
				$headers[$matches[1]] = $matches[2];
			$lastHeader = $matches[1];
		}
		elseif($lastHeader != null) {
			$headers[$lastHeader] .= ' '.$lineContent[$iterator];//we have a sub-info section
		}
		$iterator++;
	}
	
	
	//get MIME boundary
	$MIMEboundary = substr( $headers['Content-Type'], ($start = strpos($headers['Content-Type'],'boundary="') + 10), strpos($headers['Content-Type'],'"',$start) - $start );
	
	//get content sections
	while($iterator < count($lineContent) ) {
		
		//detect input end
		if($lineContent[$iterator] == '--'.$MIMEboundary.'--')
			break;
		
		//break the message body up by content type
		if($lineContent[$iterator] == '--'.$MIMEboundary) {
			$section++;
			$sectionHeaders[$section] = array();
			$sections[$section] = '';
			
			
			
			//get section headers
			
			$lastSectionHeader = null;
			
			$iteratorCache = $iterator;
			
			while($iterator < count($lineContent) && trim($lineContent[$iterator]) != '') {
				
				$found = preg_match("/^([A-Z][A-Za-z\-]*):\s?(.*)/",$lineContent[$iterator],$matches);
				
				if( $found ) {
					if(isset($sectionHeaders[$matches[1]]))
						$sectionHeaders[$section][$matches[1]] .= ' '.$matches[2];
					else
						$sectionHeaders[$section][$matches[1]] = $matches[2];
					$lastSectionHeader = $matches[1];
				}
				elseif($lastHeader != null) {
					$sectionHeaders[$section][$lastSectionHeader] .= ' '.$lineContent[$iterator];//we have a sub-info section
				}
				$iterator++;
			}
			
			//backpedal 1 to account for the increment at the end of the outer while loop
			if($iteratorCache != $iterator)
				$iterator--;
			
		}
		else
			$sections[$section] .= $lineContent[$iterator];
		$iterator++;
	}
	
	$return = array();
	$return['headers'] = $headers;
	$return['content'] = array();
	for($i = 0; $i < count($sections); $i++) {
		$return['content'][$i] = array(
			'headers' => $sectionHeaders[$i],
			'data' => $sections[$i]
		);
	}
	return $return;
}