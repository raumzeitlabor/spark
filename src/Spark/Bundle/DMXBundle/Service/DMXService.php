<?php


namespace Spark\Bundle\DMXBundle\Service;


use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Spark\Bundle\ClientBundle\Service\DMXClientService;
use Spark\Bundle\ClientBundle\Service\RDMClientService;

class DMXService
{
    private $clients = array();

    private $universe = 0;

    private $logger;

    /**
     * @var RDMClientService
     */
    private $rdmClientService;

    public function __construct(LoggerInterface $logger)
    {
        $this->rdmClientService = new RDMClientService($logger);
        $this->dmxClientService = new DMXClientService($logger);

        $this->rdmClientService->discovery();

        $this->logger = $logger;
    }

    public function execute($msg)
    {
        $data = json_decode($msg->body);

        switch ($data->command) {
            case "get_slots":
                $return = array("status" => "ok", "return" => $this->getSlots());
                break;
            case "set_channel_value":
                $this->setChannelValue($data->parameters->channel, $data->parameters->value);
                $return = array("status" => "ok");
                break;
            default:
                $return = array("status" => "error", "errorMessage" => "Invalid command");
                break;
        }

        return $return;
    }

    public function setChannelValue($channel, $value)
    {
        $this->syncSlotValues();

        if ($this->rdmClientService->hasDMXChannel($channel)) {
            $this->rdmClientService->setDMXChannelValue($channel, $value);
        }

        $this->send();
    }

    public function syncSlotValues()
    {
        $this->rdmClientService->updateDMXSlots();
        $this->send();

        usleep(100);
    }

    public function getSlots()
    {
        $dmxChannelMap = array();

        foreach ($this->rdmClientService->getClients() as $client) {
            $slots = $client->getSlotNames();

            foreach ($slots as $offset => $slotName) {
                $dmxChannelMap[] = array(
                    "slotName" => $slotName,
                    "device" => $client->getDeviceLabel(),
                    "address" => $client->getDMXStartAddress() + $offset
                );
            }
        }

        return $dmxChannelMap;
    }

    public function send()
    {
        for ($i = 1; $i < 513; $i++) {
            $dmxChannelMap[$i] = 0;
        }
        // Get all DMX and RDM client values
        foreach ($this->rdmClientService->getDMXValues() as $channel => $value) {
            $dmxChannelMap[$channel] = $value;
        }

        foreach ($this->dmxClientService->getDMXValues() as $channel => $value) {
            $dmxChannelMap[$channel] = $value;
        }

        $command = "ola_set_dmx -u " . escapeshellarg($this->universe) . " -d " . escapeshellarg(
                implode(",", $dmxChannelMap)
            );

        $this->logger->debug("Executing command " . $command);
        shell_exec($command);
    }
} 