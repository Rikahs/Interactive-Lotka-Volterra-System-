<?php
	namespace MyApp;
	// FormulasDecorator.php // Each class should be in a separate file
	//COPIES DAMAGE OUTPUT
	class FormulasDecorator  {
		protected $damg; 
		// public function _constructor (int $formulas_in){ // the constructor is __construct
		public function __construct(int $formulas_in) {
			$this->damg = $formulas_in;
		}
	}
?>