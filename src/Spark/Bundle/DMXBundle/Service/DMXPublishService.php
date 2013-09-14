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

        if (array_key_exists('red', $data) && $data['red'] !== false) {
            $this->olympRed = $data['red'];
        }

        if (array_key_exists('green', $data) && $data['green'] !== false) {
            $this->olympGreen = $data['green'];
        }

        if (array_key_exists('blue', $data) && $data['blue'] !== false) {
            $this->olympBlue = $data['blue'];
        }

        $cmdTemplate = 'ola_set_dmx -u 0 --dmx %s';

        $channelValues = array($this->olympBlue, $this->olympGreen, $this->olympRed);

        $cmd = sprintf($cmdTemplate, escapeshellarg(implode(",", $channelValues)));

        $this->logger->debug($cmd);

        exec($cmd);

        return array(123);
    }
}