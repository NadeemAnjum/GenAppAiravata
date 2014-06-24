<?php

$dat = file_get_contents("directives.json");
$dat = substr($dat, (strpos($dat, 'file')+4));
$dat = json_decode($dat, $depth = 5); 
$exec_path = $dat['executable_path']['html5'];
//echo $exec_path;
$json = file_get_contents("menu.json");
$json=substr($json, (strpos($json, 'file')+4));
$results = json_decode($json, $depth = 5);           //depth value could be the level you want to reach
$args="";
for($i=0;$i<sizeof($results['menu']);$i++){	
	for($j=0;$j<sizeof($results['menu'][$i]['modules']);$j++){	
		$app = $results['menu'][$i]['modules'][$j]['id'];		
		$args = $args.$app." $exec_path"."/".$app." /tmp ";	
	}	
}
$args=trim($args);
echo "java -jar registerGenAppModule.jar $args";
exec("java -jar registerGenAppModule.jar $args");
?>
