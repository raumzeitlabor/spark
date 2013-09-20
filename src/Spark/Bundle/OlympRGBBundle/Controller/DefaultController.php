<?php

namespace Spark\Bundle\OlympRGBBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View AS FOSView;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints as Assert;

class DefaultController extends FOSRestController
{
    public function __construct () {
    }

    /**
     * @Route("/olymp/set/r/{value}", defaults={"method" = "get", "_format" = "json"})
     * @ApiDoc(description="Sets the red channel brightness")
     * @param integer $value 0-255
     */
    public function setRedAction ($value)
    {
        return $this->get("olymp.setrgb.service")->setRGB($value, false, false);
    }

    /**
     * @Route("/olymp/set/g/{value}", defaults={"method" = "get", "_format" = "json"})
     * @ApiDoc(description="Sets the green channel brightness")
     * @param integer $value 0-255
     */
    public function setGreenAction ($value)
    {
        return $this->get("olymp.setrgb.service")->setRGB(false, $value, false);
    }

    /**
     * @Route("/olymp/set/b/{value}", defaults={"method" = "get", "_format" = "json"})
     * @ApiDoc(description="Sets the blue channel brightness")
     * @param integer $value 0-255
     */
    public function setBlueAction ($value)
    {
        return $this->get("olymp.setrgb.service")->setRGB(false, false, $value);
    }

    /**
     * @Route("/olymp/get", defaults={"method" = "get", "_format" = "json"})
     * @Route("/olymp/get/", defaults={"method" = "get", "_format" = "json"})
     * @ApiDoc(description="Returns the RGB values")
     */
    public function getRGB () {
        return $this->get("olymp.setrgb.service")->getRGB();
    }
}
