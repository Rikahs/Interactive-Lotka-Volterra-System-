<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Move {
		protected $entity;
		
		public function __construct($entity) {
			$this->entity = $entity;
		}
		
		public function serialize() {
			return array(Types::Messages_MOVE, $this->entity->id, $this->entity->x, $this->entity->y);
		}
	}
?>