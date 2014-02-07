<?php
namespace Spark\Bundle\WebBundle\Service;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use OldSound\RabbitMqBundle\RabbitMq\RpcClient;

class DMXPublisherService
{
    /**
     * @var RpcClient
     */
    private $dmxRPC;

    /**
     * Creates a new Olymp DMX service.
     *
     * @param Client $producer
     */
    public function __construct($dmxRPC)
    {
        $this->dmxRPC = $dmxRPC;
    }

    public function getChannels()
    {
        return $this->sendRPC("get_slots", array());
    }

    public function setChannelValue($channel, $value)
    {
        return $this->sendRPC(
            "set_channel_value",
            array(
                "channel" => $channel,
                "value" => $value
            )
        );
    }

    public function sendRPC($command, $parameters)
    {
        $request = array(
            "command" => $command,
            "parameters" => $parameters
        );

        $this->dmxRPC->addRequest(json_encode($request), "spark_service", uniqid());
        return $this->dmxRPC->getReplies();
    }
}