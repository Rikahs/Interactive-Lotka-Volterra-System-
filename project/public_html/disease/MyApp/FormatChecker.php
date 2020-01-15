<?php
	namespace MyApp;
	use MyApp\Types;
	
	class FormatChecker {
		public static $formats = array(
			Types::Messages_HELLO => array('s','n','n'),
			Types::Messages_MOVE => array('n','n'),
			Types::Messages_LOOTMOVE => array('n','n','n'),
			Types::Messages_AGGRO => array('n'),
			Types::Messages_ATTACK => array('n'),
			Types::Messages_HIT => array('n'),
			Types::Messages_HURT => array('n'),
			Types::Messages_CHAT => array('s'),
			Types::Messages_LOOT => array('n'),
			Types::Messages_TELEPORT => array('n','n'),
			Types::Messages_ZONE => array(),
			Types::Messages_OPEN => array('n'),
			Types::Messages_CHECK => array('n')
		);
		
		public static function check($msg) {
			$type = $msg[0];
			array_shift($msg);
			
			if ($type === Types::Messages_WHO) {
				for ($i = 0; $i < count($msg); $i++) {
					if ( !is_numeric($msg[$i]) ) {
						return false;
					}
				}
				return true;
			}
			
			$format = self::$formats[$type];
				
			if ( is_array($format) ) {
				if ( count($msg) !== count($format) ) {
					return false;
				}
				
				for ($i = 0; $i < count($msg); $i++) {
					if ( $format[$i] === 'n' && !is_numeric($msg[$i]) ) {
						return false;
					}
					if ( $format[$i] === 's' && !is_string($msg[$i]) ) {
						return false;
					}
				}
				return true;
			} else {
				echo "Unknown message type: $type\n";
				return false;
			}
		}
	}
?>