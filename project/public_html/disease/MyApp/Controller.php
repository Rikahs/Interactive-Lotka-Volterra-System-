<?php
namespace ya\System;

use ya\System\WsMessageHandlers\WsMessageHandlerAbstract;
use ya\System\WsMessageHandlers\WsMessageHandlerArrayIterator;
use stdClass;

/**
 * @author Alexander Patrick
 * @version 1.0
 * @created 19-Jun-2014 12:10:35 AM
 */
class Controller extends SystemModule
{
    /**
     * @var array The observers for the controller
     */
    private $webSocketMessageHandlers = array();

    /**
     * Attach all observers
     */
    public function _construct(WsMessageHandlerArrayIterator $observers) {

        // attach default set of message handlers
       foreach ($observers as $msgHandler) {
           $this->attachWsMessageHandler($msgHandler);
           $this->log('Message handler attached ' . $msgHandler->getMessageHandlerName() . "\n");
       }
    }


    /**
     * Add a message handler to the observer list
     * @param WsMessageHandlerAbstract $handler
     */
    protected function attachWsMessageHandler(WsMessageHandlerAbstract $handler)
    {
        $this->webSocketMessageHandlers[$handler->getMessageHandlerType()] = $handler;
    }


    /**
     * Remove a message handler from the observer list
     * @param WsMessageHandlerAbstract $handler
     */
    protected function detachWsMessageHandler(WsMessageHandlerAbstract $handler)
    {
       if (isset($this->webSocketMessageHandlers[$handler->getMessageHandlerType()])) {
            unset($this->webSocketMessageHandlers[$handler->getMessageHandlerType()]);
        }
    }

    /**
     * Tell all observers of a certain type that a message has been received for handling. This
     * notification assumes only one handler for each type of message.
     * @param Client $client
     * @param \StdClass $message
     * @return bool
     */
    protected function notifyWebSocketMessageHandlers(Client $client, $message)
    {
        if (is_array($this->webSocketMessageHandlers) && !empty($this->webSocketMessageHandlers)) {
            if (isset($this->webSocketMessageHandlers[$message[0]])) {
                $this->webSocketMessageHandlers[$message[0]]->update($client, $message);
            }
            else {
                 $this->log('No message handler exists for this type of message ' . $message[0]);
                //trigger_error('No message handler exists for this type of message', E_USER_ERROR);
                return false;
            }
        }
        else {
           // $this->log('Invalid collection of message handlers received');
            trigger_error('Invalid collection of message handlers received', E_USER_ERROR);
            return false;
        }
        return true;
    }


    /**
     * Receives and parses the messages from the ClientMessenger to ensure there are
     * correct commands. Our server will use its own Websocket message object eventually
     * so the handler must wrap whatever message it receives into that object. For BrowserQuest
     * it takes the array and wraps it as appropriate
     *
     * @param $client
     * @param $message
     */
    public function handleMessage($client, $message) {
        $this->notifyWebSocketMessageHandlers($client, $message);
    }

    /**
     * Tells the client to go ahead and send a message if necessary - as is the case for BrowserQuest
     */
    public function init($client=null, $doInit = false) {
        if (!$doInit) {
            return true;
        }
        else {
            // BrowswerQuest requires a 'go' message from the server before it actually
            // considers the connection established, even though it already is
            $client->send('go');
            $this->log('Initialization message sent');
        }
    }

}
?>