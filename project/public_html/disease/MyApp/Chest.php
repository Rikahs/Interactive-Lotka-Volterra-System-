<?php
	namespace MyApp;
	
	class Chest extends Item {
		protected $items;
		
		public function __construct($id, $x, $y) {
			parent::__construct($id, Types::Entities_CHEST, $x, $y);
		}
		
		public function setItems($items) {
			$this->items = $items;
		}
		
		public function getRandomItem() {
			$nbItems = count($this->items);
            $item = null;

			if ($nbItems > 0) {
				$item = $this->items[Utils::random($nbItems)];
			}
			return $item;
		}
	}
?>