<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Aggro Message Handler
     */	
	
    class Aggro extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "AGGRO message received\n";
		
		if ($player->callbacks['Move']) {
			$player->server->handleMobHate($message[1], $player->id, 5);
		}		
		
		}
	}
?>