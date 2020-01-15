<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Spawn {
		protected $entity;
		
		public function __construct($entity) {
			$this->entity = $entity;
		}
		
		public function serialize() {
			$spawn = array(Types::Messages_SPAWN);
			$spawn = array_merge($spawn, $this->entity->getState());
			return $spawn;
		}
	}
?>