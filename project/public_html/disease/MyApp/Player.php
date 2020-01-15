<?php
	namespace MyApp;
	use MyApp\Character;
	
	class Player extends Character{
		public $server;
		public $connection;
		
		protected $haters;
		public $lastCheckpoint;
		protected $disconnectTimeout;
        
        protected $callbacks;
		
		protected $exit_callback;
		public $move_callback;
		public $lootmove_callback;
		protected $zone_callback;
		protected $orient_callback;
		protected $message_callback;
		protected $broadcast_callback;
		protected $broadcastzone_callback;
		protected $requestpos_callback;
		
		public $name;
		public $orientation;
		public $hitPoints;
		public $hasEnteredGame;
		public $isDead;
		
		protected $armor;
		protected $weapon;
		
		public $configuration;
		public $updateCount;
		public $full;
		public function __construct($configuration,$connection, $worldServer) {
			$this->server = $worldServer;
		    $this->connection = $connection;
			
			parent::__construct($this->connection->resourceId, 'player', Types::Entities_WARRIOR, 0, 0, '');
			
			$this->configuration=$configuration;
            $this->callbacks = array();
		    $this->hasEnteredGame = false;
		    $this->isDead = false;
		    $this->haters = array();
		    $this->lastCheckpoint = null;
		    $this->disconnectTimeout = null;
			$this->full=true;
			$this->connection->send('go');
			$this->updateCount=0;

		}
		
		
		public function destroy() {}
		
		public function getState() {
			$basestate = $this->_getBaseState();
			$state = array($this->name, $this->orientation, $this->armor, $this->weapon);
			if ($this->target) {
				array_push($state, $this->target);
			}
			return array_merge($basestate, $state);
		}
		
		public function send($message) {}
		
	public function broadcast($message, $ignoreSelf = true) {
			if ($this->callbacks['Broadcast']) {
				$this->callbacks['Broadcast']($message, $ignoreSelf);
			}
		}
		
		public function broadcastToZone ($message, $ignoreSelf) {}
		
        // These are more callback setters that can be removed once the new on() method is utilised for them.
		public function onExit($callback) {
			$this->exit_callback = $callback;
		}
		
		public function onMove($callback) {
		    $this->callbacks['Move'] = $callback;

		}
		
		public function onLootMove($callback) {
			$this->lootmove_callback = $callback;
		}
		
		public function onZone($callback) {
			$this->zone_callback = $callback;
		}
		
		public function onOrient($callback) {
			$this->orient_callback = $callback;
		}
		
		public function onMessage($callback) {
			$this->message_callback = $callback;
		}
		
		public function onBroadcast($callback) {
			$this->broadcast_callback = $callback;
		}
		
		public function onBroadcastToZone($callback) {
			$this->broadcastzone_callback = $callback;
		}
		
		public function equip($item) {
			return new Messages\EquipItem($this, $item);
		}
		
		public function addHater($mob) {
			if ($mob) {
				if (!array_key_exists($mob->id, $this->haters) ) {
					$this->haters[$mob->id] = $mob;
				}
			}
		}
		
		public function removeHater($mob) {
			if ($mob && array_key_exists($mob->id, $this->haters) ) {
				unset($this->haters[$mob->id]);
			}
		}
		
		public function forEachHater($callback) {
			foreach ($this->haters as &$mob) {
				$callback($mob);
			}
			unset($mob);
		}
		
		public function equipArmor($kind) {
			$this->armor = $kind;
			$this->armorLevel = Properties::getArmorLevel($kind);
		}
		
		public function equipWeapon($kind) {
			$this->weapon = $kind;
			$this->weaponLevel = Properties::getWeaponLevel($kind);
		}
		
		public function equipItem($item) {
			if ($item) {
				echo $this->name . ' equips ' . Types::getKindAsString($item->kind) . "\n";
				if ( Types::isArmor($item->kind) ) {
					$this->equipArmor($item->kind);
					$this->updateHitPoints1();
					$message = new Messages\HitPoints($this->maxHitPoints);
					$this->connection->send( $message->serialize() );
				} else if ( Types::isWeapon($item->kind) ) {
					$this->equipWeapon($item->kind);
				}
			}
		}
		public function updateHitPoints1() {
			$this->resetHitPoints1( Formulas::hp($this->armorLevel) );
		}

		public function updateHitPoints() {
			$this->resetHitPoints( Formulas::hp($this->armorLevel) );
		}
		
		public function updatePosition() {
			if ($this->requestpos_callback) {
				$pos = $this->requestpos_callback();
				$this->setPosition($pos->x, $pos->y);
			}
		}
		
		public function onRequestPosition($callback) {
			$this->requestpos_callback = $callback;
		}
		
		public function resetTimeout() {}
		public function timeout() {}
        
        // The general purpose callback assigner
        // instead of calling onSomeEvent() and having to define a function for each event just pass the event name as the first parameter.
        // The benefits of this is that if new events need to be defined later, the same on() method and callbacks array can be reused
        // instead of having to modify WorldServer to create a new onSomeEvent() method with its matching someEvent_callback storage variable.
        public function on($event, $callback) {
            $this->callbacks[$event] = $callback;
        }
		
		// needed to get callbacks to work from within an object
		public function __call($function, $arguments) {
			$callbacks = array('exit_callback', 'move_callback', 'lootmove_callback', 'zone_callback', 'orient_callback', 'message_callback', 'broadcast_callback', 'broadcastzone_callback', 'requestpos_callback');
			if ( in_array($function, $callbacks) ) {
				return call_user_func_array($this->$function, $arguments);
			}			
		}
        
        // This should allow the callbacks to be accessible from outside of this class.
        public function __get($variableName) {
            switch ($variableName) {
                case 'callbacks': return $this->callbacks;
            }
        }
		
	}
?>
