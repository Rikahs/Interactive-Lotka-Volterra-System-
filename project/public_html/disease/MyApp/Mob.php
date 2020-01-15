<?php
	namespace MyApp;
	
	class Mob extends Character {
		protected $spawningX;
		protected $spawningY;
		public $armorLevel;
		public $weaponLevel;
		public $hatelist;
		protected $respawnTimeout;
		protected $returnTimeout;
		public $isDead;
		public $deathage;
		public $preybleedcount;
		protected $move_callback;
		protected $respawn_callback;
		protected $bite_callback;
		protected $infect_callback;
		public $deathtime;
		
		
		
		public function __construct($id, $kind, $x, $y,$config) {

			parent::__construct($id, 'mob', $kind, $x, $y);
			$this->updateHitPoints();
			$this->spawningX = $x;
			$this->spawningY = $y;
			$this->armorLevel = Properties::getArmorLevel($this->kind);
			$this->weaponLevel = Properties::getWeaponLevel($this->kind);
			$this->hatelist = array();
			$this->respawnTimeout = null;
			$this->returnTimeout = null;
			$this->isDead = false;
			$this->deathage=0;
			$this->full=false;
			$this->preybleedcount=0;

			switch ($kind) {
				case 'rat':
					$this->deathtime=$config->ratdeathage;
					break;
					case 'bat':
					$this->deathtime=$config->batdeathage;
					break;
					case 'goblin':
					$this->deathtime=$config->goblindeathage;
					break;
					case 'crab':
					$this->deathtime=$config->crabdeathage;
					break;
					case 'skeleton':
					$this->deathtime=$config->skeletondeathage;
					break;
				
				default:
					# code...
					break;

					
			
			}
		}
		
		public function destroy() {
			$this->isDead = true;
			$this->hatelist = array();
			$this->clearTarget();
			$this->updateHitPoints();
			$this->resetPosition();
			$this->handleRespawn();
		}
		
		public function receiveDamage($points, $playerId) {
			$this->hitPoints -= $points;
		}
		
		public function hates($playerId) {
			foreach ($this->hatelist as $obj) {
				if ($obj->id === $playerId) {
					return true;
				}
			}
			return false;
		}
		
		public function increaseHateFor($playerId, $points) {
			if ( $this->hates($playerId) ) {
				foreach ($this->hatelist as &$obj) {
					if ($obj->id === $playerId) {
						$obj->hate += $points;
						break;
					}
				}
				unset($obj);
			} else {
				$hated = new \StdClass();
				$hated->id = $playerId;
				$hated->hate = $points;
				array_push($this->hatelist, $hated); 
			}
			
			// if(this.returnTimeout) {
				// Prevent the mob from returning to its spawning position
				// since it has aggroed a new player
				// clearTimeout(this.returnTimeout);
				// this.returnTimeout = null;
			// }
		}
		
		public function getHatedPlayerId($hateRank) {
			$i;
			$playerId;
            $sorted = $this->hatelist;
			
			usort($sorted, function($a, $b) { 
				if ($a->hate == $b->hate) return 0;
				return ($a->hate < $b->hate) ? -1 : 1;
			});
            $size = count($this->hatelist);
        
			if($hateRank && $hateRank <= $size) {
				$i = $size - $hateRank;
			} else {
				$i = $size - 1;
			}
			if($sorted && $sorted[$i]) {
				$playerId = $sorted[$i]->id;
			}
			
			return $playerId;
		}
		
		public function forgetPlayer($playerId, $duration) {
			foreach ($this->hatelist as $id=>&$obj) {
				if ($obj->id === $playerId) {
					unset($this->hatelist[$id]);
				}
			}
			unset($obj);
			
			if (count($this->hatelist) === 0) {
				$this->returnToSpawningPosition($duration);
			}
		}
		
		public function forgetEveryone() {
			$this->hatelist = array();
			$this->returnToSpawningPosition(1);
		}
		
		public function drop($item) {
			if ($item) {
				return new Messages\Drop($this, $item);
			}
		}
		
		public function handleRespawn() {
			$delay = 30000;
			$self = $this;
			
			if($this->area && $this->area instanceof MobArea) {
				// Respawn inside the area if part of a MobArea
				$this->area->respawnMob($this, $delay);
			} else {
				if($this->area && $this->area instanceof ChestArea) {
					$this->area->removeFromArea($this);
				}
				
				// setTimeout(function() {
					// if(self.respawn_callback) {
						// self.respawn_callback();
					// }
				// }, delay);
			}
		}
		
		public function onRespawn($callback) {
			$this->respawn_callback = $callback;
		}

		public function resetPosition() {
			$this->setPosition($this->spawningX, $this->spawningY);
		}
		
		public function returnToSpawningPosition($waitDuration = 4000) {
			$self = $this;
			$delay = $waitDuration;
			
			$this->clearTarget();
			
			// this.returnTimeout = setTimeout(function() {
				// self.resetPosition();
				// self.move(self.x, self.y);
			// }, delay);
		}
		
		public function onMove($callback) {
			$this->move_callback = $callback;
		}
		
		public function onInfect($callback) {
			$this->infect_callback = $callback;
		}
		public function onBite($callback) {
			$this->bite_callback = $callback;
		}
		
		public function move($x, $y) {
			$this->setPosition($x, $y);
			if ($this->move_callback) {
				$this->move_callback($this);
			}
		}

		public function updateHitPoints() {
			$this->resetHitPoints(Properties::getHitPoints($this->kind));
		}
		
		public function loseHealth(){
			$this->hitpoints=0;
		}
		
		public function distanceToSpawningPoint($x, $y) {
			return Utils::distanceTo($x, $y, $this->spawningX, $this->spawningY);
		}
		
		// needed to get callbacks to work from within an object
		public function __call($function, $arguments) {
			$callbacks = array('move_callback','respawn_callback','bite_callback','infect_callback');
			if ( in_array($function, $callbacks) ) {
				call_user_func_array($this->$function, $arguments);
			}			
		}
	}
?>
