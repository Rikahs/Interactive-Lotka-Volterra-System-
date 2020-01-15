<?php
	namespace MyApp;
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	use MyApp\WorldServer;
	use MyApp\Types;
	use MyApp\Formulas;		
	use MyApp\MessageHandlerFactory;
	use MyApp\MessageHandlers;
	use System\Authenticator;
	use ya\System\WsMessageHandlers\WsMessageHandlerArrayIterator;

	class Main implements MessageComponentInterface {
		protected $configuration;
		protected $clients;
		
		private static $move;
		public $players;
		protected $metrics;
		protected $worlds;
		protected $lastTotalPlayers;
		public $ticksSinceStart;
		public static $startTime;
		public static $endTime;
		public $time ;
		protected $infect;
		protected $recover;
		protected $initialInfect;

		public function __construct($configuration) {
			$this->configuration = $configuration;
			$this->clients = new \SplObjectStorage;
			$this->players = array();
			$this->metrics = $configuration->metrics_enabled ? new Metrics($configuration) : null;
			$this->worlds = new \SplObjectStorage;
			$this->lastTotalPlayers = 0;
			
			$this->move=false;
			$this->recover=true;
			$this->infect=true;
			$this->initialInfect=true;
			$this->ticksSinceStart;
			$this->startTime= microtime(true);
			$this->time = date("Y-m-d H:i:s", time());
			
			
				
			
			echo "Starting RatchetQuest game server...\n";
			echo "Current date and time fo the server ".$this->time."\n";
			for ($i = 1; $i <= $this->configuration->numberOfWorlds; $i++) {
				$world = new WorldServer($this->configuration,'World-' . $i, $this->configuration->numberOfPlayersPerWorld, $this); 
				$world->run($this->configuration->mapFilepath);
				$this->worlds->attach($world);
				if ($this->metrics) {
					$world->on('PlayerAdded',onPopulationChange);
					$world->on('PlayerRemoved',onPopulationChange);
				}
			}
			
			if ($this->configuration->metrics_enabled) {
				$metrics->ready(function() {
					onPopulationChange(); // initialise all counters to 0 when the server starts
				});
			}
		}

		public function onOpen(ConnectionInterface $conn) {
			// Store the new connection to send messages to later
			// This uses SplObjectStorage. They seem to work similar to arrays but this is mainly just something left over from the Ratchet demo program. 
			$this->clients->attach($conn);
			
			$world = $this->getAvailableWorld();
			
			if ($this->metrics) {
				$this->metrics->getOpenWorldCount(function($open_world_count) {
					// choose the least populated world among open worlds
					// This was supposed to iterate through all the worlds and find the one least populated to place the new player in. 
					// I found it is not necessary for our research environment
					/*
                    foreach ($this->worlds, $open_world_count) {
					}
					$world =
                    */ 
				});
			}
			
			if ($world) {
				$player = new Player($this->configuration,$conn, $world);
				$this->players[$conn->resourceId] = $player;

                // Here is how the new way to call callbacks would look.
                // The benefit is that it works off of an array which can have new events added to it
                // instead of multiple different variables one having to be defined for each new event desired. 
                $world->callbacks['PlayerConnect']($player);
				// $world->connect_callback($player);
			} else {
				echo "No Worlds defined.\n";
			}
		}

		public function updateTime()
		{
			$this->time = time();
		}

		
		public function onMessage(ConnectionInterface $from, $msg) {
			if ($msg == 'update') {
				   // This section deals with the update message that is sent from ratchetquest-updater.html
			    if ($this->configuration->updateIndicator) {
	                    echo "*";
	                    $this->ticksSinceStart++;
	                    foreach($this->worlds as $world)
	                    {
							if($this->infect)
							{
							if($world->countSusceptible()==0)
							{
								$this->infect=false;
								echo "ALL ENTITIES INFECTED";
								echo "Disease Spread began at:  ".$this->startTime;
								
								echo "ended at:".time();
								echo "time taken to infect all susceptible entities ".((time()-$this->startTime)  )." seconds";
								$serverFile = fopen(".\MyApp\SIRLOG.txt", "a+");
								fclose($serverFile);
							}
						}
						if($this->recover)
						{
							if($world->countRecovered()==$world->countEntities())
							{
								$this->recover=false;
								
								echo "ALL ENTITIES Recovered";
								echo "Disease Spread began at:  ".$this->startTime;
								
								echo "ended at:".time();
								echo "time taken to infect all susceptible entities ".((time()-$this->startTime)  )." seconds";
								
								$serverFile = fopen(".\MyApp\SIRLOG.txt", "a+");
								fclose($serverFile);
								exit();
							}
							
						}
						}
	                    
                }
				foreach ($this->worlds as $world) {
					$world->processGroups();
					$world->processQueues();
					if($this->initialInfect)
					{
					$this->initialInfect=false;
					$world->infectMobGroup("rat",array(Types::Disease_Dengue));
					//$world->infectMobGroup("bat",array(Types::Disease_Dengue));
					}
					$world->mobAreaRoam();
					$world->stateOfWorld();
					
				//	$world->recoverDisease();
					

					//makes mobs fight this code create an array containing the id for mobs which are prey then send it to the checkdistance function in worldserver
					$prey=array();
					foreach ($world->areas as $area)
						{
							foreach ($area->entities as $mob)
							{
								if($mob instanceof Mob)
								{
								switch ($mob->kind)
								{
								     case 4://goblin
								     if(isset($this->configuration->pp[1][0]))
								     {
								     	array_push($prey, 3);
								     }
								       if(isset($this->configuration->pp[1][1]))
								     {
								     	array_push($prey, 4);
								     }
								       if(isset($this->configuration->pp[1][2]))
								     {
								     	array_push($prey, 7);
								     }
								       if(isset($this->configuration->pp[1][3]))
								     {
								     	array_push($prey, 8);
								     }
								        if(isset($this->configuration->pp[1][4]))
								     {
								     	array_push($prey, 2);
								     }

								     	$world->checkDistance(10,$mob,$prey);
								         break;
								     case 3://skely
								             if(isset($this->configuration->pp[0][0]))
								     {
								     	array_push($prey, 3);
								     }
								       if(isset($this->configuration->pp[0][1]))
								     {
								     	array_push($prey, 4);
								     }
								       if(isset($this->configuration->pp[0][2]))
								     {
								     	array_push($prey, 7);
								     }
								       if(isset($this->configuration->pp[0][3]))
								     {
								     	array_push($prey, 8);
								     }
								        if(isset($this->configuration->pp[0][4]))
								     {
								     	array_push($prey, 2);
								     }

								     	$world->checkDistance(10,$mob,$prey);
								         break;
								     case 7://crabs
								            if(isset($this->configuration->pp[2][0]))
								     {
								     	array_push($prey, 3);
								     }
								       if(isset($this->configuration->pp[2][1]))
								     {
								     	array_push($prey, 4);
								     }
								       if(isset($this->configuration->pp[2][2]))
								     {
								     	array_push($prey, 7);
								     }
								       if(isset($this->configuration->pp[2][3]))
								     {
								     	array_push($prey, 8);
								     }
								        if(isset($this->configuration->pp[2][4]))
								     {
								     	array_push($prey, 2);
								     }

								     	$world->checkDistance(10,$mob,$prey);
								         break;
								       case 8://bats
								             if(isset($this->configuration->pp[3][0]))
								     {
								     	array_push($prey, 3);
								     }
								       if(isset($this->configuration->pp[3][1]))
								     {
								     	array_push($prey, 4);
								     }
								       if(isset($this->configuration->pp[3][2]))
								     {
								     	array_push($prey, 7);
								     }
								       if(isset($this->configuration->pp[3][3]))
								     {
								     	array_push($prey, 8);
								     }
								        if(isset($this->configuration->pp[3][4]))
								     {
								     	array_push($prey, 2);
								     }

								     	$world->checkDistance(10,$mob,$prey);
								         break;
								           case 2://rats
								             if(isset($this->configuration->pp[4][0]))
								     {
								     	array_push($prey, 3);
								     }
								       if(isset($this->configuration->pp[4][1]))
								     {
								     	array_push($prey, 4);
								     }
								       if(isset($this->configuration->pp[4][2]))
								     {
								     	array_push($prey, 7);
								     }
								       if(isset($this->configuration->pp[4][3]))
								     {
								     	array_push($prey, 8);
								     }
								        if(isset($this->configuration->pp[4][4]))
								     {
								     	array_push($prey, 2);
								     }

								     	$world->checkDistance(10,$mob,$prey);
								         break;
								}
								}
							}
						}
					


						// this makes the prey bleed after a set time
					
						foreach ($world->areas as $area)
						{
							foreach ($area->entities as $mob)
							{
								if($mob instanceof Mob)
								{
									if($mob->preybleedcount<$world->preybleedtime)
									{
										$mob->preybleedcount+=1;
									}
									else
									{
										if(($mob->kind==2) || ($mob->kind==3) || ($mob->kind==4) || ($mob->kind==7) || ($mob->kind==8))
										$mob->receiveDamage(2,$this->players[$from->resourceId]);
										if($mob->hitPoints<=0)
										{
											$world->handleBleedToDeath($mob,$this->players[$from->resourceId]);	//handle item drop and despawning
										}
										$mob->preybleedcount=0;	//resets counter
									}
								}
									
							}
						}
					
						//player active metrate die faster when moving 
					if($this->move)
					{
						switch ($this->configuration->playermetrate)
          				{
			                case 1:
			                   $world->updateCount+=1;
			                   break;
			                case 2:
			                    $world->updateCount+=2;
			                    break;
			                case 3:
			                    $world->updateCount+=3;
			                    break;
			                default:
			                   $world->updateCount+=3;                   
			            };
			            $this->move=false;
					}

					//player death rate
				if($world->updateCount < $world->regenCount) 
						{
							switch ($this->configuration->playerdeathrate)
          				{
			                case 1:
			                   $world->updateCount+=1;
			                   break;
			                case 2:
			                    $world->updateCount+=2;
			                    break;
			                case 3:
			                    $world->updateCount+=5;
			                    break;
			                default:
			                   $world->updateCount+=3;                   
			            };
						}	
						else 
						{		
							if(!isset($this->configuration->sim))	// if not a simulation make plpayer bleed 
							{
							$world->callbacks['BleedTick']($this->players[$from->resourceId]);
							
							
								
								//$world->bleed_callback();
							
							
							$world->updateCount = 0;
							}					
							
						}
					
					//handles killing from old age
					foreach ($world->areas as $area)// mobareas?
						{
							foreach ($area->entities as $entity ) 
							{
								if ($entity instanceof Mob)
								{
									if($entity->deathage==$world->deathtime)	
									{
										$entity->hitPoints=0;
										$world->dieofoldage($entity,$this->players[$from->resourceId]);
									}	
									else
									{
										$entity->deathage+=1;
									}							
								}
							}
							
						}
						
					//handles breeding
					if($world->breedCount < $world->breedTime)
					{
						$world->breedCount +=1;
					}
					else
					{
						foreach ($world->areas as $area)// mobareas?
						{
							foreach ($area->entities as $entity ) 
							{
								if ($entity instanceof Mob)
								{
									$chance=mt_rand(1,3);
									if ($chance==2)
									{
										
										$area->addToArea($area->_createMobInsideArea());
									}# code.
								}
								# code...
							}
							
						}
					
						$world->breedCount = 0;
					}
		
				}
				return;
			}
			
			echo "Received: $msg\n";
			
			$message = json_decode($msg);
			$action = intval($message[0]);
			
			if ( !FormatChecker::check($message) ) {
				echo "Invalid message format: $msg\n";
				$from->close();
				return;
			}
			
              // v- This exclamation point had me stuck for 4 months.
			if ( !$this->players[$from->resourceId]->hasEnteredGame && $action !== Types::Messages_HELLO ) {
				echo "Invalid handshake message: $msg.\n";
				$from->close();
				return;
			}
			
            // HELLO can be sent only once
			if ( $this->players[$from->resourceId]->hasEnteredGame && !$this->players[$from->resourceId]->isDead && $action === Types::Messages_HELLO) { 
				echo "Cannot initiate handshake twice: $msg.\n";
                $from->close();
                return;
            }
			
			$this->players[$from->resourceId]->resetTimeout();
			$handler = MessageHandlerFactory::generateMessageHandler(Types::messageHandlers($action), $this);	
			
			//if player is moving set move to true so they can bleed faster
			if($action==4)
			{
		    	$this->move=true;
			}
			$hp=($this->players[$from->resourceId]->hitPoints/$this->players[$from->resourceId]->maxHitPoints)*100;
				//it it is simulation mode or if the players health is to high to atk stop them from atking
				if(($action==8 &&  $hp>=$this->configuration->playerfoodlv) || isset($this->configuration->sim))
				{
					echo "hp is not low enough";
				}
				else
				{
					$handler($from, $message);
				}
				
			
            		
            
		}
		public function getStartTime()
		{
			return self::$startTime;
		}
		public function onClose(ConnectionInterface $conn) {

// I noticed the exit callback which actually decrements the player count and removed the player from the relevant datastructures was never being called. So I added the following three lines.
            if ( isset($this->players[$conn->resourceId]->callbacks['Exit']) ) {
                $this->players[$conn->resourceId]->callbacks['Exit']();
            }
            $this->clients->detach($conn);
            unset($this->players[$conn->resourceId]);

            echo "Connection {$conn->resourceId} has disconnected\n";
        }

		public function onError(ConnectionInterface $conn, \Exception $e) {
			echo "An error has occurred: {$e->getMessage()}\n";

			$conn->close();
		}
		
		public function getConnection($id) {
			foreach ($this->clients as $client) {
				if ($client->resourceId == $id) {
					return $client;
				}
			}
		}
		
		protected function getAvailableWorld() {
			foreach ($this->worlds as $world) {
				if ($world->playerCount < $this->configuration->numberOfPlayersPerWorld) {
					return $world;
				}
			}
		}
	}
?>
