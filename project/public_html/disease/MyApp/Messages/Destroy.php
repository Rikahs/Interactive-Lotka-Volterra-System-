<?php
	namespace MyApp\Messages;
	
	class Destroy {
		protected $entity;
		
		public function __construct($entity) {
			$this->entity = $entity;
		}
		
		public function serialize() {
			return array(Types::Messages_DESTROY, $this->entity->id);
		}
	}
?>