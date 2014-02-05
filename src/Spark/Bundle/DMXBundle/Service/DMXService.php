<?php


namespace Spark\Bundle\DMXBundle\Service;


use Psr\Log\LoggerInterface;
use Spark\Bundle\ClientBundle\Service\DMXClientService;
use Spark\Bundle\ClientBundle\Service\RDMClientService;

class DMXService {
    private $clients = array();

    private $universe = 0;

    private $logger;

    /**
     * @var RDMClientService
     */
    private $rdmClientService;

    public function __construct (LoggerInterface $logger) {
        $this->rdmClientService = new RDMClientService($logger);
        $this->dmxClientService = new DMXClientService($logger);

        $this->rdmClientService->discovery();

        $this->logger = $logger;
    }

    public function send () {
        for ($i=1;$i<513;$i++) {
            $dmxChannelMap[$i] = 0;
        }
        // Get all DMX and RDM client values
        foreach ($this->rdmClientService->getDMXValues() as $channel => $value) {
            $dmxChannelMap[$channel] = $value;
        }

        foreach ($this->dmxClientService->getDMXValues() as $channel => $value) {
            $dmxChannelMap[$channel] = $value;
        }

        $command = "ola_set_dmx -u ". escapeshellarg($this->universe) . " -d ". escapeshellarg(implode(",",$dmxChannelMap));

        $this->logger->debug("Executing command " . $command);
        shell_exec($command);
    }
} 