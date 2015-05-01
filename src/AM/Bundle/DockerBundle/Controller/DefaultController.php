<?php

namespace AM\Bundle\DockerBundle\Controller;

use React\EventLoop\Factory as LoopFactory;
use Clue\React\Docker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $docker = $this->get('docker');
        $imageManager = $docker->getImageManager();
        $containerManager = $docker->getContainerManager();

        $assignation = [];
        $assignation['containers'] = $containerManager->findAll([
            'all' => true
        ]);


        return $this->render('AMDockerBundle:Default:index.html.twig', $assignation);
    }


    public function imagesAction()
    {
        $docker = $this->get('docker');
        $imageManager = $docker->getImageManager();

        $assignation = [];
        $assignation['images'] = $imageManager->findAll();


        return $this->render('AMDockerBundle:Default:images.html.twig', $assignation);
    }
}
