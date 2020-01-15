<?php
	namespace MyApp;
	
	class Entity {
		public $id;
		public $type;
		public $kind;
		public $x;
		public $y;
		
		public $area;
		
		public $diseaseState;//Susceptible,Infected,Recovered, Exposed,Pass from mother
		public $diseasesContracted;
		public $diseases = array();
		
		public $group;
		public $recentlyLeftGroups = array();
		public $ticksSinceInfection;
		
		
		public function __construct($id, $type, $kind, $x, $y) {
			$this->id = $id;
			$this->type = $type;
			$this->kind = $kind;
			$this->x = $x;
			$this->y = $y;
			$this->diseaseState = Types::DiseaseState_SUSCEPTIBLE;
			$this->diseasesContracted =0;
			$this->ticksSinceInfection = 0;
			
		}
		
		public function destroy() {
			// Actually is empty
		}
		
		public function _getBaseState() {
			return array(intval($this->id), $this->kind, $this->x, $this->y);
		}
		
		public function getState() {
			return $this->_getBaseState();
		}
		
		public function spawn() {
			return new Messages\Spawn($this);
		}
		
		public function despawn() {
			return new Messages\Despawn($this->id);
		}
		public function setDiseaseState($state)
		{
			
			$this->diseaseState=$state;
			
		}
		public function getDiseaseState(){
			return  $this->diseaseState;
		}
		public function setPosition($x, $y) {
			$this->x = $x;
			$this->y = $y;
		}
		
		public function getPositionNextTo($entity) {
			$pos = null;
			if($entity) {
				$pos = new \StdClass();
				// This is a quick & dirty way to give mobs a random position
				// close to another entity.
				$r = Utils::random(4);
				
				$pos->x = $entity->x;
				$pos->y = $entity->y;
				if($r === 0)
					$pos->y -= 1;
				if($r === 1)
					$pos->y += 1;
				if($r === 2)
					$pos->x -= 1;
				if($r === 3)
					$pos->x += 1;
			}
			return $pos;
		}
		
		/*static public function adjacentPlayer($otherPlayer)
		{

			$otherPlayer->x;
			$otherPlayer->y;
			
			if($otherPlayer->x === $this->x){
				if(($otherPlayer->y === ($this->y - 1))||($otherPlayer->y === ($this->y + 1))){
					return true;}
			}
			else if ($otherPlayer->y === $this->y){
				if(($otherPlayer->x === ($this->x - 1))||($otherPlayer->x === ($this->x + 1))){
					return true;}
			}
			else{
				return false;}
		}*/		
		
	}
?>
