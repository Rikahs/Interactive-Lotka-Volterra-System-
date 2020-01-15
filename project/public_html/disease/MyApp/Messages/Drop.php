<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class Drop {
		protected $mob;
		protected $item;
		
		public function __construct($mob, $item) {
			$this->mob = $mob;
			$this->item = $item;
		}
		
		public function serialize() {
			$ids = array();
			foreach ($this->mob->hatelist as $hated) {
				array_push($ids, $hated->id);
			}
			return array(Types::Messages_DROP, $this->mob->id, $this->item->id, $this->item->kind, $ids);
		}
	}
?>