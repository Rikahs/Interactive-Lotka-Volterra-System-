<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class HitPoints {
		protected $maxHitPoints;
		
		public function __construct($maxHitPoints) {
			$this->maxHitPoints = $maxHitPoints;
		}	
		
		public function serialize() {
			return array(Types::Messages_HP, $this->maxHitPoints);
		}		
	}
?>