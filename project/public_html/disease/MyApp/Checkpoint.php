<?php
	namespace MyApp;
	
	class Checkpoint {
		public $id;
		protected $x;
		protected $y;
		protected $width;
		protected $height;
		
		public function __construct($id, $x, $y, $width, $height) {
			$this->id = $id;
			$this->x = $x;
			$this->y = $y;
			$this->width = $width;
			$this->height = $height;
		}
		
		public function getRandomPosition() {
			$pos = new \StdClass();
			$pos->x = $this->x + mt_rand(0, $this->width-1);
			$pos->y = $this->y + mt_rand(0, $this->height-1);
			return $pos;
		}
	}
?>