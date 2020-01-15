<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Population {
		protected $world;
		protected $total;
		
		public function __construct($world, $total) {
			$this->world = $world;
			$this->total = $total;
		}
		
		public function serialize() {
			return array(Types::Messages_POPULATION, $this->world, $this->total);
		}
	}
?>