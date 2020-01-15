<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	
    /**
     * The Loot Message Handler
     */	
	
    class Loot extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "LOOT message received\n";
		
		
		$item = $player->server->getEntityById($message[1]);
		
		if ($item) {
			$kind = $item->kind;
			if (Types::isItem($kind)) {
				$player->broadcast($item->despawn());
				$player->server->removeEntity($item);
				
				if ($kind === Types::Entities_FIREPOTION) {
					$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
					$text ="\r\n".(microtime(true)-\Myapp\Main::getStartTime())."\t"." Player $player->name $player->id \t has has recieved a firepotion";
					fwrite($playerFile,$text);
					fclose($playerFile);
					$player->updateHitPoints();
					$player->broadcast($player->equip(Types::Entities_FIREFOX) );
					// self.firepotionTimeout = setTimeout(function() {
						// self.broadcast(self.equip(self.armor)); // return to normal after 15 sec
						// self.firepotionTimeout = null;
					// }, 15000);
					$message = new Messages\HitPoints($player->maxHitPoints);
					$player->connection->send( json_encode( $message->serialize() ) );
				} else if ( Types::isHealingItem($kind) ) {
					$amount;
					switch ($kind) {
						case Types::Entities_FLASK:
							$amount = 40;
							$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
							$text ="\r\n".(microtime(true)-\Myapp\Main::getStartTime())."\t"." Player $player->name $player->id \t has has recieved a flask";
							fwrite($playerFile,$text);
							fclose($playerFile);
							break;
						case Types::Entities_BURGER:
							$amount = 50;
							$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
							$text ="\r\n".(microtime(true)-\Myapp\Main::getStartTime())."\t"." Player $player->name $player->id \t has has recieved a burger";
							fwrite($playerFile,$text);
							fclose($playerFile);
							break;
					}
					
					if ( !$player->hasFullHealth() ) {
						$player->regenHealthBy($amount);
						$player->server->pushToPlayer( $player, $player->health() );
					}
				} else if ( Types::isArmor($kind) || Types::isWeapon($kind) ) {
					$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
					$text ="\r\n".(microtime(true)-\Myapp\Main::getStartTime())."\t"." Player $player->name $player->id \t has has recieved a ".Types::getKindAsString($item->kind);
					fwrite($playerFile,$text);
					fclose($playerFile);
					$player->equipItem($item);
					$player->broadcast(  $player->equip($kind)  );
				}
			}		
		}
		
		}
	}
?>