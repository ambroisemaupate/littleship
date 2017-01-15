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
 * @file UserController.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\UserBundle\Controller;

use AM\Bundle\DockerBundle\Entity\Container;
use AM\Bundle\UserBundle\Entity\User;
use Docker\API\Model\ContainerConfig;
use Docker\Manager\ContainerManager;
use Symfony\Component\HttpFoundation\Request;
use AM\Bundle\UserBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    public function listAction()
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $assignation = [];
        $users = $this->get('doctrine')
                        ->getManager()
                        ->getRepository('AM\Bundle\UserBundle\Entity\User')
                        ->findBy([], ['username' => 'ASC']);

        $assignation['users'] = $users;

        return $this->render('AMUserBundle:User:list.html.twig', $assignation);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction(Request $request, $id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $assignation = [];
        $docker = $this->get('docker');

        /** @var User $user */
        $user = $this->get('doctrine')
                        ->getManager()
                        ->find('AM\Bundle\UserBundle\Entity\User', $id);

        if (null !== $user) {
            $containers = $user->getContainers();

            try {
                /** @var ContainerManager $manager */
                $manager = $docker->getContainerManager();
                $existingContainersId = [];
                $existingContainers = $manager->findAll([
                    'all' => true,
                ]);
                /** @var ContainerConfig $existingContainer */
                foreach ($existingContainers as $existingContainer) {
                    $existingContainersId[] = $existingContainer->getId();
                }

                /** @var Container $container */
                foreach ($containers as $container) {
                    if (!in_array($container->getContainerId(), $existingContainersId)) {
                        $container->setOrphan(true);
                    }
                }

                $this->get('doctrine')
                    ->getManager()
                    ->flush();
            } catch (\Exception $e) {

            }

            $form = $this->createForm(new UserType(), $user);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->get('doctrine')
                    ->getManager()
                    ->flush();
                return $this->redirect($this->generateUrl('am_user_edit', ['id'=>$id]));
            }

            $assignation['user'] = $user;
            $assignation['form'] = $form->createView();
            $assignation['containers'] = $containers;

            return $this->render('AMUserBundle:User:edit.html.twig', $assignation);
        }

        return $this->createNotFoundException('User does not exist.');
    }
}
