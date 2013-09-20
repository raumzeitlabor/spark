<?php
namespace Spark\Bundle\DMXBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class DMXPublishService implements ConsumerInterface {
    private $logger;

    /**
     * Represents the current mode of the Olymp RGB strips
     * @var string
     */
    private $olympMode = 0;

    /**
     * Holds the current olymp RGB state
     * @var integer
     */
    private $olympRed = 0;

    /**
     * @var integer
     */
    private $olympGreen = 0;

    /**
     * @var integer
     */
    private $olympBlue = 0;

    public function __construct (
        LoggerInterface $logger
    ) {
        $this->logger = $logger;

        $this->logger->debug("constructed new publisher");
    }

    public function execute(AMQPMessage $msg)
    {
        $data = unserialize($msg->body);

        switch ($data["mode"]) {
            case 'set':
                $return = $this->setRGB($data);
                 break;
            case 'get':
                $return = $this->getRGB();
                break;
        }

        return $return;
    }

    /**
     * Returns the current RGB state
     * @return array
     */
    public function getRGB () {
        return array('red' => $this->olympRed, 'green' => $this->olympGreen, 'blue' => $this->olympBlue);
    }

    /**
     * Sets the RGB state
     * @param $data
     * @return arrayS
     */
    public function setRGB ($data) {
        if (array_key_exists('red', $data) && $data['red'] !== false) {
            $this->olympRed = intval($data['red']);
        }

        if (array_key_exists('green', $data) && $data['green'] !== false) {
            $this->olympGreen = intval($data['green']);
        }

        if (array_key_exists('blue', $data) && $data['blue'] !== false) {
            $this->olympBlue = intval($data['blue']);
        }

        $cmdTemplate = 'ola_set_dmx -u 0 --dmx %s';

        $channelValues = array($this->olympBlue, $this->olympGreen, $this->olympRed);

        $cmd = sprintf($cmdTemplate, escapeshellarg(implode(",", $channelValues)));

        $this->logger->debug($cmd);

        exec($cmd);

        return $this->getRGB();
    }
}