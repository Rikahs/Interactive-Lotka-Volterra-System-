<?php
 
$target_dir = "logs/exp part 1/original/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
if (($handle = fopen($target_file, "r")) !== FALSE)
{
	$count=0;
	$names=array();
    while (($data = fgetcsv($handle)) !== FALSE) 
    {

    		$names[]=$data[0];
    		$count++;
    	
	}
     fclose($handle);
}

    if (($handle = fopen($target_file, "r")) !== FALSE)
        {
             while (($data = fgetcsv($handle)) !== FALSE) 
            {
               $apoints=0;
$fpoints=0;
 $ppoints=0;
  $ipoints=0;
                  
                   
                        for($i=1; $i<=25;$i++)
                        {
                            
                            if($data[$i]=="Yes")
                            switch ($i%5)
                            {
                                case 1:
                                $apoints=$apoints+1;
                                print_r($i%5);
                                break;

                                case 2:
                                $apoints=$apoints+2;
                                print_r($i%5);
                                break;

                                case 3:
                                $apoints=$apoints+3;
                                print_r($i%5);
                                break;

                                case 4:
                                $apoints=$apoints+4;
                                print_r($i%5);
                                break;
                                      
                                case 0:
                                $apoints=$apoints+5;
                                print_r($i%5);
                                break;                        
                                
                            }                    
                        }

                        
                        for($i=26; $i<=70;$i++)
                        {
                            if($data[$i]=="Yes")
                            switch ($i%5)
                            {
                                case 1:
                                $fpoints=$fpoints+1;
                                break;

                                case 2:
                                $fpoints=$fpoints+2;
                                break;

                                case 3:
                                $fpoints=$fpoints+3;
                                break;

                                case 4:
                                $fpoints=$fpoints+4;
                                break;
                                      
                                case 0:
                                $fpoints=$fpoints+5;
                                break;                        
                                
                            }                    
                        }
                       
                        for($i=71; $i<=90;$i++)
                        {
                            if($data[$i]=="Yes")
                            switch ($i%5)
                            {
                                case 1:
                                $ppoints=$ppoints+1;
                                break;

                                case 2:
                                $ppoints=$ppoints+2;
                                break;

                                case 3:
                                $ppoints=$ppoints+3;
                                break;

                                case 4:
                                $ppoints=$ppoints+4;
                                break;
                                      
                                case 0:
                                $ppoints=$ppoints+5;
                                break;                        
                                
                            }                    
                        }
                       
                        for($i=91; $i<=95;$i++)
                        {
                            if($data[$i]=="Yes")
                            switch ($i%5)
                            {
                                case 1:
                                $ipoints=$ipoints+1;
                                break;

                                case 2:
                                $ipoints=$ipoints+2;
                                break;

                                case 3:
                                $ipoints=$ipoints+3;
                                break;

                                case 4:
                                $ipoints=$ipoints+4;
                                break;
                                      
                                case 0:
                                $ipoints=$ipoints+5;
                                break;                        
                                
                            }                    
                        }

                        $playerFile = fopen("points.csv", "a+");
                        $text ="\n$data[0]\t $apoints \t $fpoints\t $ppoints\t $ipoints";
                        fwrite($playerFile,$text);
                        fclose($playerFile);  
                    
                }
                fclose($handle);
            }
        