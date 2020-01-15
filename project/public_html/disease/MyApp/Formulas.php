<?php
	namespace MyApp;
	
	class Formulas {
		public static function dmg($weaponLevel, $armorLevel) {
			$dealt = $weaponLevel * Utils::randomInt(5, 10);//make these variable be read from a file so that they can be reduced dynamically
			$absorbed = $armorLevel * Utils::randomInt(1, 3);
			$dmg = $dealt - $absorbed;
			
			echo "abs: " . $absorbed . "   dealt: " . $dealt . "   dmg: " . $dmg . "\n";
			if($dmg <= 0) {
				return Utils::randomInt(0, 3);
			} else {
				return $dmg;
			}
		}
		
		public static function hp($armorLevel) {
			$hp = 80 + ( ($armorLevel - 1) * 30);
			return $hp;
		}
	}
?>


