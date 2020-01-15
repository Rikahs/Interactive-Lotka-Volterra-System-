<?php
	namespace MyApp;
	use MyApp\Checkpoint;
	
	class Map {
		protected $width;
		protected $height;
		protected $collisions;
		public $mobAreas;
		public $chestAreas;
		public $staticChests;
		public $staticEntities;
		protected $isLoaded;
		
		protected $zoneWidth;
		protected $zoneHeight;
		protected $groupWidth;
		protected $groupHeight;
		
		protected $ready_func;
		
		protected $grid;
		
		protected $connectedGroups;
		protected $checkpoints;
		protected $startingAreas;
		
		public function __construct() {
			$this->isLoaded = false;
		}
		
		public function initMap($map) {
			$this->width = $map->width;
			$this->height = $map->height;
			$this->collisions = $map->collisions;
			$this->mobAreas = $map->roamingAreas;
			//
			//$this->chestAreas = $map->chestAreas;
			//$this->staticChests = $map->staticChests;
			$this->staticEntities = $map->staticEntities;
			$this->isLoaded = true;
			
			$this->zoneWidth = 28;
			$this->zoneHeight = 12;
			$this->groupWidth = floor($this->width / $this->zoneWidth);
			$this->groupHeight = floor($this->height / $this->zoneHeight);
			
			$this->initConnectedGroups($map->doors);
			$this->initCheckpoints($map->checkpoints);
		
			if ($this->ready_func) {
				$this->ready_func();
			}
		}
		
		public function ready($f) {
			$this->ready_func = $f;
		}
		
		public function tileIndexToGridPosition($tileNum) {
			$x = 0;
			$y = 0;
			$getX = function ($num, $w) {
				if ($num == 0) {
					return 0;
				}
				return ($num % $w == 0) ? $w - 1 : ($num % $w) - 1;
			};
			
			$tileNum -= 1;
			$x = $getX($tileNum + 1, $this->width);
			$y = floor($tileNum / $this->width);
			
			$result = new \StdClass();
			$result->x = $x;
			$result->y = $y;
			return $result;
		}
		
		public function gridPositionToTileIndex($x, $y) {
			return ($y * $this->width) + $x + 1;
		}
		
		/**
		 * Goes through each tile of the map and checks if that tileIndex exists in the collisions array. 
		 * O( (nxm)^2 ) time complexity.
		 * The BrowserQuest Map is 172x314=54,008 tiles.
		 */
		public function generateCollisionGrid() {
			echo "Generating Collision Grid. Please Hold...\n";
			$this->grid = array();
			if ($this->isLoaded) {
				$tileIndex = 0;
				for ($i = 0; $i < $this->height; $i++) {
					$this->grid[$i] = array();
					for ($j = 0; $j < $this->width; $j++) {
						if ( in_array($tileIndex, $this->collisions) ) {
							$this->grid[$i][$j] = 1;
						} else {
							$this->grid[$i][$j] = 0;
						}
						$tileIndex += 1;
					}
				}
				echo "Collision grid generated.\n";
			}
		}
		
		public function isOutOfBounds($x, $y) {
			return $x <= 0 || $x >= $this->width || $y <= 0 || $y >= $this->height;
		}
		
		public function isColliding($x, $y) {
			if ( $this->isOutOfBounds($x, $y) ) {
				return false;
			}
			return $this->grid[$y][$x] === 1;
		}
		
		public function groupIdToGroupPosition($id) {
			$posArray = explode('-', $id);
			
			$pos = new \StdClass();
			$pos->x = intval($posArray[0]);
			$pos->y = intval($posArray[1]);
			
			return $pos;
		}
		
		public function forEachGroup($callback) {
			$width = $this->groupWidth;
			$height = $this->groupHeight;
			
			for ($x = 0; $x < $width; $x += 1) {
				for ($y = 0; $y < $height; $y += 1) {
					$callback($x . '-' . $y);
				}
			}
		}
		
		public function getGroupIdFromPosition($x, $y) {
			$w = $this->zoneWidth;
			$h = $this->zoneHeight;
			// $gx = (int)(floor($x - 1) / $w);
			// $gy = (int)(floor($y - 1) / $h);
			$gx = floor( ($x - 1) / $w );
			$gy = floor( ($y - 1) / $h );
			
			return "$gx-$gy";
		}
		
		public function getAdjacentGroupPositions($id) {
			$position = $this->groupIdToGroupPosition($id);
			$x = $position->x;
			$y = $position->y;
			
			$list = array(
				self::pos($x-1, $y-1), self::pos($x, $y-1), self::pos($x+1, $y-1),
				self::pos($x-1, $y),   self::pos($x, $y),   self::pos($x+1, $y),
				self::pos($x-1, $y+1), self::pos($x, $y+1), self::pos($x+1, $y+1),
			);
			
			if (array_key_exists($id, $this->connectedGroups)) {
				foreach ($this->connectedGroups[$id] as $position) {
					$any = false;
					foreach ($list as $groupPos) {
						if ( self::equalPositions($groupPos, $position) ) {
							$any = true;
						}
						if ($any) {
							break;
						}
					}
					if (!$any) {
						array_push($list, $position);
					}
				}
			}
			
			$adjacentGroupPositions = array();
			foreach ($list as $pos) {
				if ($pos->x >= 0 && $pos->y >= 0 && $pos->x < $this->groupWidth && $pos->y < $this->groupHeight) {
					$adjacentGroupPositions[] = $pos;
				}
			}
			
			return $adjacentGroupPositions;
		}
		
		public function forEachAdjacentGroup($groupId, $callback) {
			if ($groupId) {
				foreach ($this->getAdjacentGroupPositions($groupId) as $pos) {
					$callback($pos->x . '-' . $pos->y);
				}
			}
		}
		
		public function initConnectedGroups($doors) {
			$this->connectedGroups = array();
			foreach ($doors as $door) {
				$groupId = $this->getGroupIdFromPosition($door->x, $door->y);
				$connectedGroupId = $this->getGroupIdFromPosition($door->tx, $door->ty);
				$connectedPosition = $this->groupIdToGroupPosition($connectedGroupId);
				
				if ( array_key_exists($groupId, $this->connectedGroups) ) {
					array_push($this->connectedGroups[$groupId], $connectedPosition);
				} else {
					$this->connectedGroups[$groupId] = array($connectedPosition);
				}
			}
		}
		
		public function initCheckpoints($checkpointList) {
			$this->checkpoints = array();
			$this->startingAreas = array();
			
			foreach ($checkpointList as $cp) {
				$checkpoint = new CheckPoint($cp->id, $cp->x, $cp->y, $cp->w, $cp->h);
				$this->checkpoints[$checkpoint->id] = $checkpoint;
				if ($cp->s === 1) {
					array_push($this->startingAreas, $checkpoint);
				}
			}
		}
		
		public function getCheckpoint($id) {
			return $this->checkpoints[$id];
		}
		
		public function getRandomStartingPosition() {
			$nbAreas = count($this->startingAreas);
			$i = mt_rand(0, $nbAreas-1);
			$area = $this->startingAreas[$i];
			$startPos = $area->getRandomPosition();
			return $startPos;
		}
		
		public static function pos($x, $y) {
			$pos = new \StdClass();
			$pos->x = $x;
			$pos->y = $y;
			return $pos;
		}
		
		public static function equalPositions($pos1, $pos2) {
			return $pos1->x === $pos2->x && $pos1->y === $pos2->y;
		}
		
		// needed to get callbacks to work from within an object
		public function __call($function, $arguments) {
			$callbacks = array('ready_func');
			if ( in_array($function, $callbacks) ) {
				call_user_func_array($this->$function, $arguments);
			}			
		}
	}
?>
