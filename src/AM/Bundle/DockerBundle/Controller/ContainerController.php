<?php
/**
 * Copyright © 2015, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file ContainerController.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\DockerBundle\Controller;

use AM\Bundle\DockerBundle\Docker\ContainerInfos;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description.
 */
class ContainerController extends Controller
{
    public function listAction()
    {
        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();

        $assignation = [];
        $assignation['containers'] = $manager->findAll([
            'all' => true
        ]);


        return $this->render('AMDockerBundle:Container:list.html.twig', $assignation);
    }

    public function startAction($id)
    {
        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($id);

        if (null !== $container) {
            $infos = new ContainerInfos($container);
            if (!$infos->isRunning()) {
                $manager->start($container);
            }
            return $this->redirect($this->generateUrl('am_docker_homepage'));

        } else {
            throw $this->createNotFoundException();
        }
    }

    public function stopAction($id)
    {
        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($id);

        if (null !== $container) {
            $infos = new ContainerInfos($container);
            if ($infos->isRunning()) {
                $manager->stop($container, 2);
            }
            return $this->redirect($this->generateUrl('am_docker_homepage'));

        } else {
            throw $this->createNotFoundException();
        }
    }
}
