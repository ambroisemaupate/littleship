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
 * @file UserContainerController.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\DockerBundle\Controller;

use AM\Bundle\DockerBundle\Docker\ContainerInfos;
use Docker\Container;
use Http\Client\Plugin\Exception\ClientErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserContainerController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException();
        }

        $containers = $this->getUser()->getContainers();

        try {
            $docker = $this->get('docker');
            $manager = $docker->getContainerManager();
            $assignation['containerEntities'] = $containers;
            $assignation['containers'] = [];

            foreach ($containers as $container) {
                $assignation['containers'][] = $manager->find($container->getContainerId());
            }

        } catch (ClientErrorException $e) {
            $assignation['error'] = $e->getResponse()->getBody();
        }

        return $this->render('AMDockerBundle:UserContainer:list.html.twig', $assignation);
    }

    /**
     * @param $username
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listForUsernameAction($username)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->get('doctrine')
            ->getManager()
            ->getRepository('AM\Bundle\UserBundle\Entity\User')
            ->findOneByUsername($username);
        if (null === $user) {
            throw $this->createNotFoundException();
        }
        $assignation['user'] = $user;
        $containers = $user->getContainers();

        try {
            $docker = $this->get('docker');
            $manager = $docker->getContainerManager();
            $assignation['containerEntities'] = $containers;
            $assignation['containers'] = [];

            foreach ($containers as $container) {
                $assignation['containers'][] = $manager->find($container->getContainerId());
            }

        } catch (ClientErrorException $e) {
            $assignation['error'] = $e->getMessage();
        }

        return $this->render('AMDockerBundle:UserContainer:listForUsername.html.twig', $assignation);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsAction($id)
    {
        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException();
        }
        $containerEntity = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\DockerBundle\Entity\Container', $id);

        if (null === $containerEntity) {
            throw $this->createNotFoundException();
        }
        if (!$this->getUser()->hasContainer($containerEntity)) {
            throw $this->createAccessDeniedException();
        }

        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($containerEntity->getContainerId());

        if (null === $container) {
            throw $this->createNotFoundException();
        }

        $assignation['containerEntity'] = $containerEntity;
        $containerInfos = new ContainerInfos($container);
        $containerInfos->getDetailsAssignation($manager, $assignation);

        return $this->render('AMDockerBundle:UserContainer:details.html.twig', $assignation);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function startAction($id)
    {
        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException();
        }
        $containerEntity = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\DockerBundle\Entity\Container', $id);
        if (null === $containerEntity) {
            throw $this->createNotFoundException();
        }
        if (!$this->getUser()->hasContainer($containerEntity)) {
            throw $this->createAccessDeniedException();
        }

        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($containerEntity->getContainerId());

        if (null === $container) {
            throw $this->createNotFoundException();
        }

        $infos = new ContainerInfos($container);
        if (!$infos->isRunning()) {
            $manager->start($container);
        }
        return $this->redirect($this->generateUrl('am_docker_usercontainer_details', [
            'id' => $id,
        ]));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function stopAction($id)
    {
        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException();
        }
        $containerEntity = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\DockerBundle\Entity\Container', $id);

        if (null === $containerEntity) {
            throw $this->createNotFoundException();
        }
        if (!$this->getUser()->hasContainer($containerEntity)) {
            throw $this->createAccessDeniedException();
        }

        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($containerEntity->getContainerId());

        if (null === $container) {
            throw $this->createNotFoundException();
        }

        $infos = new ContainerInfos($container);
        if ($infos->isRunning()) {
            $manager->stop($container, 2);
        }
        return $this->redirect($this->generateUrl('am_docker_usercontainer_details', [
            'id' => $id,
        ]));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function restartAction($id)
    {
        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException();
        }
        $containerEntity = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\DockerBundle\Entity\Container', $id);

        if (null === $containerEntity) {
            throw $this->createNotFoundException();
        }
        if (!$this->getUser()->hasContainer($containerEntity)) {
            throw $this->createAccessDeniedException();
        }

        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($containerEntity->getContainerId());

        if (null === $container) {
            throw $this->createNotFoundException();
        }

        $infos = new ContainerInfos($container);
        if ($infos->isRunning()) {
            $manager->restart($container);
        }
        return $this->redirect($this->generateUrl('am_docker_usercontainer_details', [
            'id' => $id,
        ]));
    }
}
