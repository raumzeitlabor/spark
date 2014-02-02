<?php
namespace Spark\Bundle\ClientBundle\Service;

use Spark\Bundle\ClientBundle\Model\LightControllerClient;
use Spark\Bundle\ClientBundle\Model\RDMClient;
use Psr\Log\LoggerInterface;

class RDMClientService
{
    private $universe = 0;

    private $logger;

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

                $this->applyDeviceInfo($this->clients[$uid]);
                $this->applyDMXStartAddress($this->clients[$uid]);
                $this->applyDeviceLabel($this->clients[$uid]);

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

    public function applyDeviceInfo(RDMClient $client)
    {
        $command = $this->getCommandTemplate($client);
        $command .= "device_info";

        exec($command, $output);

        foreach ($output as $line) {
            if (strpos($line, "DMX Footprint") !== false) {
                $footprint = str_replace("DMX Footprint:", "", $line);
                $footprint = intval(trim($footprint));
                $client->setDMXFootprint($footprint);
            }
        }
    }

    public function applyDMXStartAddress(RDMClient $client)
    {
        $command = $this->getCommandTemplate($client);

        $command .= "dmx_start_address";

        $this->logger->debug("Executing command " . $command);
        $dmxStartAddress = shell_exec($command);

        // This is a bit ugly, and will probably break if e.g. another locale is set
        // Unfortunately, ola_rdm_get doesn't return a nicely parseable format

        $dmxStartAddress = str_replace("DMX Address: ", "", $dmxStartAddress);

        $client->setDMXStartAddress($dmxStartAddress);
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

        for ($i = 0; $i < $client->getDMXFootprint(); $i++) {
            $lineNum = ($i * 4) + 2;

            $slotValue = str_replace("Default Slot Value:", "", $output[$lineNum]);
            $slotValue = trim($slotValue);

            $client->setSlotValue($i, $slotValue);
        }
    }

    /**
     * Returns a pre-filled ola_rdm_get string which includes the universe and uid of the RDM device.
     *
     * @param RDMClient $client
     *
     * @return string
     */
    public function getCommandTemplate(RDMClient $client)
    {
        $command = "ola_rdm_get --uid " . escapeshellarg($client->getUID()) . " -u " . escapeshellarg(
                $this->universe
            ) . " ";

        return $command;
    }
}
