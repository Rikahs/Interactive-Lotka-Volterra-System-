<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Bite {
		protected $biterId;
		protected $targetId;
		
		public function __construct($biter) {
			$this->biterId = $biter->id;
			$this->targetId = $biter->diseaseTarget;
		}
		
		public function serialize() {
			return array(Types::Messages_Bite, $this->biterId, $this->targetId);
		}
	}
	

?>
