<?php
	namespace MyApp;
	use MyApp\Types;
	
	class Utils {
		/**
		 * @todo Actually sanitise the string
		 */
		public static function sanitize($string) {
			return $string;
		}
		
		public static function random($range) {
			return mt_rand(0, $range - 1);
		}
		
		public static function randomInt($min, $max) {
			return mt_rand($min, $max);
		}
		
		public static function randomOrientation() {
			$random = mt_rand() % 4;
			switch ($random) {
				case 0: return Types::Orientations_LEFT; break;
				case 1: return Types::Orientations_RIGHT; break;
				case 2: return Types::Orientations_UP; break;
				case 3: return Types::Orientations_DOWN; break;
			}
		}
		
		public static function distanceTo($x, $y, $x2, $y2) {
			$distX = abs($x - $x2);
			$distY = abs($y - $y2);
			
			// This is how they did it. This isn't even manhattan distance.
			return ($distX > $distY) ? $distX : $distY;
		}
	}
?>