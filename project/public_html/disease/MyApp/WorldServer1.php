<?php
	namespace MyApp;
	use MyApp\Map;
	use MyApp\Player;
	use MyApp\Messages;
	
	class WorldServer {
		protected $id;
		protected $maxPlayers;
		protected $server;
		
		protected $updatesPerSecond;
		public $map;
		
		protected $entities;
        protected $players;
        protected $mobs;
        protected $attackers;
        protected $items;
        protected $equipping;
        protected $hurt;
        protected $npcs;
        protected $mobAreas;
        protected $chestAreas;
        protected $groups;
		
		protected $outgoingQueues;
        
        protected $itemCount;
        public $playerCount;
		public $updateCount;
		public $regenCount;
		
		protected $zoneGroupsReady;
		
		protected $init_callback;
		protected $connect_callback;
		protected $enter_callback;
		protected $added_callback;
		protected $removed_callback;
		public $regen_callback;
		protected $attack_callback;
		
		public function __construct($id, $maxPlayers, $websocketServer) {
			echo "Creating World\n";
			$self = $this;
			
			$this->id = $id;
			$this->maxPlayers = $maxPlayers;
			$this->server = $websocketServer;
			$this->updatesPerSecond = 50;
			
			$this->map = null;
			
			$this->entities = null;
			$this->players = null;
			$this->mobs = null;
			$this->attackers = null;
			$this->items = null;
			$this->equipping = null;
			$this->hurt = null;
			$this->npcs = array();
			$this->mobAreas = array();
			$this->chestAreas = array();
			$this->groups = array();
			
			$this->outgoingQueues = array();
			
			$this->itemCount = 0;
			$this->playerCount = 0;
			
			$this->zoneGroupsReady = false;
			
			$this->onPlayerConnect( function($player) use ($self) {
				$player->onRequestPosition(function() use ($self, $player) {
					if($player->lastCheckpoint) {
						return $player->lastCheckpoint->getRandomPosition();
					} else {
						$pos = $self->map->getRandomStartingPosition();
						return $pos;
					}
				});
			});
								
			$this->onPlayerEnter( function($player) use ($self) {
				echo $player->name . ' has joined ' . $self->id . "\n";
				
				if(!$player->hasEnteredGame) {
					$self->incrementPlayerCount();
				}
				
				// Number of players in this world
				$self->pushToPlayer($player, new Messages\Population($self->playerCount, $self->playerCount));
				$self->pushRelevantEntityListTo($player);
		
				$move_callback = function($x, $y) use ($self, $player) {
					echo "{$player->name} is moving to ($x,$y).\n";
					
					$player->forEachAttacker( function($mob) use ($self, $player) {
						$target = $self->getEntityById($mob->target);
						if ($target) {
							$pos = $self->findPositionNextTo($mob, $target);
							if ($mob->distanceToSpawningPoint($pos->x, $pos->y) > 50) {
								$mob->clearTarget();
								$mob->forgetEveryone();
								$player->removeAttacker($mob);
							} else {
								$self->moveEntity($mob, $pos->x, $pos->y);
							}
						}
					});
				};

				$player->onMove($move_callback);
				$player->onLootMove($move_callback);
				
				$player->onZone( function() use ($self, $player) {
					$hasChangedGroups = $self->handleEntityGroupMembership($player);
					
					if ($hasChangedGroups) {
						$self->pushToPreviousGroups($player, new Messages\Destroy($player));
						$self->pushRelevantEntityListTo($player);
					}
				});

				$player->onBroadcast( function($message, $ignoreSelf) use ($self, $player) {
					$self->pushToAdjacentGroups($player->group, $message, $ignoreSelf ? $player->id : null);
				});
				
				$player->onBroadcastToZone( function($message, $ignoreSelf) use ($self, $player) {
					$self->pushToGroup($player->group, $message, $ignoreSelf ? $player->id : null);
				});
		
				$player->onExit(function() use ($self, $player) {
					echo "{$player->name} has left the game.\n";
					$self->removePlayer($player);
					$self->decrementPlayerCount();
					
					if ($self->removed_callback) {
						$self->removed_callback();
					}
				});
				
				if ($self->added_callback) {
					$self->added_callback();
				}
			});
								
			// Called when an entity is attacked by another entity
			$this->onEntityAttack( function($attacker) use ($self) {
				$target = $self->getEntityById($attacker->target);
				if ($target && $attacker->type === "mob") {
					$pos = $self->findPositionNextTo($attacker, $target);
					$self->moveEntity($attacker, $pos->x, $pos->y);
				}
			});
								
			$this->onRegenTick( function() use ($self) {
				$self->forEachCharacter( function($character) use ($self) {
					if( !$character->hasFullHealth() ) {
						$character->regenHealthBy( floor($character->maxHitPoints / 25));
				
						if( $character->type === 'player') {
							$self->pushToPlayer( $character, $character->regen() );
						}
					}
				});
			});
			
			echo "World Created.\n";
		}
		
		public function run($mapFilePath) {
			$self = $this;
			
			$this->map = new Map();
			
			$this->map->ready( function() use ($self) {
				$self->initZoneGroups();
				
				// $self->map->generateCollisionGrid(); // CPU Intensive BLOCKING task in PHP!!!!! Takes about 30 seconds.
				
				// Populate all mob "roaming" areas
				foreach ($self->map->mobAreas as $mobArea) {
					$area = new MobArea($mobArea->id, $mobArea->nb, $mobArea->type, $mobArea->x, $mobArea->y, $mobArea->width, $mobArea->height, $self);
					$area->spawnMobs();
					$area->onEmpty( $self->handleEmptyMobArea($area) ); // $area->onEmpty( $self->handleEmptyMobArea->bind($self, $area) ); //Another Bind
					
					array_push($self->mobAreas, $area);
				}
				
				// Create all chest areas
				foreach ($self->map->chestAreas as $chestArea) {
					// $area = new ChestArea($chestArea->id, $chestArea->x, $chestArea->y, $chestArea->w, $chestArea->h, $chestArea->tx, $chestArea->ty, $chestArea->i, $self); // Probably another bug, the JSON map data does not have an Id for the chestAreas.
					$area = new ChestArea(0, $chestArea->x, $chestArea->y, $chestArea->w, $chestArea->h, $chestArea->tx, $chestArea->ty, $chestArea->i, $self);
					array_push($self->chestAreas, $area);
					$area->onEmpty( $self->handleEmptyChestArea($area) );
					// $area->onEmpty( $self->handleEmptyChestArea->bind($self, $area) ); // Binds again
				}
				
				// Spawn static chests
				foreach ($self->map->staticChests as $chest) {
					$c = $self->createChest($chest->x, $chest->y, $chest->i);
					$self->addStaticItem($c);
				}
				
				// Spawn static entities
				$self->spawnStaticEntities();
				
				// Set maximum number of entities contained in each chest area
				foreach ($self->chestAreas as $area) {
					$area->setNumberOfEntities(count($area->entities));
				}
			});
			// This has to come after because PHP does not have asynchronous file loading.
			$json = json_decode( file_get_contents($mapFilePath) );
			$this->map->initMap($json);
			
			$this->regenCount = $this->updatesPerSecond * 2;
			$this->updateCount = 0;
			
			// Not sure how to implement setInterval in PHP which would basically be to execute world-generated events and push along messages.
			/* setInterval(function() {
				self.processGroups();
				self.processQueues();
				
				if(updateCount < regenCount) {
					updateCount += 1;
				} else {
					if(self.regen_callback) {
						self.regen_callback();
					}
					updateCount = 0;
				}
			}, 1000 / this.updatesPerSecond); */
			
			
			echo "{$this->id} created (Capacity: {$this->maxPlayers} players)\n";
		}
		
		public function setUpdatesPerSecond($updatesPerSecond) {
			$this->updatesPerSecond = $updatesPerSecond;
		}
		
		public function onInit($callback) {
			$this->init_callback = $callback;
		}
		
		public function onPlayerConnect($callback) {
			$this->connect_callback = $callback;
		}
		
		public function onPlayerEnter($callback) {
			$this->enter_callback = $callback;
		}
		
		public function onPlayerAdded($callback) {
			$this->added_callback = $callback;
		}
		
		public function onPlayerRemoved($callback) {
			$this->removed_callback = $callback;
		}
		
		public function onRegenTick($callback) {
			$this->regen_callback = $callback;
		}
		
		public function pushRelevantEntityListTo($player) {
			if ( $player && array_key_exists($player->group, $this->groups) ) {
			
				$entities = array_keys($this->groups[$player->group]->entities);
				for ($i = 0; $i < count($entities); $i++) {
					if ($entities[$i] == $player->id) {
						unset( $entities[$i] );
					}
				}
				
				for ($i = 0; $i < count($entities); $i++) {
					$entities[$i] = intval($entities[$i]);
				}
				
				if ($entities) {
					$this->pushToPlayer( $player, new Messages\EntityList($entities) );
				}
			}
		}
		
		public function pushSpawnsToPlayer($player, $ids) {
			foreach($ids as $id) {
				$entity = $this->getEntityById($id);
				if ($entity) {
					$this->pushToPlayer( $player, new Messages\Spawn($entity) );
				}
			}
			
			echo 'Pushed ' . count($ids) . ' new spawn' . (count($ids) == 1 ? '' : 's') . ' to ' . $player->id . "\n";
		}
		
		public function pushToPlayer($player, $message) {
			if ( $player && array_key_exists($player->id, $this->outgoingQueues) ) {
				array_push( $this->outgoingQueues[$player->id], $message->serialize() );
			} else {
				echo "pushToPlayer: player was undefined\n";
			}
		}
		
		public function pushToGroup($groupId, $message, $ignoredPlayer = null) {
			if ( isset($this->groups[$groupId]) ) {
				$group = $this->groups[$groupId];
				foreach ($group->players as $playerId) {
					if ($playerId != $ignoredPlayer) {
						$this->pushToPlayer($this->getEntityById($playerId), $message);
					}
				}
			} else {
				echo "groupId: $groupId is not a valid group\n";
			}
		}
		
		public function pushToAdjacentGroups($groupId, $message, $ignoredPlayer = null) {
			$self = $this;
			$this->map->forEachAdjacentGroup($groupId, function($id) use ($self, $message, $ignoredPlayer) {
				$self->pushToGroup($id, $message, $ignoredPlayer);
			});
		}
		
		public function pushToPreviousGroups($player, $message) {
			foreach ($player->recentlyLeftGroups as $id) {
				$this->pushToGroup($id, $message);
			}
			$player->recentlyLeftGroups = array();
		}
		
		public function pushBroadcast($message, $ignoredPlayer) {
			foreach ($this->outgoingQueues as $id => &$queue) {
				if ($id != $ignoredPlayer) {
					$queue[] = $message->serialize();
				}
			}
			unset($queue);
		}
		
		public function processQueues() {
			foreach ($this->outgoingQueues as $id => &$queue) {
				if (count($queue) > 0) {
					$connection = $this->server->players[$id]->connection;
					$connection->send(json_encode($queue));
					$queue = array();
				}
			}
			unset($queue);
		}
		
		public function addEntity($entity) {
			$this->entities[$entity->id] = $entity;
			$this->handleEntityGroupMembership($entity);
		}
		
		public function removeEntity($entity) {
			if ( array_key_exists($entity->id, $this->entities) ) {
				unset($this->entities[$entity->id]);
			}
			if ( array_key_exists($entity->id, $this->mobs) ) {
				unset($this->mobs[$entity->id]);
			}
			if ( array_key_exists($entity->id, $this->items) ) {
				unset($this->items[$entity->id]);
			}
			
			if ($entity->type === 'mob') {
				$this->clearMobAggroLink($entity);
				$this->clearMobHateLinks($entity);
			}
			
			$entity->destroy();
			$this->removeFromGroups($entity);
			echo 'Removed ' . Types::getKindAsString($entity->kind) . " : {$entity->id}\n";
		}
		
		public function addPlayer($player) {
			$this->addEntity($player);
			$this->players[$player->id] = $player;
			$this->outgoingQueues[$player->id] = array();
			echo "Added Player: #{$player->id}.\n";
		}
		
		public function removePlayer($player) {
			$player->broadcast( $player->despawn() );
			$this->removeEntity($player);
			unset($this->players[$player->id]);
			unset($this->outgoingQueues[$player->id]);
		}
		
		public function addMob($mob) {
			$this->addEntity($mob);
			$this->mobs[$mob->id] = $mob;
		}
		
		public function addNPC($kind, $x, $y) {
			$npc = new NPC("8$x$y", $kind, $x, $y);
			$this->addEntity($npc);
			$this->npcs[$npc->id] = $npc;
			
			return $npc;
		}
		
		public function addItem($item) {
			$this->addEntity($item);
			$this->items[$item->id] = $item;
			return $item;
		}
		
		public function createItem($kind, $x, $y) {
			$id = '9' . $this->itemCount++;
			$item = null;
			
			if ($kind === Types::Entities_CHEST) {
				$item = new Chest($id, $x, $y);
			} else {
				$item = new Item($id, $kind, $x, $y);
			}
			return $item;
		}
		
		public function createChest($x, $y, $items) {
			$chest = $this->createItem(Types::Entities_CHEST, $x, $y);
			$chest->setItems($items);
			return $chest;
		}
		
		public function addStaticItem($item) {
			$self = $this;
			$item->isStatic = true;
			$item->onRespawn(function() use ($self, $item) { $self->addStaticItem($item); });
			return $this->addItem($item);
		}
		
		public function addItemFromChest($kind, $x, $y) {
			$item = $this->createItem($kind, $x, $y);
			$item->isFromChest = true;
			return $this->addItem($item);
		}
		
		public function clearMobAggroLink($mob) {
			$player = null;
			if ($mob->target) {
				$player = $this->getEntityById($mob->target);
				if ($player) {
					$player->removeAttacker($mob);
				}
			}
		}
		
		public function clearMobHateLinks($mob) {
			if ($mob) {
				foreach ($mob->hatelist as $obj) {
					$player = $this->getEntityById($obj->id);
					if ($player) {
						$player->removeHater($mob);
					}
				}
			}
		}
		
		public function forEachEntity($callback) {
			foreach ($this->entities as &$entity) {
				$callback($entity);
			}
			unset($entity);
		}
		
		public function forEachPlayer($callback) {
			foreach ($this->players as &$player) {
				$callback($player);
			}
			unset($player);
		}
		
		public function forEachMob($callback) {
			foreach ($this->mobs as &$mob) {
				$callback($mob);
			}
			unset($mob);
		}
		
		public function forEachCharacter($callback) {
			$this->forEachPlayer($callback);
			$this->forEachMob($callback);
		}
		
		public function handleMobHate($mobId, $playerId, $hatePoints) {
			$mob = $this->getEntityById($mobId);
			$player = $this->getEntityById($playerId);
			$mostHated;
			
			if ($player && $mob) {
				$mob->increaseHateFor($playerId, $hatePoints);
				$player->addHater($mob);
				
				// only choose a target if still alive
				if ($mob->hitPoints > 0) {
					$this->chooseMobTarget($mob);
				}
			}
		}
		
		public function chooseMobTarget($mob, $hateRank = null) {
			$player = $this->getEntityById($mob->getHatedPlayerId($hateRank));
			
			// If the mob is not already attacking the player, create an attack link between them.
			if ($player && !array_key_exists($mob->id, $player->attackers)) {
				$this->clearMobAggroLink($mob);
				
				$player->addAttacker($mob);
				$mob->setTarget($player);
				
				$this->broadcastAttacker($mob);
				echo "{$mob->id} is now attacking {$player->id}\n";
			}
		}
		
		public function onEntityAttack($callback) {
			$this->attack_callback = $callback;
		}
		
		public function getEntityById($id) {
			if (array_key_exists($id, $this->entities) ) {
				return $this->entities[$id];
			} else {
				echo "Unknown entity: $id\n";
			}
		}
		
		public function getPlayerCount() {
			$count = 0;
			foreach ($this->players as $p=>$val) {
				if (property_exists($this->players, $p) ) {
					$count += 1;
				}
			}
			return $count;
		}
		
		public function broadcastAttacker($character) {
			if ($character) {
				$this->pushToAdjacentGroups($character->group, $character->attack(), $character->id);
			}
			if ($this->attack_callback) {
				$this->attack_callback($character);
			}
		}
		
		public function handleHurtEntity($entity, $attacker = null, $damage = null) {
			if ($entity->type === 'player') {
				// A player is only aware of his own hitpoints
				$this->pushToPlayer($entity, $entity->health() );
			}
			
			if ($entity->type === 'mob') {
				// Let the mob's attacker (player) know how much damage was inflicted
				$this->pushToPlayer($attacker, new Messages\Damage($entity, $damage) );
			}
			
			// If the entity is about to die
			if ($entity->hitPoints <= 0) {
				if ($entity->type === 'mob') {
					$mob = $entity;
					$item = $this->getDroppedItem($mob);
					
					$this->pushToPlayer($attacker, new Messages\Kill($mob) );
					$this->pushToAdjacentGroups($mob->group, $mob->despawn()); // Despawn must be enqueued before the item drop
					if ($item) {
						$this->pushToAdjacentGroups($mob->group, $mob->drop($item));
						$this->handleItemDespawn($item);
					}
				}
				
				if ($entity->type === 'player') {
					$this->handlePlayerVanish($entity);
					$this->pushToAdjacentGroups($entity->group, $entity->despawn());
				}
				
				$this->removeEntity($entity);
			}
		}
		
		public function despawn($entity) {
			$this->pushToAdjacentGroups($entity->group, $entity->despawn());
			
			if (array_key_exists($entity->id, $this->entities)) {
				$this->removeEntity($entity);
			}
		}
		
		public function spawnStaticEntities() {
			$self = $this;
			$count = 0;
			foreach ($this->map->staticEntities as $tid=>$kindName) {
				$kind = Types::getKindFromString($kindName);
				$pos = $this->map->tileIndexToGridPosition($tid);
				
				if (Types::isNPC($kind) ) {
					$this->addNPC($kind, $pos->x+1, $pos->y);
				}
				if (Types::isMob($kind) ) {
					$mob = new Mob('7' . $kind . $count++, $kind, $pos->x+1, $pos->y);
					$mob->onRespawn( function() use ($self, $mob) {
						$mob->isDead = false;
						$self->addMob($mob);
						if ($mob->area && $mob->area instanceof ChestArea) {
							$mob->area->addToArea($mob);
						}
					});
					$mob->onMove( function () use ($self, $mob) { $self->onMobMoveCallback($mob); });
					$this->addMob($mob);
					$this->tryAddingMobToChestArea($mob);
				}
				if (Types::isItem($kind) ) {
					$this->addStaticItem($this->createItem($kind, $pos->x+1, $pos->y) );
				}
			}
		}
		
		public function isValidPosition($x, $y) {
			if ( $this->map && is_numeric($x) && is_numeric($y) && !$this->map->isOutOfBounds($x, $y) && !$this->map->isColliding($x, $y) ) {
				return true;
			}
			return false;
		}
		
		public function handlePlayerVanish($player) {
			$self = $this;
			$previousAttackers = array();
			
			// When a player dies or teleports, all of his attackers go and attack their second most hated player.
			$player->forEachAttacker( function($mob) use ($self, $previousAttackers) {
				$previousAttackers[] = $mob;
				$self->chooseMobTarget($mob, 2);
			});
			
			foreach ($previousAttackers as $mob) {
				$player->removeAttacker($mob);
				$mob->clearTarget();
				$mob->forgetPlayer($player->id, 1000);
			}
			
			$this->handleEntityGroupMembership($player);
		}
		
		public function setPlayerCount($count) {
			$this->playerCount = $count;
		}
		
		public function incrementPlayerCount() {
			$this->setPlayerCount($this->playerCount + 1);
		}
		
		public function decrementPlayerCount() {
			if ($this->playerCount > 0) {
				$this->setPlayerCount($this->playerCount - 1);
			}
		}
		
		public function getDroppedItem($mob) {
			$kind = Types::getKindAsString($mob->kind);
			$drops = Properties::$mobs[$kind]['drops'];
			$v = mt_rand(0, 100);
			$p = 0;
			$item = null;
			
			foreach ($drops as $itemName=>$percentage) {
				$p += $percentage;
				if ($v <= $p) {
					$item = $this->addItem($this->createItem(Types::getKindFromString($itemName), $mob->x, $mob->y) );
					break;
				}
			}
			
			return $item;
		}
		
		public function onMobMoveCallback($mob) {
			$this->pushToAdjacentGroups( $mob->group, new Messages\Move($mob), false );
			$this->handleEntityGroupMembership($mob);
		}
		
		public function findPositionNextTo($entity, $target) {
			$valid = false;
			$pos = null;
			
			while (!$valid) {
				$pos = $entity->getPositionNextTo($target);
				$valid = $this->isValidPosition($pos->x, $pos->y);
			}
			return $pos;
		}
		
		public function initZoneGroups() {
			$self = $this;
			
			$this->map->forEachGroup( function($id) use ($self) {
				$self->groups[$id] = new \StdClass();
				$self->groups[$id]->entities = array();
				$self->groups[$id]->players = array();
				$self->groups[$id]->incoming = array();
			} );
			$this->zoneGroupsReady = true;
		}
		
		public function removeFromGroups($entity) {
			$self = $this;
			$oldGroups = array();
			
			if ($entity && $entity->group) {
				$group = $this->groups[$entity->group];
				if ($entity instanceof Player) {
					$filtered = array();
					foreach ($group->players as $id) {
						if ($id !== $entity->id) {
							$filtered[] = $id;
						}
					}
					$group->players = $filtered;
				}
				
				$this->map->forEachAdjacentGroup($entity->group, function ($id) use ($self, $entity) {
					if (array_key_exists($entity->id, $self->groups[$id]->entities) ) {
						unset($self->groups[$id]->entities[$entity->id]);
						$oldGroups[] = $id;
					}
				});
				
				$entity->group = null;
			}
			return $oldGroups;
		}
		
		 /**
		 * Registers an entity as "incoming" into several groups, meaning that it just entered them.
		 * All players inside these groups will receive a Spawn message when WorldServer.processGroups is called.
		 */
		public function addAsIncomingToGroup($entity, $groupId) {
			$self = $this;
			$isChest = $entity && $entity instanceof Chest;
			$isItem = $entity && $entity instanceof Item;
			$isDroppedItem = $entity && $isItem && !$entity->isStatic && !$entity->isFromChest;
			
			if ($entity && $groupId) {
				$this->map->forEachAdjacentGroup($groupId, function($id) use ($self, $entity, $isChest, $isItem, $isDroppedItem) {
					$group = $self->groups[$id];
								
					if ($group) {
						
						if (!in_array($entity->id, $group->entities, true) && (!$isItem || $isChest || ($isItem && !$isDroppedItem) ) ) {
							$group->incoming[] = $entity;
						}
					}
				});
			}
		}
		
		public function addToGroup($entity, $groupId) {
			$self = $this;
			$newGroups = array();
			
			if ($entity && $groupId && (array_key_exists($groupId, $this->groups) ) ) {
				$this->map->forEachAdjacentGroup($groupId, function($id) use ($self, $entity) {
					$self->groups[$id]->entities[$entity->id] = $entity;
					$newGroups[] = $id;
				});
				$entity->group = $groupId;
				
				if ($entity instanceof Player) {
					$this->groups[$groupId]->players[] = $entity->id;
				}
			}
			return $newGroups;
		}
		
		public function logGroupPlayers($groupId) {
			echo "Players inside group $groupId:\n";
			foreach ($this->groups[$groupId]->players as $id) {
				echo " - player $id\n";
			}
		}
		
		public function handleEntityGroupMembership($entity) {
			$hasChangedGroups = false;
			if ($entity) {
				$groupId = $this->map->getGroupIdFromPosition($entity->x, $entity->y);
				if ( !$entity->group || ($entity->group !== $groupId) ) {
					$hasChangedGroups = true;
					$this->addAsIncomingToGroup($entity, $groupId);
					$oldGroups = $this->removeFromGroups($entity);
					$newGroups = $this->addToGroup($entity, $groupId);
					
					if (count($oldGroups) > 0) {
						$entity->recentlyLeftGroups = array_diff($oldGroups, $newGroups);
						echo "Group Diff: {$entity->recentlyLeftGroups}\n";
					}
				}
			}
			return $hasChangedGroups;
		}
		
		public function processGroups() {
			$self = $this;
			
			if ($this->zoneGroupsReady) {
				$this->map->forEachGroup( function($id) use ($self) {
					$spawns = array();
					if (count($self->groups[$id]->incoming) > 0) {
						foreach ($self->groups[$id]->incoming as $entity) {
							if ($entity instanceof Player) {
								$self->pushToGroup($id, new Messages\Spawn($entity), $entity->id);
							} else {
								$self->pushToGroup( $id, new Messages\Spawn($entity) );
							}
						}
						$self->groups[$id]->incoming = array();
					}
				} );
			}
		}
		
		public function moveEntity($entity, $x, $y) {
			if ($entity) {
				$entity->setPosition($x, $y);
				$this->handleEntityGroupMembership($entity);
			}
		}
		
		public function handleItemDespawn($item) {
			$self = $this;
			if ($item) {
				$argument = new \StdClass();
				$argument->beforeBlinkDelay = 10000;
				$argument->blinkCallback = function() use ($self) { $self->pushToAdjacentGroups($item->group, new Messages\Blink($item)); };
				$argument->blinkingDuration = 4000;
				$argument->despawnCallback = function() use ($self) { 
					$self->pushToAdjacentGroups($item->group, new Messages\Destroy($item) );
					$self->removeEntity($item);
				};
				$item->handleDespawn($argument);
			}
		}
		
		public function handleEmptyMobArea($area) {
			// Actually is empty
		}
		
		public function handleEmptyChestArea($area) {
			if ($area) {
				$chest = $this->addItem($this->createChest($area->chestX, $area->chestY, $area->items) );
				$this->handleItemDespawn($chest);
			}
		}
		
		public function handleOpenedChest($chest, $player) {
			$this->pushToAdjacentGroups($chest->group, $chest->despawn() );
			$this->removeEntity($chest);
			
			$kind = $chest->getRandomItem();
			if ($kind) {
				$item = $this->addItemFromChest($kind, $chest->x, $chest->y);
				$this->handleItemDespawn($item);
			}
		}
		
		public function tryAddingMobToChestArea($mob) {
			foreach ($this->chestAreas as $area) {
				if ( $area->contains($mob) ) {
					$area->addToArea($mob);
				}
			}
		}
		
		public function updatePopulation($totalPlayers) {
			$this->pushBroadcast( new Messages\Population($this->playerCount, $totalPlayers ? $totalPlayers : $this->playerCount) );
		}
		
		// needed to get callbacks to work from within an object
		public function __call($function, $arguments) {
			$callbacks = array('init_callback', 'connect_callback', 'enter_callback', 'added_callback', 'removed_callback', 'regen_callback', 'attack_callback');
			if ( in_array($function, $callbacks) ) {
				call_user_func_array($this->$function, $arguments);
			} else {
				trigger_error("No WorldServer::$function Method.",E_USER_ERROR);
			}
		}
	}
?>