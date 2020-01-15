<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Blink {
		protected $item;
		
		public function __construct($item) {
			$this->item = $item;
		}
		
		public function serialize() {
			return array(Types::Messages_BLINK, $this->item->id);
		}
	}
?>