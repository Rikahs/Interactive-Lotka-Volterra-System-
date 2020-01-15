<?php
	namespace MyApp;
	use MyApp\Entity;
	use MyApp\Utils;
	
	class Character extends Entity {
		protected $orientation;
		public $attackers;
		public $target;
		public $diseaseTarget;
		
		public $maxHitPoints;
		public $hitPoints;
		public $full;
		
		public $statusEffects; //slow or weakness(lower strength) for chikungunya,bleed for dengue
		
		
		public function __construct($id, $type, $kind, $x, $y) {
			parent::__construct($id, $type, $kind, $x, $y);
			
			$this->orientation = Utils::randomOrientation();
			$this->attackers = array();
			$this->target = null;
			$this->statusEffects = array();
		}
		
		public function getState() {
			$basestate = $this->_getBaseState();
			$state = array();
			array_push($state, $this->orientation);
			if ($this->target) {
				array_push($state, $this->target);
			}
			
			return array_merge($basestate, $state);
		}
		public function isInfected()
		{
			if($this->diseaseState == 1)
			return true;
			else
			return false;
		}
		
			public function resetHitPoints1($maxHitPoints) {
			//$this->maxHitPoints = $maxHitPoints;
			if($maxHitPoints>$this->maxHitPoints)
			{
				$this->hitPoints = $this->maxHitPoints;
			}
			else
			{
				$this->hitPoints=$maxHitPoints;
			}
		}

		public function resetHitPoints($maxHitPoints) {
			$this->maxHitPoints = $maxHitPoints;
			$this->hitPoints = $this->maxHitPoints;
		}
		
		public function regenHealthBy($value) {
			$hp = $this->hitPoints;
			$max = $this->maxHitPoints;
			
			if ($hp < $max) {
				if ($hp + $value <= $max) {
					$this->hitPoints += $value;
				} else {
					$this->hitPoints = $max;
				}
			}
		}
		
		public function decreaseHealthBy($value) {
			$hp = $this->hitPoints;
			$min = 1;
			
			
				
					$this->hitPoints -= $value;
			
		}
				
			
		
		
		public function hasFullHealth() {
			$max = $this->maxHitPoints;
			if($this->hitPoints==$max)
			{
				
				return true;
			}
			else
			{
				return false;
			}
		}
		
		public function setTarget($entity) {
			$this->target = $entity->id;
		}
		public function setDiseaseTarget($entity) {
			$this->diseaseTarget = $entity;
		}
		public function clearDiseaseTarget() {
			$this->diseaseTarget = null;
		}
		
		public function clearTarget() {
			$this->target = null;
		}
		
		public function hasTarget() {
			return $this->target !== null;
		}
		
		public function attack() {
			return new Messages\Attack($this->id, $this->target);
		}
		public function bite() {
			echo "BITE Message Sent";
				return new Messages\Bite($this);
		}
		
		public function infect() {
				echo "INFECT Message Sent";
				return new Messages\Infect($this);
		}
		public function health() {
			return new Messages\Health($this->hitPoints, false, false, 0);
		}
		
		public function regen() {
			return new Messages\Health($this->hitPoints, true,false,0);
		}
		
		public function bleed() {
			return new Messages\Health($this->hitPoints,false,true,1);
		}
		
		public function addAttacker($entity) {
			if($entity) {
				$this->attackers[$entity->id] = $entity;
			}
		}
		
		public function removeAttacker($entity) {
			if( $entity && array_key_exists($entity->id, $this->attackers) ) {
				unset($this->attackers[$entity->id]);
				echo $this->id . ' REMOVED ATTACKER ' . $entity->id . "\n";
			}
		}
		
		public function forEachAttacker($callback) {
			foreach ($this->attackers as $id=>&$attacker) {
				$callback($attacker);
			}
			unset($attacker);
		}
		
		public function getOrientation()
		{
			return $this->orientation;
		}
		
		public function setOrientation($orientation)
		{
			return $this->orientation=$orientation; 
		}
	}
?>
