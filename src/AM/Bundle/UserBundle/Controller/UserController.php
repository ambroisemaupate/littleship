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

    public function editAction(Request $request, $id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $assignation = [];
        $user = $this->get('doctrine')
                        ->getManager()
                        ->find('AM\Bundle\UserBundle\Entity\User', $id);

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

        return $this->render('AMUserBundle:User:edit.html.twig', $assignation);
    }
}
