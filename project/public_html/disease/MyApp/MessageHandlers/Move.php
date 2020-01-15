<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	use MyApp\Formulas;
	use MyApp\HurtDecorator;
	use MyApp\Entity;	
    
    /**
     * The Move Message Handler
     */
    class Move extends MessageHandlerAbstract{
        public function __invoke(ConnectionInterface $from, array $message) {
            // I think a player module is needed that will provide access to the players 
            $player = $this->main->players[$from->resourceId];
            
            echo "MOVE message received\n";
            if ($player->callbacks['Move']) {
                $x = $message[1];
                $y = $message[2];
                
                if ( $player->server->isValidPosition($x, $y) ) {
                    $player->setPosition($x, $y);		
                    $player->clearTarget();        
                    $player->broadcast(new Messages\Move($player) );
                    $player->callbacks['Move']($player->x, $player->y);
				  

                }		
				
            }
        }
    }
    
?>