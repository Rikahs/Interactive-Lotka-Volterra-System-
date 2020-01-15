<?php
	namespace MyApp\Messages;
	use MyApp\Types;
	
	class EntityList{
		protected $ids;
		
		public function __construct($ids) {
			$this->ids = $ids;
		}
		
		public function serialize() {
			$list = $this->ids;
			
			array_unshift($list, Types::Messages_LIST);
			return $list;
		}
	}
?>