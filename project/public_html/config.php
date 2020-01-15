<?php
$settings=array(

    "port"=> 8081,
    "debugLevel"=> "info",
    "numberOfPlayersPerWorld"=> 4,
    "numberOfWorlds"=> 1,
    "mapFilepath"=> "./world_server.json",
    "metrics_enabled"=> false,
	"updateIndicator"=> true,
	);
$set=array_merge($settings,$_POST);



$fh = fopen("settings.json", 'w')
      or die("Error opening output file");
fwrite($fh, json_encode($set,JSON_UNESCAPED_UNICODE));
fclose($fh);


?>
