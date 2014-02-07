<?php

namespace Spark\Bundle\WebBundle\Controller;

use
    Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Routing;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\View;

class DefaultController extends Controller
{
    /**
     * @Routing\Route("/getChannels", defaults={"method" = "get","_format" = "json"})
     * @Routing\Method({"GET"})
     * @ApiDoc(
     *  description="Retrieves all registered DMX channels with their description"
     * )
     * )
     * @View()
     */
    public function getChannelsAction()
    {
        return $this->get("dmxservice")->getChannels();
    }

    /**
     * @Routing\Route("/setChannelValue/{channel}/{value}", defaults={"method" = "get","_format" = "json"})
     * @Routing\Method({"GET"})
     * @ApiDoc(
     *  description="Sets a channel to a specific value"
     * )
     * )
     * @View()
     */
    public function setChannelValueAction($channel, $value)
    {
        return $this->get("dmxservice")->setChannelValue($channel, $value);
    }
}
