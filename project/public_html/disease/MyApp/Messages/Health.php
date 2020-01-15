<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Health {
		protected $points;
		protected $isRegen;
		
		//JTG08042015
		protected $isBleed;
		protected $diseaseState;
		
		
		
		public function __construct($points, $isRegen,$isBleed,$diseaseState) {
			$this->points = $points;
			$this->isRegen = $isRegen;
			$this->isbleed = $isBleed;
			$this->diseaseState = $diseaseState;
		}
		
		public function serialize() {
			$health = array(Types::Messages_HEALTH, $this->points);
			if ($this->isRegen) {
				array_push($health, 1);
			}
			return $health;
		}
	}
?>
