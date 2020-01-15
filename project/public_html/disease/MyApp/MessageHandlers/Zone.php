<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Zone Message Handler
     */	
	
    class Zone extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		$player->callbacks['Zone']();
		
		echo "ZONE message received\n";
		}
	}
?>