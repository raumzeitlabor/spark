<?php
namespace Spark\Bundle\ClientBundle\Model;

abstract class RDMClient
{
    /**
     * The device label as parsed via RDM
     *
     * @var string
     */
    private $deviceLabel;

    /**
     * The UID of the device
     *
     * @var string
     */
    private $uid;

    /**
     * The device DMX start address, as parsed via RDM
     *
     * @var int
     */
    private $dmxStartAddress;

    /**
     * The device slot names as array
     *
     * @var array
     */
    private $slotNames = array();

    /**
     * The slot values
     *
     * @var array
     */
    private $slotValues = array();


    public function __construct($uid)
    {
        $this->uid = $uid;
    }

    public function setDMXStartAddress($startAddress)
    {
        $this->dmxStartAddress = $startAddress;
    }

    public function getUID()
    {
        return $this->uid;
    }

    public function setDeviceLabel($deviceLabel)
    {
        $this->deviceLabel = $deviceLabel;
    }

    /**
     * Returns the device label
     *
     * @return string
     */
    public function getDeviceLabel()
    {
        return $this->deviceLabel;
    }

    public function setSlotName($slot, $slotName)
    {
        $this->slotNames[$slot] = $slotName;
    }

    /**
     * Returns the slot names
     *
     * @return string[]
     */
    public function getSlotNames()
    {
        return $this->slotNames;
    }

    public function setSlotValue($slot, $slotValue)
    {
        $this->slotValues[$slot] = $slotValue;
    }

    /**
     * Returns all slot values
     *
     * @return int[]
     */
    public function getSlotValues()
    {
        return $this->slotValues;
    }
}