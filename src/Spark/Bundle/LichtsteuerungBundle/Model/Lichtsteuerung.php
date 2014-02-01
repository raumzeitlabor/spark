<?php

namespace Spark\Bundle\LichtsteuerungBundle\Model;

/**
 * Class Lichtsteuerung
 *
 * Represents a single instance of a Lichtsteuerung client
 * @package Spark\Bundle\LichtsteuerungBundle\Model
 */
class Lichtsteuerung {
    /**
     * The device label as parsed via RDM
     * @var string
     */
    private $deviceLabel;

    /**
     * The UID of the device
     * @var string
     */
    private $uid;
    /**
     * The device DMX start address, as parsed via RDM
     * @var int
     */
    private $dmxStartAddress;

    /**
     * The device slot names as array
     * @var array
     */
    private $slotNames = array();


    public function __construct ($uid) {
        $this->uid = $uid;
    }

    public function setDMXStartAddress ($startAddress) {
        $this->dmxStartAddress = $startAddress;
    }

    public function setUID ($uid) {
        $this->uid = $uid;
    }

    public function getUID () {
        return $this->uid;
    }

    public function setDeviceLabel ($deviceLabel) {
        $this->deviceLabel = $deviceLabel;
    }

    public function setSlotName ($slot, $slotName) {
        $this->slotNames[$slot] = $slotName;
    }
}