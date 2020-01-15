<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Attack {
		protected $attackerId;
		protected $targetId;
		
		public function __construct($attackerId, $targetId) {
			$this->attackerId = $attackerId;
			$this->targetId = $targetId;
		}
		
		public function serialize() {
			return array(Types::Messages_ATTACK, $this->attackerId, $this->targetId);
		}
	}
	
	//WHAT DETERMINE THE AMOUNT OF HITPOINTS IS DEDUCTED FROM THE TARGET
	//I WOULD LIKE TO REDUCE THE HITPOINTS BY 40% IF THE PLAYER IS POISONED
	//MAY BE BETTER TO ADD DECORATOR TO THE DAMAGE MESSAGE CLASS
?>