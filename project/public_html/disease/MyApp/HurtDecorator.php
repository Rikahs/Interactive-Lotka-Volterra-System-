<?php
	namespace MyApp;

	//EDITS DAMAGE OUTPUT
	class HurtDecorator extends FormulasDecorator{

		private $hurting;

		public function __construct(int $hurt_in){ 
			$this->hurting = $hurt; 
		}

		public static function TriggerHurt() { // I am not sure if this is what you were thinking of.
			$hurting = 15;
			return $hurting;
		}
	}
?>