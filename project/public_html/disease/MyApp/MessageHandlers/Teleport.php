<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Teleport Message Handler
     */	
	
    class Teleport extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "TELEPORT message received\n";
		
		$x = $message[1];
		$y = $message[2];
		
		if ( $player->server->isValidPosition($x, $y) ) {
			$player->setPosition($x, $y);
			$player->clearTarget();
			
			$player->broadcast( new Messages\Teleport($player) );
			
			$player->server->handlePlayerVanish($player);
			$player->server->pushRelevantEntityListTo($player);
		}		
		
		}
	}
?>