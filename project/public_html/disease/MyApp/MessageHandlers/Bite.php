<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Bite Message Handler
     */	
	
    class Bite extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "BITE message received\n";
		
		$mob = $world->server->getEntityById($message[1]);
		$target = $world->server->getEntityById($message[2]);
		
		if ($mob->diseaseState==Types::DiseaseState_INFECTED) {
			//$player->setTarget($mob);
			//$player->server->broadcastAttacker($player);}		
			
			//$infection=Utils::random(5) == 1;
			$infection=true;
									
									$mob->infect();
									
									
				}
		
		}
	}
?>
