<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Chat Message Handler
     */	
	
    class Chat extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "CHAT message received\n";
		
		$msg = Utils::sanitize($message[1]); // Note: Sanitise not fully implemented, it just returns its argument.
		if ($msg !== '') { // Don't send empty messages
			$msg = substr($msg, 0, 60); // Enforce maxlength of chat input.
			$player->broadcast(new Messages\Chat($player, $msg), false);
		}		
		
		}
	}
?>