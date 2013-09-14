<?php
namespace Spark\Bundle\OlympRGBBundle\Service;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Handles the RGB strip communication with the DMXPublishService via RabbitMQ.
 *
 * Class OlympDMXService
 * @package Spark\Bundle\DMXBundle\Service
 */
class OlympDMXService {
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Holds the producer to push to the RabbitMq queue
     *
     * @var \OldSound\RabbitMqBundle\RabbitMq\Producer
     */
    private $olympDMXRPC;

    /**
     * Creates a new Olymp DMX service.
     *
     * @param EntityManager   $entityManager
     * @param Producer        $producer
     */
    public function __construct(EntityManager $entityManager, $olympDMXRPC)
    {
        $this->olympDMXRPC = $olympDMXRPC;
        $this->entityManager = $entityManager;
    }

    /**
     * Sets the mode to fixed and pushes the given RGB values via RabbitMQ.
     *
     * @param $red integer 0-255 or false if not required to change
     * @param $green integer 0-255 or false if not required to change
     * @param $blue integer 0-255 or false if not required to change
     */
    public function setRGB ($red, $green, $blue) {

        $this->validateColor($red, "red");
        $this->validateColor($green, "green");
        $this->validateColor($blue, "blue");

        $this->olympDMXRPC->addRequest(serialize(array('red' => $red, 'green' => $green, 'blue' => $blue)), 'dmx_publish', 'request_id');
        $replies = $this->olympDMXRPC->getReplies();
    }

    /**
     * Validates the given color value. Throws an exception for the color with the specified colorName.
     *
     * @param $value    integer|false The value to validate
     * @param $colorName string The color name for use in the exception
     * @throws \OutOfRangeException
     */
    private function validateColor ($value, $colorName) {
        $exceptionMessage = "The parameter %s is 0-255 or false, got %s";

        if ($value !== false && ($value < 0 || $value > 255)) {
            throw new \OutOfRangeException(sprintf($exceptionMessage, $colorName, print_r($value, true)));
        }
    }

}