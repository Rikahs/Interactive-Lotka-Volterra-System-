<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Infect {
		protected $sourceId;
		protected $targetId;
		
		public function __construct($source) {
			$this->sourceId = $source->id;
			$this->targetId = $source->diseaseTarget;
		}
		
		public function serialize() {
			return array(Types::Messages_Infect, $this->sourceId, $this->targetId);
		}
	}
	

?>
