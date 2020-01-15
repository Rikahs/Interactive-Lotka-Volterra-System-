<?php
$target_dir = "logs/mobs/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
if (($handle = fopen($target_file, "r")) !== FALSE)
{
	$preycount=0;
$predcount=0;
    while (($data = fgetcsv($handle)) !== FALSE) 
    {

      if(!isset($btime))
          $btime=$data[0];

        $time=$data[0]-$btime;
  if(($data[1]=="rat") && ($data[5]=="spawned"))
  {
    $preycount++;
  }


   if(($data[1]=="goblin") && ($data[5]=="spawned"))
  {
    $predcount++;
  }

   if(($data[1]=="rat") && ($data[5]=="killed"))
  {
    $preycount--;
  }
   if(($data[1]=="rat") && ($data[4]=="died"))
  {
    $preycount--;
  }

   if(($data[1]=="goblin") && ($data[4]=="died"))
  {
    $predcount--;
  }

    $playerFile = fopen("mob.csv", "a+");
                        $text ="$time\t$preycount\t  $predcount \n";
                        fwrite($playerFile,$text);
                        fclose($playerFile);
	}
     fclose($handle);
}