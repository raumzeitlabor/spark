<?php
namespace Spark\Bundle\ClientBundle\Service;

use Spark\Bundle\ClientBundle\Model\LightControllerClient;
use Spark\Bundle\ClientBundle\Model\RDMClient;
use Psr\Log\LoggerInterface;

class RDMClientService
{
    private $universe = 0;

    private $logger;

    private $jsonAPIURI = "http://localhost:9090/";

    /**
     * @var RDMClient[]
     */
    private $clients = array();

    public function __construct(
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
    }

    /**
     * Discovers any clients. This removes all existing clients and retrieves new information.
     * */
    public function discovery()
    {
        $this->logger->info("Device discovery started");
        $this->interruptDMXTransmit();
        $this->clients = array();

        $command = "ola_rdm_discover -u " . escapeshellarg($this->universe) . " -f";

        $this->logger->debug("Executing command " . $command);
        exec($command, $output);

        foreach ($output as $uid) {
            if (strlen($uid) != 13) {
                // Found invalid UID, log and ignore
                $this->logger->info("Command " . $command . " returned invalid UID " . $uid);
            } else {
                $this->clients[$uid] = new LightControllerClient($uid);

                $this->applyDeviceLabel($this->clients[$uid]);
                $this->applyDeviceInfo($this->clients[$uid]);

                $this->applySlotNames($this->clients[$uid]);
                $this->applyDefaultSlotValues($this->clients[$uid]);

                $slotNames = array();
                foreach ($this->clients[$uid]->getSlotNames() as $slotId => $slotName) {
                    $slotNames[] = sprintf("%d: %s", $slotId, $slotName);
                }
                $this->logger->info(
                    sprintf(
                        "Found device %s, label %s, slot names %s",
                        $uid,
                        $this->clients[$uid]->getDeviceLabel(),
                        implode(", ", $slotNames)
                    )
                );
            }

        }

        $this->logger->info(sprintf("Device discovery finished. Found %s device(s)", count($this->clients)));
    }

    public function updateDMXSlots()
    {
        $this->interruptDMXTransmit();
        foreach ($this->clients as $client) {
            $this->applyDefaultSlotValues($client);
        }
    }

    public function applyDeviceInfo(RDMClient $client)
    {
        $uri = $this->jsonAPIURI . "json/rdm/uid_info?id=" . $this->universe . "&uid=" . $client->getUID();

        $json = json_decode(file_get_contents($uri));

        $client->setDMXFootprint($json->footprint);
        $client->setDMXStartAddress($json->address);
    }

    public function applyDeviceLabel(RDMClient $client)
    {
        $command = $this->getCommandTemplate($client);

        $command .= "device_label";
        $this->logger->debug("Executing command " . $command);
        $label = shell_exec($command);

        $client->setDeviceLabel(trim($label));
    }


    /**
     * Applies the slot names as reported by the device
     *
     * @param RDMClient $client
     */
    public function applySlotNames(RDMClient $client)
    {
        for ($i = 0; $i < $client->getDMXFootprint(); $i++) {
            $command = $this->getCommandTemplate($client);
            $command .= "slot_description " . escapeshellarg($i);

            $this->logger->debug("Executing command " . $command);
            exec($command, $output);

            $label = str_replace("Name: ", "", $output[1]);
            $client->setSlotName($i, $label);
        }
    }

    /**
     * Queries the default slot values from an RDM client
     *
     * @param RDMClient $client
     */
    public function applyDefaultSlotValues(RDMClient $client)
    {
        $command = $this->getCommandTemplate($client);
        $command .= "default_slot_value";

        $this->logger->debug("Executing command " . $command);
        exec($command, $output);

        $debugValues = array();

        for ($i = 0; $i < count($output); $i += 4) {
            $slotOffset = trim(str_replace("Slot Offset:", "", $output[$i + 1]));
            $slotValue = trim(str_replace("Default Slot Value:", "", $output[$i + 2]));

            $debugValues[$slotOffset] = $slotValue;

            $client->setSlotValue($slotOffset, $slotValue);
        }

        ksort($debugValues);
        $this->logger->debug(sprintf("Got DMX values %s", implode(",", $debugValues)));
    }

    public function getCommandTemplate(RDMClient $client)
    {
        $command = "ola_rdm_get --uid " . escapeshellarg($client->getUID()) . " -u " . escapeshellarg(
                $this->universe
            ) . " ";

        return $command;
    }

    public function getDMXValues()
    {
        $dmxChannelMap = array();

        foreach ($this->clients as $client) {
            $slots = $client->getSlotValues();

            foreach ($slots as $offset => $slotValue) {
                $dmxChannelMap[$client->getDMXStartAddress() + $offset] = $slotValue;
            }
        }

        return $dmxChannelMap;
    }

    /**
     * This is a workaround for OLA in conjunction with the USBDMX Pro interface. The interface can't transmit
     * DMX and RDM at the same time, and OLA fails to stop the DMX stream when doing an RDM command. To avoid problems,
     * we simply trigger a full discovery twice (first one will fail, second one ensures that devices aren't thrown out
     * of OLA)..
     */
    public function interruptDMXTransmit()
    {
        $command = "ola_rdm_discover -u " . escapeshellarg($this->universe) . " -f";
        exec($command, $output);
        exec($command, $output);
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function hasDMXChannel($channel)
    {
        foreach ($this->clients as $client) {
            if ($client->hasChannel($channel)) {
                return true;
            }
        }

        return false;
    }

    public function setDMXChannelValue($channel, $value)
    {
        $value = intval($value);
        foreach ($this->clients as $client) {
            if ($client->hasChannel($channel)) {
                $client->setChannelValue($channel, $value);
            }
        }
    }

}
