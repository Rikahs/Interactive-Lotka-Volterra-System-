<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Damage {
		protected $entity;
		protected $points;
		
		public function __construct($entity, $points) {
			$this->entity = $entity;
			$this->points = $points;
		}
		
		public function serialize() {
			return array(Types::Messages_DAMAGE, $this->entity->id, $this->points);
		}
	}
?>