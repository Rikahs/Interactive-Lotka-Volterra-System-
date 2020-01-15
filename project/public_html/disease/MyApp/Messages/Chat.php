<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Chat {
		protected $playerId;
		protected $message;
		
		public function __construct($player, $message) {
			$this->playerId = $player->id;
			$this->message = $message;
		}
		
		public function serialize() {
			return array(Types::Messages_CHAT, $this->playerId, $this->message);
		}
	}
?>