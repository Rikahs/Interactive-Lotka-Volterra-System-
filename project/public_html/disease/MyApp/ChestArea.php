<?php
	namespace MyApp;
	
	class ChestArea extends Area {
		public $items;
		public $chestX;
		public $chestY;
		
		public function __construct($id, $x, $y, $width, $height, $cx, $cy, $items, $world) {
			parent::__construct($id, $x, $y, $width, $height, $world);
			$this->items = $items;
			$this->chestX = $cx;
			$this->chestY = $cy;
		}
		
		public function contains($entity) {
			if ($entity) {
				return ($entity->x >= $this->x && $entity->y >= $this->y && $entity->x < $this->x + $this->width && $entity->y < $this->y + $this->height);
			} else {
				return false;
			}
		}
	}
?>