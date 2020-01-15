<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class LootMove {
		protected $entity;
		protected $item;
		
		public function __construct($entity, $item) {
			$this->entity = $entity;
			$this->item = $item;
		}
		
		public function serialize() {
			return array(Types::Messages_LOOTMOVE, $this->entity->id, $this->item->id);
		}
	}
?>