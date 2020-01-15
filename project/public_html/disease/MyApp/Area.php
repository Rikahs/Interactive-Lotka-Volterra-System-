<?php
	namespace MyApp;
	
	class Area {
		protected $id;
		protected $x;
		protected $y;
		protected $width;
		protected $height;
		protected $world;
		public $entities;
		protected $hasCompletelyRespawned;
		protected $nbEntities;
		
		protected $empty_callback;
		
		public function __construct($id, $x, $y, $width, $height, $world) {
			$this->id = $id;
			$this->x = $x;
			$this->y = $y;
			$this->width = $width;
			$this->height = $height;
			$this->world = $world;
			$this->entities = array();
			$this->hasCompletelyRespawned = true;
		}
		
		public function _getRandomPositionInsideArea() {
			$pos = new \StdClass();
			$valid = false;
			
			while (!$valid) {
				$pos->x = $this->x + mt_rand(0, $this->width + 1);
				$pos->y = $this->y + mt_rand(0, $this->height + 1);
				$valid = $this->world->isValidPosition($pos->x, $pos->y);
			}
			
			return $pos;
		}
		
		public function removeFromArea($entity) {
			$ids = array();
			foreach ($this->entities as $obj) {
				array_push($ids, $obj->id);
			}
			$i = array_search($entity->id, $ids);
			array_splice($this->entities, $i, 1);
			
			if($this->isEmpty() && $this->hasCompletelyRespawned && $this->empty_callback) {
				$this->hasCompletelyRespawned = false;
				$this->empty_callback();
			}
		}
		
		public function addToArea($entity) {
			if ($entity) {
				array_push($this->entities, $entity);
				//$this->entities[] = $entity;
				$entity->area = $this;
				if ($entity instanceof Mob) {
					$this->world->addMob($entity);
				}
			}
		}
		
		public function setNumberOfEntities($nb) {
			$this->nbEntities = $nb;
		}
		
		public function isEmpty() {
			foreach ($this->entities as $entity) {
				if (!$entity->isDead) {
					return false;
				}
			}
			return true;
		}
		
		public function isFull() {
			return !$this->isEmpty() && ($this->nbEntities === count($this->entities));
		}
		
		public function onEmpty($callback) {
			$this->empty_callback = $callback;
		}
		
		// needed to get callbacks to work from within an object
		public function __call($function, $arguments) {
			$callbacks = array('empty_callback');
			if ( in_array($function, $callbacks) ) {
				call_user_func_array($this->$function, $arguments);
			} else {
				trigger_error("No Area::$function Method.",E_USER_ERROR);
			}
		}
	}
?>