<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The LootMove Message Handler
     */	
	
    class LootMove extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "LOOTMOVE message received\n";
		
		if ($player->lootmove_callback) {
			$player->setPosition($message[1], $message[2]);
			$item = $player->server->getEntityById($message[3]);
			if ($item) {
				$player->clearTarget();
				$player->broadcast( new Messages\LootMove($player, $item) );
				$player->callbacks['Lootmove']($player->x,$player->y);
			}
		}		
		
		}
	}
?>