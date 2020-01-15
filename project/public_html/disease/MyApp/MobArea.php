<?php
	namespace MyApp;

	class MobArea extends Area {
		protected $nb;
		protected $kind;
		protected $respawns;
		protected $config;
		
		public function __construct($id, $nb, $kind, $x, $y, $width, $height, $world) {
			parent::__construct($id, $x, $y, $width, $height, $world);
			$this->nb = $nb;
			$this->kind = $kind;
			$this->respawns = array();
			$this->setNumberOfEntities($this->nb);
			
		}
		
		public function spawnMobs() {
			for ($i = 0; $i < $this->nb; $i++) {
				$this->addToArea( $this->_createMobInsideArea() );
			}
		}
		
		public function _createMobInsideArea() {
			$k = Types::getKindFromString($this->kind);
			$pos = $this->_getRandomPositionInsideArea();
			$mob = new Mob("1{$this->id}$k" . count($this->entities), $k, $pos->x, $pos->y,$this->config);
			

			$mobFile = fopen(".\MyApp\MOBLOG.csv", "a+");
			$text ="\r\n".(microtime(true)-Main::getStartTime())."\t".Types::getKindAsString($mob->kind)." $mob->id has been spawned at point: $mob->x $mob->y";
			fwrite($mobFile,$text);
			fclose($mobFile);
			
			$mob->onMove( $this->world->onMobMoveCallback($mob) );
			// $mob->onMove( $this->world->onMobMoveCallback->bind($this->world) ); // Need to figure out about bind and why there is no mob
			
			return $mob;
		}
		
		public function respawnMob($mob, $delay) {
			$self = $this;
			
			$this->removeFromArea($mob);
			
			// setTimeout(function() use ($self, $mob) {
				// $pos = $self._getRandomPositionInsideArea();
				
				// $mob->x = $pos->x;
				// $mob->y = $pos->y;
				// $mob->isDead = false;
				// $self->addToArea($mob);
				// $self->world->addMob($mob);
			// }, $delay);
		}
		
		public function initRoaming($mob) {
			// echo "ToDo: MobArea::initRoaming\n";
			echo "Simulated in WorldServer::mobAreaRoam, Called in Main::onMessage";
		}
		
		public function createReward() {
			echo "ToDo: MobArea::createReward\n";
		}
	}
?>
