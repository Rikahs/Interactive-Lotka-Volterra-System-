<?php
	namespace MyApp;
	
	class Item extends Entity {
		public $isStatic;
		public $isFromChest;
		
		protected $blinkTimeout;
		protected $despawnTimeout;
		
		protected $respawn_callback;
		
		public function __construct($id, $kind, $x, $y) {
			parent::__construct($id, 'item', $kind, $x, $y);
			$this->isStatic = false;
			$this->isFromChest = false;
		}
		
		public function handleDespawn($params) {
			$self = $this;
			// More SetTimeout I cannot do.
			//$this->blinkTimeout = setTimeout( function() use ($self, $params) {
			//	$params->blinkCallback();
			//	$self->despawnTimeout = setTimeout($params->despawnCallback, $params->blinkingDuration);
			//}, $params->beforeBlinkDelay);
		}
		
		public function destroy() {
			echo "ToDo: Item::destroy\n";
			if($this->blinkTimeout) {
				// clearTimeout($this->blinkTimeout);
			}
			if($this->despawnTimeout) {
				// clearTimeout($this->despawnTimeout);
			}
			
			if($this->isStatic) {
				$this->scheduleRespawn(30000);
			}
		}
		
		public function scheduleRespawn($delay) {
			echo "ToDo: Item::scheduleRespawn\n";
			$self = $this;
			// setTimeout(function() {
				// if(self.respawn_callback) {
					// self.respawn_callback();
				// }
			// }, delay);
		}
		
		public function onRespawn($callback) {
			$this->respawn_callback = $callback;
		}
		
		// needed to get callbacks to work from within an object
		public function __call($function, $arguments) {
			$callbacks = array('respawn_callback');
			if ( in_array($function, $callbacks) ) {
				call_user_func_array($this->$function, $arguments);
			} else {
				//trigger_error("No Item::$function Method.",E_USER_ERROR);
			}
		}
	}
?>