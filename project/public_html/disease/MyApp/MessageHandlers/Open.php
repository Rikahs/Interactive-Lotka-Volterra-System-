<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Open Message Handler
     */	
	
    class Open extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "OPEN message received\n";
		
		$chest = $player->server->getEntityById($message[1]);
		if ($chest && $chest instanceof Chest) {
			$player->server->handleOpenedChest($chest, $player);
		}		
		
		
		}
	}
?>