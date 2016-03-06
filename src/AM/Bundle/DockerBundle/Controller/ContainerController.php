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
use AM\Bundle\DockerBundle\Entity\Container as ContainerEntity;
use AM\Bundle\DockerBundle\Form\ContainerEntityType;
use AM\Bundle\DockerBundle\Form\ContainerType;
use Docker\API\Model\Container;
use Docker\API\Model\ContainerConfig;
use Docker\API\Model\HostConfig;
use Docker\API\Model\RestartPolicy;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

/**
 * Description.
 */
class ContainerController extends Controller
{
    public function listAction()
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $assignation = [];
        try {
            $docker = $this->get('docker');
            $manager = $docker->getContainerManager();
            $assignation['containers'] = $manager->findAll();
        } catch (RequestException $e) {
            $assignation['error'] = $e->getMessage();
        }

        return $this->render('AMDockerBundle:Container:list.html.twig', $assignation);
    }
    public function listAllAction()
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $assignation = [];

        try {
            $docker = $this->get('docker');
            $manager = $docker->getContainerManager();
            $assignation['containers'] = $manager->findAll([
                'all' => true,
            ]);
        } catch (RequestException $e) {
            $assignation['error'] = $e->getMessage();
        }

        return $this->render('AMDockerBundle:Container:list.html.twig', $assignation);
    }

    public function detailsAction($id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($id);
        $assignation = [];

        if (null !== $container) {
            $em = $this->get('doctrine')->getManager();
            $containerEntity = $em->getRepository('AM\Bundle\DockerBundle\Entity\Container')
                ->findOneByContainerId($id);

            if (null !== $containerEntity) {
                $assignation['containerEntity'] = $containerEntity;
            }

            $containerInfos = new ContainerInfos($container);
            $containerInfos->getDetailsAssignation($manager, $assignation);

            return $this->render('AMDockerBundle:Container:details.html.twig', $assignation);

        } else {
            throw $this->createNotFoundException();
        }
    }

    public function addAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        try {
            $docker = $this->get('docker');
            $cManager = $docker->getContainerManager();
            $iManager = $docker->getImageManager();

            $form = $this->createForm(new ContainerType($iManager, $cManager));

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $containerConfig = new ContainerConfig();

                $restartPolicy = new RestartPolicy();
                $restartPolicy->setName($data['restart_policy']);
                $restartPolicy->setMaximumRetryCount(0);

                $hostConfig = new HostConfig();
                $hostConfig->setPublishAllPorts((boolean) $data['publish_ports']);
                $hostConfig->setRestartPolicy($restartPolicy);
                if (isset($data['links']) && count($data['links']) > 0) {
                    $hostConfig->setLinks($data['links']);
                }
                if (isset($data['volumes_from']) && count($data['volumes_from']) > 0) {
                    $hostConfig->setVolumesFrom($data['volumes_from']);
                }
                $containerConfig->setExposedPorts($this->getExposedPorts($data['ports']));
                $containerConfig->setImage($data['image']);
                $containerConfig->setNames([$data['name']]);
                $containerConfig->setHostConfig($hostConfig);
                $containerConfig->setAttachStdin(false);
                $containerConfig->setAttachStdout(false);
                $containerConfig->setAttachStderr(false);

                // Test if env is set and not empty before setting it
                if (isset($data['env']) && count($data['env']) > 0) {
                    $containerConfig->setEnv($data['env']);
                }
                try {
                    $containerCreateResult = $cManager->create($containerConfig);
                    $cManager->start($containerCreateResult->getId());
                    $this->get('logger')->info('New container created and started.', [
                        'id' => $containerCreateResult->getId(),
                        'name' => $containerConfig->getNames()[0],
                    ]);
                    return $this->redirect($this->generateUrl('am_docker_container_list'));
                } catch (\Http\Client\Plugin\Exception\ServerErrorException $e) {
                    $form->addError(new FormError($e->getMessage()));
                }
            }

            $assignation['form'] = $form->createView();
        } catch (RequestException $e) {
            $assignation['error'] = $e->getMessage();
        }

        return $this->render('AMDockerBundle:Container:add.html.twig', $assignation);
    }

    protected function getExposedPorts(array &$ports)
    {
        $exposedPorts = [];
        foreach ($ports as $port) {
            $exposedPorts[$port] = [];
        }
        return new \ArrayObject($exposedPorts);
    }

    public function startAction($id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($id);

        if (null !== $container) {
            $infos = new ContainerInfos($container);
            if (!$infos->isRunning()) {
                $manager->start($container->getId());
                $this->get('logger')->info('Started container', [
                    'name' => $container->getName(),
                    'config' => $container->getConfig(),
                ]);
            }
            return $this->redirect($this->generateUrl('am_docker_container_details', [
                'id' => $id,
            ]));

        } else {
            throw $this->createNotFoundException();
        }
    }

    public function stopAction($id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($id);

        if (null !== $container) {
            if ($container->getState()->getRunning()) {
                $manager->stop($container->getId(), [
                    't' => 2
                ]);
                $this->get('logger')->info('Stopped container', [
                    'name' => $container->getName(),
                    'config' => $container->getConfig(),
                ]);
            }
            return $this->redirect($this->generateUrl('am_docker_container_details', [
                'id' => $id,
            ]));

        } else {
            throw $this->createNotFoundException();
        }
    }

    public function restartAction($id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($id);

        if (null !== $container) {
            if ($container->getState()->getRunning()) {
                $manager->restart($container->getId());
                $this->get('logger')->info('Restarted container', [
                    'name' => $container->getName(),
                    'config' => $container->getConfig(),
                ]);
            }
            return $this->redirect($this->generateUrl('am_docker_container_details', [
                'id' => $id,
            ]));

        } else {
            throw $this->createNotFoundException();
        }
    }

    public function removeAction(Request $request, $id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($id);

        if (null !== $container) {
            $assignation['container'] = $container;
            $form = $this->createFormBuilder()
                ->add('submit', 'submit', [
                    'label' => 'Remove container',
                    'attr' => [
                        'class' => 'btn btn-danger',
                    ],
                ])
                ->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // remove container
                if ($container->getState()->getRunning()) {
                    $manager->kill($container->getId());
                }
                $manager->remove($container->getId());

                // unsync container entity
                $em = $this->get('doctrine')->getManager();
                $containerEntity = $em->getRepository('AM\Bundle\DockerBundle\Entity\Container')
                    ->findOneByContainerId($id);

                if (null !== $containerEntity) {
                    $em->remove($containerEntity);
                    $em->flush();
                }
                $this->get('logger')->info('Removed container', [
                    'name' => $container->getName(),
                    'config' => $container->getConfig(),
                ]);
                return $this->redirect($this->generateUrl('am_docker_container_list'));
            }

            $assignation['form'] = $form->createView();
            return $this->render('AMDockerBundle:Container:remove.html.twig', $assignation);

        } else {
            throw $this->createNotFoundException();
        }
    }

    public function syncAction(Request $request, $id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($id);

        if (null !== $container) {
            $infos = new ContainerInfos($container);
            $containerEntity = new ContainerEntity();
            $containerEntity->setContainerId($id);
            $containerEntity->setConfiguration(serialize($container));
            $containerEntity->setName($container->getName());

            $form = $this->createForm(new ContainerEntityType(), $containerEntity);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $containerEntity->setSynced(true);
                $em = $this->get('doctrine')->getManager();
                $em->persist($containerEntity);
                $em->flush();

                return $this->redirect($this->generateUrl('am_docker_container_details', ['id' => $id]));
            }

            $assignation['form'] = $form->createView();
            return $this->render('AMDockerBundle:Container:sync.html.twig', $assignation);
        } else {
            throw $this->createNotFoundException();
        }
    }

    public function unsyncAction(Request $request, $id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->get('doctrine')->getManager();
        $containerEntity = $em->find('AM\Bundle\DockerBundle\Entity\Container', $id);

        if (null === $containerEntity) {
            throw $this->createNotFoundException();
        }
        if (null === $containerEntity->getUser()) {
            throw $this->createNotFoundException();
        }

        $docker = $this->get('docker');
        $manager = $docker->getContainerManager();
        $container = $manager->find($containerEntity->getContainerId());
        $assignation['container'] = $container;

        if (null === $container) {
            throw $this->createNotFoundException();
        }

        $form = $this->createFormBuilder()
            ->add('submit', 'submit', [
                'label' => 'Un-sync container',
                'attr' => [
                    'class' => 'btn btn-danger',
                ],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($containerEntity);
            $em->flush();
            return $this->redirect($this->generateUrl('am_docker_container_details', ['id' => $containerEntity->getContainerId()]));
        }

        $assignation['form'] = $form->createView();
        return $this->render('AMDockerBundle:Container:unsync.html.twig', $assignation);
    }
}
