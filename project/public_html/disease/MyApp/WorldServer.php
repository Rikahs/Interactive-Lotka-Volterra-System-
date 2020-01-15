<?php
	namespace MyApp;
	use MyApp\Map;
	use MyApp\Player;
	use MyApp\Messages;
	use MyApp\Types;
	
	class WorldServer {
		protected $id;
		protected $maxPlayers;
		protected $server;
		
		protected $updatesPerSecond;
		public $map;
		

		protected $entities;    // An array of all entities in the world
        protected $players;     // An array of all players in the world. Players are entities too.
        protected $mobs;        // An array of all enemies in the world. Enemies are entities too.
        protected $items;       // An array of all items in the world.  Items are entities too.
        protected $npcs;        // An array of all the NPCs in the world. NPCs are entities too. NPCs are those stationary people/animals that you can talk to. Not the enemies you fight.
        
        protected $attackers;   // Exists in BrowserQuest, but I do not see it being used anywhere.
        protected $equipping;   // Exists in BrowserQuest, but I do not see it being used anywhere.
        protected $hurt;        // Exists in BrowserQuest, but I do not see it being used anywhere.
        
        protected $mobAreas;    // An array that holds all of the mobAreas in the world. These are invisible areas defined in the maps json that mobs can spawn in.
        protected $chestAreas;  // An array that holds all of the mobAreas in the world. These are invisible  areas defined in the maps json that chests can spawn in when all the mobs in an area are defeated.
        protected $groups;      // An array that holds all of the groups in the world. A player is assigned to a group and only gets updates that are relevant to its group. Each screen/zone is linked to a group id. 
		
		protected $outgoingQueues;    // An array of outgoing message queues for each player in the world.  
        
        protected $itemCount;   // A count of items in the world.
        public $playerCount;    // A count of players in the world.
		
		public $preybleedcount;// dot
		public $preybleedtime;// dot
		public $fulltime;
		public $fullCount;
		public $configuration;
		public $deathtime;
		public $deathcount;
		public $i;
		public $areas; 			//holdsall mob areas
		public $breedTime;
		public $breedCount;     // A counter of how many update cyces have passed before breeding
		public $updateCount;    // A counter of how many update cycles have passed. Used in Main::onMessage() when handling update messages.
		public $regenCount;     // A value indicating how many updates should occur before regenerating health for the players.
		
		protected $zoneGroupsReady;
		
        // This callbacks variable will hold an associative array of all the callbacks defined individually below.  
        protected $callbacks;
        
        // After all of the calls that set these callbacks are changed over to using the on() method these should be able to be removed.
		protected $init_callback;
		protected $connect_callback;
		protected $enter_callback;
		protected $added_callback;
		protected $removed_callback;
		public $regen_callback;
		public $bleed_callback;
		protected $attack_callback;
		protected $bite_callback;
		
		
		public $ticksSinceStart;
		
		public function __construct($configuration,$id, $maxPlayers, $websocketServer) {
			echo "Creating World\n";
			$self = $this;
			
			$this->configuration=$configuration;

			$this->id = $id;
			$this->maxPlayers = $maxPlayers;
			$this->server = $websocketServer;
			$this->updatesPerSecond = 50;
			
			$this->map = null;
			
			$this->entities = array();
			$this->players = array();
			$this->mobs = array();
			$this->attackers = array();
			$this->items = array();
			$this->equipping = null;
			$this->hurt = null;
			$this->npcs = array();
			$this->mobAreas = array();
			$this->chestAreas = array();
			$this->groups = array();
			
			$this->outgoingQueues = array();
			
			$this->itemCount = 0;
			$this->playerCount = 0;
			$this->ticksSinceStart =0;
			
			$this->zoneGroupsReady = false;
			
            // Here is an example of using the general purpose callback setter, as opposed to the commented out code below.
            // However, this change also means there needs to be a change where the callback is actually executed.
            // Unfortunately, that is not necessarily in a centralised location.
			$this->on('PlayerConnect', function($player) use ($self) {
				$player->onRequestPosition(function() use ($self, $player) {
					if($player->lastCheckpoint) {
						return $player->lastCheckpoint->getRandomPosition();
					} else {
						$pos = $self->map->getRandomStartingPosition();
						return $pos;
					}
				});
			});
            
            /*
            $this->onPlayerConnect(function($player) use ($self) {
                $player->onRequestPosition(function() use ($self, $player) {
                    if($player->lastCheckpoint) {
                        return $player->lastCheckpoint->getRandomPosition();
                    } else {
                        $pos = $self->map->getRandomStartingPosition();
                        return $pos;
                    }
                });
            });
            */
			
			// Here's another example
			$this->on('PlayerEnter', function($player) use ($self) {
				echo $player->name . ' has joined ' . $self->id . "\n";
				$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
				$text ="\r\n".(microtime(true)-Main::getStartTime())."\t Player $player->name has logged in and is assigned the id $player->id  point:$player->x $player->y";
				fwrite($playerFile,$text);
				fclose($playerFile);
				
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
                
                $player->on('Move', $move_callback);
				$player->on('LootMove', $move_callback);
				
				$player->on('Zone', function() use ($self, $player) {


					$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
					$text ="\r\n".(microtime(true)-Main::getStartTime())."\t Player $player->name $player->id \t is at point: $player->x $player->y";
					fwrite($playerFile,$text);
					fclose($playerFile);
					$hasChangedGroups = $self->handleEntityGroupMembership($player);
					
					if ($hasChangedGroups) {
						$self->pushToPreviousGroups($player, new Messages\Destroy($player));
						$self->pushRelevantEntityListTo($player);
					}
				});
				
				if(isset($this->configuration->chat))
				{
					$player->on('Broadcast', function($message, $ignoreSelf) use ($self, $player) {
					echo "Broadcasting \n";
					$self->pushToAdjacentGroups($player->group, $message, $ignoreSelf ? $player->id : null);
					});
				}
				
				
				$player->on('BroadcastToZone', function($message, $ignoreSelf) use ($self, $player) {
					$self->pushToGroup($player->group, $message, $ignoreSelf ? $player->id : null);
				});
		
				$player->on('Exit', function() use ($self, $player) {
					echo "{$player->name} has left the game.\n";
					$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
						$text ="\r\n".(microtime(true)-Main::getStartTime())."\tPlayer $player->name $player->id \t has left the game at point:$player->x $player->y";
						fwrite($playerFile,$text);
						fclose($playerFile);
					$self->removePlayer($player);
					$self->decrementPlayerCount();
					
					if (isset($self->callbacks['PlayerRemoved'])) {	//EDITED
						$self->callbacks['PlayerRemoved']();	//EDITED
					}
				});
				
			});
								
			// Called when an entity is attacked by another entity
			$this->on('EntityAttack', function($player) use ($self) {			
			/*$this->onEntityAttack( function($attacker) use ($self) {*/
				$target = $self->getEntityById($attacker->target);
				if ($target && $attacker->type === "mob") {
					$pos = $self->findPositionNextTo($attacker, $target);
					$self->moveEntity($attacker, $pos->x, $pos->y);
				}
			});
			
			$this->on('EntityBite', function($player) use ($self) {			
			/*$this->onEntityAttack( function($attacker) use ($self) {*/
				$target = $self->getEntityById($attacker->target);
				if ($target && $attacker->type === "mob") {
					$pos = $self->findPositionNextTo($attacker, $target);
					$self->moveEntity($attacker, $pos->x, $pos->y);
				}
			});
			
			
			$this->on('RegenTick', function($player) use ($self) {			
			/*$this->onRegenTick( function() use ($self) {*/

					$self->forEachCharacter( function($character) use ($self) {
					
						$character->regenHealthBy( floor($character->maxHitPoints / 25));
				
						if( $character->type === 'player') {
							$self->pushToPlayer( $character, $character->regen() );
						}
					
				});
			});
			
			$this->on('BleedTick', function($player) use ($self) {			
			//$this->onBleedTick( function() use ($self) {
			
		
				$self->forEachCharacter( function($character) use ($self) {
					
					if( $character->type === 'player') {	
							if($character->hitPoints>=10)
							{
						
							$character->decreaseHealthBy( floor($character->maxHitPoints / 25));
							$self->pushToPlayer( $character, $character->regen() );
							}

						}
					
				});
			});
			
			echo "World Created.\n";
		}
		
		public function run($mapFilePath) {
			$self = $this;
			$this->i=0;
			$this->map = new Map();
			
			$this->map->ready( function() use ($self) {
				$self->initZoneGroups();
				
				// $self->map->generateCollisionGrid(); // CPU Intensive BLOCKING task in PHP!!!!! Takes about 30 seconds.
				
				// Populate all mob "roaming" areas

				//controls how many of each mob is spawned in town note mobs and static entites are different 
				foreach ($self->map->mobAreas as $mobArea) {
					switch ($mobArea->type)
			{
			     case "rat":
			        if($mobArea->id==0 || $mobArea->id==7 || $mobArea->id==8 )
			        {
			        	$mobArea->nb=floor($this->configuration->ratpopulation/4);
			        }
			         break;
			     case "bat":
			         if($mobArea->id==1 || $mobArea->id==2 || $mobArea->id==3 || $mobArea->id==19 || $mobArea->id==20)
			        {
			        	$mobArea->nb=floor($this->configuration->batpopulation/5);
			        }
			         break;
			     case "goblin":
			         if($mobArea->id==5 || $mobArea->id==6 )
			        {
			        	$mobArea->nb=floor($this->configuration->goblinpopulation/2);
			        }
			         break;

			          case "crab":
			         if($mobArea->id==9 || $mobArea->id==10 || $mobArea->id==11 || $mobArea->id==17)
			        {
			        	$mobArea->nb=floor($this->configuration->crabpopulation/4);
			        }
			         break;

			          case "skeleton":
			         if( $mobArea->id==18 || $mobArea->id==22)
			        {
			        	$mobArea->nb=floor($this->configuration->skeletonpopulation/2);
			        }
			         break;
			     default:
			        $mobArea->nb=0;
			}
					$area = new MobArea($mobArea->id, $mobArea->nb, $mobArea->type, $mobArea->x, $mobArea->y, $mobArea->width, $mobArea->height, $self);
					$area->spawnMobs();
					$area->onEmpty( $self->handleEmptyMobArea($area) ); // $area->onEmpty( $self->handleEmptyMobArea->bind($self, $area) ); //Another Bind
					$this->areas[$this->i]=$area;
					$this->i+=1;
						array_push($self->mobAreas, $area);
				}
				/*
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
				
				
				
				// Set maximum number of entities contained in each chest area
				foreach ($self->chestAreas as $area) {
					$area->setNumberOfEntities(count($area->entities));
				}*/
				// Spawn static entities
				$self->spawnStaticEntities();//only the npcs are spawned
	
			});
			// This has to come after because PHP does not have asynchronous file loading.
			$json = json_decode( file_get_contents($mapFilePath) );
			$this->map->initMap($json);
			
			//player bleeding
			switch ($this->configuration->playerdeathrate)
			{
			     case 1:
			        $this->regenCount =10;//player bleeding
			         break;
			     case 2:
			         $this->regenCount =20;
			         break;
			     case 3:
			         $this->regenCount =30;
			         break;
			     default:
			         $this->regenCount =20;
			}


			//prey breeding
			switch ($this->configuration->preyreporate)
			{
			     case 1:
			        $this->breedTime=10;
			         break;
			     case 2:
			        $this->breedTime=15;//prey
			         break;
			     case 3:
			        $this->breedTime=25;
			         break;
			     default:
			         $this->breedTime=25;
			}
		
			//setting the rate at which prey lose hp
			switch ($this->configuration->preymetrate)
			{
			     case 1:
			        $this->preybleedtimee=5;
			         break;
			     case 2:
			        $this->preybleedtime=13;//prey
			         break;
			     case 3:
			        $this->preybleedtime=20;
			         break;
			     default:
			         $this->preybleedtime=10;
			}
			//$this->regenCount = $this->updatesPerSecond ;
			
			
			
			$this->deathtime=$this->configuration->preydeathage; //preydyingage
			$this->breedCount=0;
			$this->updateCount = 10;
		
			$this->fullCount=0;//max food lv player
			$this->fulltime=5;

			
			// Not sure how to implement setInterval in PHP which would basically be to execute world-generated events and the important pushing along of messages.
			// I tried looking at threads but was just confused. Eventually ended up with the current plan of using the RatchetQuest-Updater.html workaround.
			//MOVED INTO MAIN.PHP
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
			
			
			$this->stateOfWorld();
			echo "{$this->id} created (Capacity: {$this->maxPlayers} players)\n";
		}
		
		public function setUpdatesPerSecond($updatesPerSecond) {
			$this->updatesPerSecond = $updatesPerSecond;
		}
		
        // After changing over to using the on() method all of these onSomeEvent() callback setters can be removed
		/*public function onInit($callback) {
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
		*/
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
		
        // This is the important function that actually sends off the messages to the clients.
		public function processQueues() {
			foreach ($this->outgoingQueues as $id => &$queue) {
				if (count($queue) > 0) {
					$connection = $this->server->players[$id]->connection;
                    if ($connection != null) {
					   $connection->send(json_encode($queue));
                    }
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
		
		//lets the prey atk back
		public function chooseMobTarget($mob, $hateRank = null) {

			$player = $this->getEntityById($mob->getHatedPlayerId($hateRank));
			
				// If the mob is not already attacking the player, create an attack link between them.
			if(!($player==null))
				if ($player && !array_key_exists($mob->id, $player->attackers)) {
					$this->clearMobAggroLink($mob);
					
					$player->addAttacker($mob);
					$mob->setTarget($player);
					
					$this->broadcastAttacker($mob);
					echo "{$mob->id} is now attacking {$player->id}\n";
				}
			
		}
		/*
		public function chooseMobTarget($mob, $hateRank = null) {
		$this->clearMobAggroLink($mob);
				/*$numOfMobs=count($this->mobs);
				$creatureIndex =Utils::random($numOfMobs);
				if(isset($this->mobs[$creatureIndex]))
				{
						$chosenMob=$this->mobs[$creatureIndex];
							
							if(Types::getKindAsString($chosenMob->kind)!="skeleton")
							{
								//$chosenMob=$this->mobs[Utils::random($numOfMobs)];
							}
							else
							{
							$mob->setTarget($this->getEntityById($chosenMob->id));
							
							$this->broadcastAttacker($mob);
							echo "{$mob->id} is now attacking {$chosenMob->id}\n";
							}
				}
			}*/
			
		//}
		
		public function onEntityAttack($callback) {
			$this->attack_callback = $callback;
		}
		public function onEntityBite($callback) {
			$this->bite_callback= $callback;
		}
		public function onEntityInfect($callback) {
			$this->infect_callback= $callback;
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
		public function broadcastBite($character) {
			if ($character) {
				$this->pushToAdjacentGroups($character->group, $character->bite(), $character->id);
			}
			if ($this->bite_callback) {
				$this->bite_callback($character);
			}
		}


		// function i made kills mobs of old age
		public function dieofoldage($entity,$attacker)
		{
			if ($entity->hitPoints <= 0) 
			{
				if ($entity->type === 'mob') 
				{
					$mob = $entity;
					$item = $this->getDroppedItem($mob);
					$message=new Messages\Kill($mob);
					//array_push( $this->outgoingQueues[$attacker->id], $message->serialize() );
					$this->pushToAdjacentGroups($mob->group, $mob->despawn()); // Despawn must be enqueued before the item drop
					if ($item) 
					{
						$this->pushToAdjacentGroups($mob->group, $mob->drop($item));
						$this->handleItemDespawn($item);
					}
				}
			}

			$mobFile = fopen(".\MyApp\MOBLOG.csv", "a+");
			$text ="\r\n".(microtime(true)-Main::getStartTime())."\t".Types::getKindAsString($entity->kind)." $entity->id has died of old age at point:$entity->x $entity->y";
			fwrite($mobFile,$text);
			fclose($mobFile);
			
			$this->removeEntity($entity);
		}
		
		// makes the mob drop an item if it bleeds to death 
		public function handleBleedToDeath($mob)
		{
			$item = $this->getDroppedItem($mob);
			$this->pushToAdjacentGroups($mob->group, $mob->despawn()); // Despawn must be enqueued before the item drop
			if ($item)
			{
				$this->pushToAdjacentGroups($mob->group, $mob->drop($item));
				$this->handleItemDespawn($item);
			}
			
			$mobFile = fopen(".\MyApp\MOBLOG.csv", "a+");
			$text ="\r\n".(microtime(true)-Main::getStartTime())."\t".Types::getKindAsString($mob->kind)." $mob->id has starved to death at point: $mob->x $mob->y";
			fwrite($mobFile,$text);
			fclose($mobFile);
			$this->removeEntity($mob);
		}

		//handles mob on mob action
		public function handlemobonmob($mobA, $mobB, $damage)
		{
			if ($mobA->hitPoints <= 0)
				{
					$mobB->updateHitPoints();//sets atking mobs hp to full
						$this->pushToAdjacentGroups($mobA->group, $mobA->despawn()); // Despawn must be enqueued before the item drop
						$mobFile = fopen(".\MyApp\MOBLOG.csv", "a+");
						$text ="\r\n".(microtime(true)-Main::getStartTime())."\t".Types::getKindAsString($mobA->kind)." $mobA->id has been killed by ".Types::getKindAsString($mobB->kind)."$mobB->id at point: $mobA->x $mobA->y";
						fwrite($mobFile,$text);
						fclose($mobFile);
						$this->removeEntity($mobA);	
				}
		}
		public function handleHurtEntity($entity, $attacker = null, $damage = null) {
			if ($entity->type === 'player')
				{
					// A player is only aware of his own hitpoints
					$this->pushToPlayer($entity, $entity->health() );
				}
				
				if ($entity->type === 'mob')
				{
					// Let the mob's attacker (player) know how much damage was inflicted
					$this->pushToPlayer($attacker, new Messages\Damage($entity, $damage) );
					//$this->pushToPlayer(,new Messages\)
					
				}
				
				// If the entity is about to die
				if ($entity->hitPoints <= 0)
				{
					if ($entity->type === 'mob')
					{
						$mob = $entity;
						$item = $this->getDroppedItem($mob);
						
						$this->pushToPlayer($attacker, new Messages\Kill($mob) );
						$this->pushToAdjacentGroups($mob->group, $mob->despawn()); 

						$playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
						$text ="\r\n".(microtime(true)-Main::getStartTime())."\tPlayer $attacker->name $attacker->id \thas killed ".Types::getKindAsString($entity->kind)." $entity->id at point: $entity->x $entity->y";
						fwrite($playerFile,$text);
						fclose($playerFile);


						$mobFile = fopen(".\MyApp\MOBLOG.csv", "a+");
						$text ="\r\n".(microtime(true)-Main::getStartTime())."\t".Types::getKindAsString($entity->kind)." $entity->id has been killed by $attacker->name $attacker->id at point: $entity->x $entity->y";
						fwrite($mobFile,$text);
						fclose($mobFile);
						// Despawn must be enqueued before the item drop
						if ($item)
						{
							$this->pushToAdjacentGroups($mob->group, $mob->drop($item));
							$this->handleItemDespawn($item);
						}
					}
					
					if ($entity->type === 'player') 
					{
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
					$mob = new Mob('7' . $kind . $count++, $kind, $pos->x+1, $pos->y,$this->configuration);
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
			 $playerFile = fopen(".\MyApp\PLAYERLOG.csv", "a+");
			$text ="\r\n".(microtime(true)-Main::getStartTime())."\t Player $player->name $player->id \t has left the game at point: $player->x $player->y";
			fwrite($playerFile,$text);
			fclose($playerFile);
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
		
		//changed to drop certain items on how tmobs died
		
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
		public function onMobBiteCallback($mob)
		{
			$this->pushToAdjacentGroups( $mob->group, new Messages\Bite($mob), false );
			$this->handleEntityGroupMembership($mob);
		}
		public function onMobInfectCallback($mob)
		{
			$this->pushToAdjacentGroups( $mob->group, new Messages\Infect($mob), false );
			$this->handleEntityGroupMembership($mob);
		}
		public function onMobMoveCallback($mob) {
		    // Note this is an exception as it is not setting a callback
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
		
        // The general purpose callback assigner
        // instead of calling onSomeEvent() and having to define a function for each event just pass the event name as the first parameter.
        // The benefits of this is that if new events need to be defined later, the same on() method and callbacks array can be reused
        // instead of having to modify WorldServer to create a new onSomeEvent() method with its matching someEvent_callback storage variable.
        public function on($event, $callback) {
            $this->callbacks[$event] = $callback;
        }
        
		// Needed to get callbacks to work from within an object the old way. This should not be needed once all callbacks have been converted over to the new way..
		public function __call($function, $arguments) {
			$callbacks = array('init_callback', 'connect_callback', 'enter_callback', 'added_callback', 'removed_callback', 'regen_callback', 'attack_callback','bite_callback','bleed_callback');
			if ( in_array($function, $callbacks) ) {
				call_user_func_array($this->$function, $arguments);
			} else {
				trigger_error("No WorldServer::$function Method.",E_USER_ERROR);
			}
		}
        
        // This should allow the callbacks to be accessible from outside of this class.
        public function __get($variableName) {
            switch ($variableName) {
                case 'callbacks': return $this->callbacks;
            }
        }
		
		// Makes each mob roam
		public function mobAreaRoam() {
		$self=$this;
			$i=0;
			$activeAreas =array();
			
			foreach($this->mobAreas as $mobArea)
			{
				$i++;
				if(count($mobArea->entities)>0)
				array_push($activeAreas,$i);
			}
			$numberOfAreas=count($activeAreas);
			
			//check if mobArea is empty then select a different area
			
			
			
			foreach ($this->mobAreas as $mobArea) {
				foreach ($mobArea->entities as $mob) {
					
					$canRoam = Utils::random(20) == 1;
					//$canRoam = true;
					if ($canRoam) {
						if (!$mob->hasTarget() && !$mob->isDead) {
							//echo " ".Types::getKindAsString($mob->kind)." - $mob->id is moving from [$mob->x, $mob->y ]";
							$randomMoveArea = Utils::random($numberOfAreas);
							$area = $this->mobAreas[$randomMoveArea];
							$pos = $area->_getRandomPositionInsideArea();
							
							
							$mob->onMove( function () use ($self, $mob) { $self->onMobMoveCallback($mob); });
					
						//	if ($mob->distanceToSpawningPoint($pos->x, $pos->y) > 50) //prevents mobs from leaving spawn area
							//$mob->move($mob->x, $mob->y);
							//else
							$mob->move($pos->x, $pos->y);
							//echo " to [$pos->x, $pos->y] \n";
							//echo "\n R";
						}
					}
				}
			}
			/*
			foreach($self->mobs as $mob)
			{
				$canRoam = true;
				if($canRoam)
				{
					if (!$mob->hasTarget() && !$mob->isDead) {
							//echo " ".Types::getKindAsString($mob->kind)." - $mob->id is moving from [$mob->x, $mob->y ]";
							$area=$mob->area;
							$pos = $area->_getRandomPositionInsideArea();
							
							
							$mob->onMove( $self->onMobMoveCallback($mob) );
						
							$mob->move($pos->x, $pos->y);
							//echo " to [$pos->x, $pos->y] \n";
							//echo "\n R";
						}
				}
			}*/
		}//mobAreaRoam
		function infectMobGroup($kind,$disease)
		{
			$self=$this;
			
			$count=0;
			$onlyone=1;
			foreach($this->mobAreas as $mobArea)
			{
				foreach($mobArea->entities as $mob){
					$count++;
					if($count%2)
					if($onlyone>=1)
					{
						$onlyone--;
						if(Types::getKindAsString($mob->kind) == $kind){
						$mob->diseaseState=Types::DiseaseState_INFECTED;
						$mob->diseases = $disease;
						}
					}
				}
			}
		}

		//sets which mob can atk which and leets them atk
		function checkDistance($distance,$mob,$prey){
			$self=$this;
			$Attack=false;
				foreach($self->mobs as $othermob)
				{
					if($othermob->id != $mob->id)
					{
						if($mob->hitPoints<= $mob->maxHitPoints/0.25)
						{
							$attack=true;
						}
						//print_r($prey);
						//exit;
						//goblins eating bats rats and crabs
						if($attack && array_search($othermob->kind, $prey))
						{
							if(Utils::distanceTo($mob->x,$mob->y,$othermob->x,$othermob->y)<$distance)
							{
							//pass to disease mob and other mob to bite module
								$dmg = Formulas::dmg($mob->weaponLevel, $mob->armorLevel);
										
									
									
								if ($dmg > 0) 
								{
									$othermob->receiveDamage($dmg, $mob->id);
									$this->handleMobHate($mob->id, $othermob->id, $dmg);
									$this->handlemobonmob($othermob, $mob, $dmg);
								}
								break;	
							}
								
						}
						
					}
				}					
			
			/*
			//in the beginning once the number of infected is minimal check to see if the infected is close to susceptible mobs
			//once the susceptible decreases to a certain threshold 
			//check the susceptible to see if any infected are close to them
			
			//for all mob areas
			//for each mob in that area
			//check if they any occurances where the distance between the mobs is less than the specified distance
			//return 
		*/
		}//checkDistance 
		
				function catchDisease($mob)
				{
					$target = $mob->diseaseTarget;
					if ($mob->diseaseState==Types::DiseaseState_INFECTED) {
					
									$infection=Utils::random(5) == 1;
									//$infection=true;
										if(($infection)&&$target->getDiseaseState()!=Types::DiseaseState_RECOVERED)
										{					
												echo "INFECT message received\n";
												$serverFile = fopen(".\MyApp\SIRLOG.csv", "a+");
								$text ="\r\n".(time()-Main::getStartTime())."\t".Types::getKindAsString($target->kind)." - $target->id has been infected by ".Types::getKindAsString($mob->kind)." $mob->id \r\n";
								fwrite($serverFile,$text);
								fclose($serverFile);
												if(!empty($mob->diseases))
												foreach($mob->diseases as $d)
												{
													array_push($target->diseases,$d);
													/*if($d==Types::Disease_Dengue)
													{
														$target->setDiseaseState(Types::DiseaseState_EXPOSED);//incubation 4-7
														//infected with disease for 4-10 after 3-7 may develop into severe dengue without medical care will die
													}*/
													$target->diseasesContracted++;
												}
												//depends on which model is used SIR or SEIR
												$target->setDiseaseState(Types::DiseaseState_INFECTED);
										}
											
						}
				}
				
				public function recoverDisease()
				{
					foreach($this->mobs as $mob)
					{
						if($mob->getDiseaseState()==Types::DiseaseState_INFECTED)
						{
							$canRecover=Utils::random(100)==1;
							if($canRecover)
							{
								$mob->setDiseaseState(Types::DiseaseState_RECOVERED);

								echo "RECOVER MESSAGE RECEIVED\n";
								$serverFile = fopen(".\MyApp\SIRLOG.csv", "a+");
								$text = "\r\n".(time()-Main::getStartTime())."\t".Types::getKindAsString($mob->kind)." - $mob->id has recovered \r\n";
								fwrite($serverFile,$text);
								fclose($serverFile);
							}
							
						}
						/*
						if($mob->getDiseaseState()==Types::DiseaseState_INFECTED)
						{
							$mob->ticksSinceInfection++;
							if($mob->ticksSinceInfection>$recoveryPeriod)
							$mob->loseHealth();
						}*/
						
					}
				}
				
				public function countEntities()
				{
					$count=0;
					foreach($this->entities as $entity)
					{
						if(($entity->kind != "item")&&($entity->kind != "item"))
						$count++;
					}
					return $count;
				}
				public function countMob($type)
				{
					$count=0;
					foreach($this->mobs as $mob)
					{
						if(Types::getKindAsString($mob)==$type)
						$count++;
						
					}
					return $count;
				}
				
				public function countInfected()
				{
					$count=0;
					foreach($this->entities as $entity)
					{
						
						if($entity->diseaseState==Types::DiseaseState_INFECTED)
						$count++;
					}
						return $count;
				}
					public function countInfectedNpc()
				{
					$count=0;
					foreach($this->entities as $entity)
					{
						if($entity->kind="npc")
						if($entity->diseaseState==Types::DiseaseState_INFECTED)
						$count++;
					}
						return $count;
				}
				public function countSusceptible()
				{
					$count=0;
					foreach($this->entities as $entity)
					{
						
						if($entity->diseaseState==Types::DiseaseState_SUSCEPTIBLE)
						$count++;
					}
						return ($count-count($this->items)-count($this->players));
				}
				public function countRecovered()
				{
					$count=0;
					foreach($this->entities as $entity)
					{
						
						if($entity->diseaseState==Types::DiseaseState_RECOVERED)
						
						$count++;
					}
						return $count;
				}
				public function countDiseaseState($state)
				{
					$count=0;
					foreach($this->entities as $entity)
					{
						if($entity->type!="player")
						if($entity->diseaseState==$state)
						$count++;
					}
						return $count;
				}
				
				public function stateOfWorld()
				{
					echo "========================================================\n";
					echo 'Spread of Disease in the World at time '.date("Y-m-d H:i:s", time())."\n";
					echo "========================================================\n";
					echo "\nTotal Number of Entities	 	= ".($this->countEntities()-count($this->items));
					echo "\nTotal Number of Mobs       		= ".count($this->mobs)."\n";
					
					$serverFile = fopen(".\MyApp\WorldStateLOG.csv", "a+");
					//$test = "Time	Mobs	Susceptible	Infected	Recovered\n";
					if(time()%10)
					{
					 $test = "\r\n".(microtime(true)-Main::getStartTime())."\t".count($this->mobs)."\t".$this->countSusceptible()."\t".$this->countInfected()."\t".$this->countRecovered()."\r\n";
						fwrite($serverFile,$test);
					}
					
					fclose($serverFile);
				}
				
	}
?>
