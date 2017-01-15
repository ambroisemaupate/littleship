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
 * @file ContractController.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\BillingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AM\Bundle\BillingBundle\Form\ContractType;
use AM\Bundle\BillingBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ContractController extends Controller
{
    /**
 * @param Request $request
 * @param $userId
 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
 */
    public function indexAction(Request $request, $userId)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\UserBundle\Entity\User', $userId);

        if (null === $user) {
            throw $this->createNotFoundException();
        }

        $contracts = $this->get('doctrine')
            ->getManager()
            ->getRepository('AM\Bundle\BillingBundle\Entity\Contract')
            ->findBy([
                'user' => $user
            ]);

        $newContract = new Contract($user);
        $addContractForm = $this->createForm(new ContractType(), $newContract);

        $addContractForm->handleRequest($request);
        if ($addContractForm->isValid()) {
            $this->get('doctrine')
                ->getManager()
                ->persist($newContract);
            $this->get('doctrine')
                ->getManager()
                ->flush();

            return $this->redirect($this->generateUrl('am_billing_contracts', ['userId'=>$userId]));
        }

        return $this->render('AMBillingBundle:Contract:index.html.twig', array(
            'user' => $user,
            'contracts' => $contracts,
            'addContractForm' => $addContractForm->createView(),
        ));
    }

    /**
     * @param Request $request
     * @param $userId
     * @param $contractId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $userId, $contractId)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\UserBundle\Entity\User', $userId);

        $contract = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\BillingBundle\Entity\Contract', $contractId);

        if (null === $user) {
            throw $this->createNotFoundException('User not found');
        }
        if (null === $contract) {
            throw $this->createNotFoundException('Contract not found');
        }

        $editContractForm = $this->createForm(new ContractType(), $contract, array(
            'button_label' => 'Edit contract',
        ));
        $editContractForm->handleRequest($request);
        if ($editContractForm->isValid()) {
            $this->get('doctrine')
                ->getManager()
                ->flush();

            return $this->redirect($this->generateUrl('am_billing_contracts', ['userId'=>$userId]));
        }

        return $this->render('AMBillingBundle:Contract:edit.html.twig', array(
            'user' => $user,
            'editContractForm' => $editContractForm->createView(),
        ));
    }

    /**
     * @param Request $request
     * @param $userId
     * @param $contractId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $userId, $contractId)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\UserBundle\Entity\User', $userId);

        $contract = $this->get('doctrine')
            ->getManager()
            ->find('AM\Bundle\BillingBundle\Entity\Contract', $contractId);

        if (null === $user) {
            throw $this->createNotFoundException('User not found');
        }
        if (null === $contract) {
            throw $this->createNotFoundException('Contract not found');
        }

        $deleteContractForm = $this->createForm('form');
        $deleteContractForm->handleRequest($request);
        if ($deleteContractForm->isValid()) {
            $this->get('doctrine')
                ->getManager()
                ->remove($contract);
            $this->get('doctrine')
                ->getManager()
                ->flush();

            return $this->redirect($this->generateUrl('am_billing_contracts', ['userId'=>$userId]));
        }

        return $this->render('AMBillingBundle:Contract:delete.html.twig', array(
            'user' => $user,
            'deleteContractForm' => $deleteContractForm->createView(),
        ));
    }
}
