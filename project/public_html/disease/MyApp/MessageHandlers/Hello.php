<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    
    /**
     * The Hello Message Handler
     */
    class Hello extends MessageHandlerAbstract{
        // The __invoke method is called whenever you try to use an object of the class as a method.
        // E.g. $helloHandler = new Hello();
        //      $helloHandler($from, $message); // This would call the __invoke function below once supplied with the correct arguments. 
        public function __invoke(ConnectionInterface $from, array $message) {
            echo "HELLO Message Received.\n";
            // I think a player module is needed that will provide access to the players 
            $player = $this->main->players[$from->resourceId];
            
            $player->name = $message[1]; // ToDo: Sanitise name and limit length, not really necessary for our controlled research environment yet.
            $player->kind = Types::Entities_WARRIOR;
            $player->equipArmor($message[2]);
            $player->equipWeapon($message[3]);
            $player->orientation = Utils::randomOrientation();
            $player->updateHitPoints();
            $player->updatePosition();
            
            $player->server->addPlayer($player);
            
            // Here is how the new way to call callbacks would look.
            // The benefit is that it works off of an array which can have new events added to it
            // instead of multiple different variables one having to be defined for each new event desired.
            // I believe most of the callbacks should end up being in side of the message handlers like this
            $player->server->callbacks['PlayerEnter']($player);
            // $player->server->enter_callback($player);
            
            $message = array(Types::Messages_WELCOME, $from->resourceId, $player->name, $player->x, $player->y, $player->hitPoints);
            $from->send( json_encode($message) );
            
            $player->hasEnteredGame = true;
            $player->isDead = false;
        }
    }
    
?>