<?php

namespace Spark\Bundle\SparkTouchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SparkSparkTouchBundle:Default:index.html.twig');
    }
}
