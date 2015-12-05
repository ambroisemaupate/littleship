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
 * @file DefaultController.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\BillingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AM\Bundle\BillingBundle\Form\ContractType;
use AM\Bundle\BillingBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $contracts = $this->get('doctrine')
                        ->getManager()
                        ->getRepository('AM\Bundle\BillingBundle\Entity\Contract')
                        ->findBy([]);

        $totalsPerCurrency = [];

        foreach ($contracts as $key => $contract) {
            if (!isset($totalsPerCurrency[$contract->getCurrency()])) {
                $totalsPerCurrency[$contract->getCurrency()] = 0.0;
            }
            $totalsPerCurrency[$contract->getCurrency()] += $contract->getNormalizedAmount();
        }

        return $this->render('AMBillingBundle:Default:index.html.twig', array(
            'contracts' => $contracts,
            'totalsPerCurrency' => $totalsPerCurrency,
        ));
    }
}