<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    use MyApp\Types;
    use MyApp\Utils;
    use MyApp\Messages;
	use MyApp\Formulas;	
	
    /**
     * The Hurt Message Handler
     */	
	
    class Hurt extends MessageHandlerAbstract{		
		public function __invoke(ConnectionInterface $from, array $message) {
		// I think a player module is needed that will provide access to the players 
		$player = $this->main->players[$from->resourceId];
		
		echo "HURT message received\n";
		$mob = $player->server->getEntityById($message[1]);		
		if ($mob && $player->hitPoints > 0) {
		//	echo $player->hitPoints;

			$player->hitPoints -= Formulas::dmg($mob->weaponLevel, $player->armorLevel);
			$player->server->handleHurtEntity($player);
		//	echo $player->hitPoints;
		//	exit;
			if ($player->hitPoints <= 0) {
				$player->isDead = true;
				
				// Disable the firePotion if active, contradictory because the firepotion makes you invincible.
				// if(self.firepotionTimeout) {
					// clearTimeout(self.firepotionTimeout);
				// }
			}
		}		
		}
	}
?>