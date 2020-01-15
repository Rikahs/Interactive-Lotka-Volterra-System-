<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Exposed {
		protected $victimId;
		protected $disease;//must get incubation period from this and ...
		
		public function __construct($sourceId, $targetId) {
			$this->victimId = $victimId;
			$this->disease = $disease;
		}
		
		public function serialize() {
			return array(Types::Messages_Exposed, $this->victimId, $this->disease);
		}
	}
	

?>
