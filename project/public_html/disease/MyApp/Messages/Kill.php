<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Kill {
		protected $mob;
		
		public function __construct($mob) {
			$this->mob = $mob;
		}
		
		public function serialize() {
			return array(Types::Messages_KILL, $this->mob->kind);
		}
	}
?>