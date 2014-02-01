<?php
namespace Spark\Bundle\LichtsteuerungBundle\Service;

use Spark\Bundle\LichtsteuerungBundle\Model\Lichtsteuerung;

class LichtsteuerungService {
    private $universe = 0;

    private $logger;

    private $clients = array();

    const SLOTS = 32;


    public function __construct (
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
    }

    /**
     * Discovers any clients. This removes all existing clients and retrieves new information.
     * */
    public function discovery () {
        $this->clients = array();

        $command = "ola_rdm_discover -u " . escapeshellarg($this->universe) . " -f";

        //$this->logger->info("Executing command ".$command);
        exec($command, $output);

        foreach ($output as $uid) {
            if (strlen($uid) != 13) {
                // Found invalid UID, log and ignore
                $this->logger->info("Command ".$command ." returned invalid UID ".$uid);
            } else {
                $this->clients[$uid] = new Lichtsteuerung($uid);

                $this->applyDMXStartAddress($this->clients[$uid]);
                $this->applyDeviceLabel($this->clients[$uid]);
                $this->applySlotNames($this->clients[$uid]);
            }
        }

        print_r($this->clients);
    }

    public function applyDMXStartAddress (Lichtsteuerung $client) {
        $command = $this->getCommandTemplate($client);

        $command .= "dmx_start_address";

        $dmxStartAddress = shell_exec($command);

        // This is a bit ugly, and will probably break if e.g. another locale is set
        // Unfortunately, ola_rdm_get doesn't return a nicely parseable format

        $dmxStartAddress = str_replace("DMX Address: ","",$dmxStartAddress);

        $client->setDMXStartAddress($dmxStartAddress);
    }

    public function applyDeviceLabel(Lichtsteuerung $client)
    {
        $command = $this->getCommandTemplate($client);

        $command .= "device_label";

        $label = shell_exec($command);

        $client->setDeviceLabel($label);
    }

    public function applySlotNames (Lichtsteuerung $client) {
        for ($i=0;$i<self::SLOTS;$i++) {
            $command = $this->getCommandTemplate($client);
            $command .= "slot_description " . escapeshellarg($i);

            exec($command, $output);

            $label = str_replace("Name :", "", $output[1]);
            $client->setSlotName($i, $label);
        }
    }
    public function getCommandTemplate (Lichtsteuerung $client) {
        $command = "ola_rdm_get --uid ". escapeshellarg($client->getUID()). " -u ". escapeshellarg($this->universe)." ";

        return $command;
    }
}
