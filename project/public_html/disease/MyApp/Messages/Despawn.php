<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Despawn {
		protected $entityId;
		
		public function __construct($entityId) {
			$this->entityId = $entityId;
		}
		
		public function serialize() {
			return array(Types::Messages_DESPAWN, $this->entityId);
		}
	}
?>