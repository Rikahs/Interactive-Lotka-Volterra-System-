<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	use MyApp\Formulas;
	use MyApp\FormulasDecorator;	
	use MyApp\FormulasDecoratorEditor;
	
    /**
     * The Hit Message Handler
     */	
	
    class Hit extends MessageHandlerAbstract{

		//public function __invoke(ConnectionInterface $from, array $message, $full)
		public function __invoke(ConnectionInterface $from, array $message) 
		{
					
				$player = $this->main->players[$from->resourceId];
					$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
					$text ="\r\n".(microtime(true)-\Myapp\Main::getStartTime())."\t Player $player->name $player->id \t has attacked a mob, player hp:$player->hitPoints";
					fwrite($playerFile,$text);
					fclose($playerFile);
					echo "HIT message received\n";
					$mob = $player->server->getEntityById($message[1]);
								
					if ($mob) 
					{
							
						static $mobsAttacked = array();//holds the ID of disease creature
						if(!in_array($mob->id,$mobsAttacked))
						{
							array_push($mobsAttacked, $mob->id);
						}
							//var_dump($mobsAttacked);					
							//static $count = array('Rats'=>0, 'Snakes'=> 0, 'Bats'=> 0);
									
							$dmg = Formulas::dmg($player->weaponLevel, $mob->armorLevel);
									
							$mobsAttackedIndex = count($mobsAttacked) - 1;	
									
							echo $mobsAttacked[$mobsAttackedIndex]." Is the ID of the mob being attacked \n";					
									
							$playerFile = fopen(".\MyApp\mobhp.csv", "a+");
							$text ="\r\n".("$mob->kind has $mob->hitPoints");
							fwrite($playerFile,$text);
							fclose($playerFile);
							if ($dmg > 0) 
							{
								$mob->receiveDamage($dmg, $player->id);
								$player->server->handleMobHate($mob->id, $player->id, $dmg);
								$player->server->handleHurtEntity($mob, $player, $dmg);
							}
					}	
			//	}
							 //echo $player->hitPoints;
							 //echo $player->maxHitPoints;
							// exit;
				
			
		// I think a player module is needed that will provide access to the players 
			 

					 
		}
	}
?>