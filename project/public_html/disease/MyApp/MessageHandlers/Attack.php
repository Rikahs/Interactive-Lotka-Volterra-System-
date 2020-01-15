<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Attack Message Handler
     */	
	
    class Attack extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "ATTACK message received\n";
		
		$mob = $player->server->getEntityById($message[1]);
		
		if ($mob) {
			$player->setTarget($mob);
			$player->server->broadcastAttacker($player);}		
		
		}
	}
?>