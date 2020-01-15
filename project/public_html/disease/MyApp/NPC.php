<?php
	namespace MyApp;
	use MyApp\Entity;
	
	class NPC extends Entity {		
		public function __construct($id, $kind, $x, $y) {
			parent::__construct($id, 'npc', $kind, $x, $y);
		}
	}
?>