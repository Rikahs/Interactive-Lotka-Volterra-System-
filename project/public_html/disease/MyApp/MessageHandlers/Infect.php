<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Infect Message Handler
     */	
	
    class Infect extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		
		
		$mob = $world->server->getEntityById($message[1]);
		$target = $world->server->getEntityById($message[2]);
		
		
			//$player->setTarget($mob);
			//$player->server->broadcastAttacker($player);}		
			echo "INFECT message received\n";
			//
			foreach($mob->disease as $d)
			{
				array_push($target->diseases,$d);
				$target->diseasesContracted++;
			}
			//depends on which model is used SIR or SEIR
			$target->diseaseState=Types::DiseaseState_INFECTED;
									
		
		}
	}
?>
