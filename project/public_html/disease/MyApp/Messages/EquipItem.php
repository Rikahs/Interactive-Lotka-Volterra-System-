<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class EquipItem {
		protected $playerId;
		protected $itemKind;
		
		public function __construct($player, $itemKind) {
			$this->playerId = $player->id;
			$this->itemKind = $itemKind;
		}
		
		public function serialize() {
			return array(Types::Messages_EQUIP, $this->playerId, $this->itemKind);
		}
	}
?>