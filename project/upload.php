<?php
 
$target_dir = "logs/exp part 1/original/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
if (($handle = fopen($target_file, "r")) !== FALSE)
{
	$count=0;
	$names=array();
    while (($data = fgetcsv($handle)) !== FALSE) 
    {
    	if(!in_array($data[2], $names))
    	{
    		$names[]=$data[2];
    		$count++;
    	}
	}
     fclose($handle);
}
echo " $count players<br/>"; 
// number of kills
/*	foreach ($names as $key => $value) 
		{ 
			$count=0;
   			if (($handle = fopen($target_file, "r")) !== FALSE)
			{
	   			 while (($data = fgetcsv($handle)) !== FALSE) 
	   			{
					if($value==$data[2] && $data[5]=="killed")
					{
						$count++;
					//	echo "$value killed a $data[6] <br/>";
					}
				}
				fclose($handle);
				$playerFile = fopen("kills.csv", "a+");
						$text ="$value\t  $count \n";
						fwrite($playerFile,$text);
						fclose($playerFile);
				echo " $value killed $count monsters<br/>";
			}
		}
// number of items
	foreach ($names as $key => $value) 
		{ 
			$count=0;
   			if (($handle = fopen($target_file, "r")) !== FALSE)
			{
	   			 while (($data = fgetcsv($handle)) !== FALSE) 
	   			{
					if($value==$data[2] && $data[6]=="recieved")
					{
						$count++;
					//	echo "$value killed a $data[6] <br/>";
					}
				}
				fclose($handle);
				$playerFile = fopen("items.csv", "a+");
						$text ="$value\t  $count \n";
						fwrite($playerFile,$text);
						fclose($playerFile);
				echo " $value has picked up $count items<br/>";
			}
		}


// time spend playing
		foreach ($names as $key => $value) 
		{ 
			$count=0;
   			if (($handle = fopen($target_file, "r")) !== FALSE)
			{
	   			 while (($data = fgetcsv($handle)) !== FALSE) 
	   			{
					if($value==$data[2] )
					{
						$time[]=$data[0];
					//	echo "$value killed a $data[6] <br/>";
					}
				}
				fclose($handle);
				$thetime=date("i:s",$time[sizeof($time)-1]-$time[0]);
				$playerFile = fopen("playtime.csv", "a+");
						$text ="$value\t $thetime \n";
						fwrite($playerFile,$text);
						fclose($playerFile);
				echo " $value has played for $thetime <br/>";
			}
		}*/
		//player moves
		if (($handle = fopen($target_file, "r")) !== FALSE)
			{
	   			 while (($data = fgetcsv($handle)) !== FALSE) 
	   			{
	   				if(!isset($btime))
					$btime=$data[0];
					if($data[0]>1445971103)
						$btime=1445971104;
						if($data[6]=="point:")
						{
						$posx= $data[7];
						$posy= $data[8];
						$time=$data[0]-$btime;echo "$data[0] - $btime = $time<br>";
						$playerFile = fopen("moves.csv", "a+");
						$text ="$posx\t$posy\t$time\n";
						fwrite($playerFile,$text);
						fclose($playerFile);
						}
						if($data[9]=="point:")
						{
						$posx=$data[10] ;
						$posy= $data[11];
						$time=$data[0]-$btime;
						$playerFile = fopen("moves.csv", "a+");
						$text ="$posx\t$posy\t$time\n";
						fwrite($playerFile,$text);
						fclose($playerFile);
						}	
						

				}
				fclose($handle);

			}
		
/*
		// number of deaths
	foreach ($names as $key => $value) 
		{ 
			$count=0;
   			if (($handle = fopen($target_file, "r")) !== FALSE)
			{
	   			 while (($data = fgetcsv($handle)) !== FALSE) 
	   			{
					if($value==$data[2] && $data[5]=="left")
					{
						$count++;
					//	echo "$value killed a $data[6] <br/>";
					}
				}
				fclose($handle);
				$playerFile = fopen("deaths.csv", "a+");
						$text ="$value\t$count\n";
						fwrite($playerFile,$text);
						fclose($playerFile);
				echo " $value died $count times<br/>";
			}
		}*/