<?php
    namespace MyApp;
	use MyApp\Types;	
    
    class MessageHandlerFactory {
        public static function generateMessageHandler($type, $data) {
            if (!class_exists("MyApp\\MessageHandlers\\$type") ) {
                trigger_error("$type is not a class", E_USER_ERROR);
                return null;
            }
            
            $reflection = new \ReflectionClass("MyApp\\MessageHandlers\\$type");
            if ( !$reflection->isSubclassOf('MyApp\\MessageHandlers\\MessageHandlerAbstract') ) {
                trigger_error("$type must extend MessageHandlerAbstract", E_USER_ERROR);
                return null;
            }
            
            return $reflection->newInstance($data);
        }
    }
?>