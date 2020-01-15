<?php
    namespace MyApp\MessageHandlers;
    use Ratchet\ConnectionInterface;
    
    /**
     * 
     */
    abstract class MessageHandlerAbstract {
        protected $main;
        public function __construct($main) {
            $this->main = $main;
        }
        abstract public function __invoke(ConnectionInterface $from, array $message);   
    }
    
?>