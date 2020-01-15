<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Recover {
		protected $victimId;
		protected $disease;//must get incubation period from this and ...
		
		public function __construct($victim) {
			$this->victimId = $victim->id;
			$this->disease = $victim->diseases;
		}
		
		public function serialize() {
			return array(Types::Messages_Recover, $this->victimId, $this->disease);
		}
	}
	

?>
