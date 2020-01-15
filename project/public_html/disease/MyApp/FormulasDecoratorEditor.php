<?php
	namespace MyApp;

	//EDITS DAMAGE OUTPUT
	class FormulasDecoratorEditor {
		private $damgEdit;
		// public function _constructor(int $damgEdit_in){ // the constructor is __construct
		public function __construct(int $damgEdit_in){ 
		$player = $this->main->players[$from->resourceId];		
			// $damgEdit = $damgEdit_in; // Accessing any class variables requires using $this
			$this->damgEdit = $damgEdit_in; 
		}

		public static function EditOutput($dmg) { // I am not sure if this is what you were thinking of.
			$damgEdit = $dmg / 2;
			echo "DAMAGE DECREASED DUE TO DISEASE. \n";
			return $damgEdit;}
	}
?>