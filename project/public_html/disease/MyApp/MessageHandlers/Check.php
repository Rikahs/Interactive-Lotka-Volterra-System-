<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Check Message Handler
     */	
	
    class Check extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "CHECK message received\n";
		
		$checkpoint = $player->server->map->getCheckpoint($message[1]);
		if($checkpoint) {
			$player->lastCheckpoint = $checkpoint;
			echo "{$player->name} is at checkpoint {$message[1]}\n";
		}
	 else {
		echo "Unknown Message type received ($action).\n";
		if ($player->callbacks['message']) {
			$player->callbacks['message']($message);
		}				
	}		
		}
		}
	
?>