<?php


namespace Spark\Bundle\ClientBundle\Service;


use Psr\Log\LoggerInterface;

class DMXClientService {
    private $logger;

    public function __construct(
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
    }

    public function getDMXValues()
    {
        return array();
    }
} 