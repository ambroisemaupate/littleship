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
 * @file ImageController.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\DockerBundle\Controller;

use Docker\Manager\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
*
*/
class ImageController extends Controller
{

    public function imagesAction()
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $assignation = [];

        try {
            $docker = $this->get('docker');
            $imageManager = $docker->getImageManager();
            $assignation['images'] = $imageManager->findAll();
        } catch (RequestException $e) {
            $assignation['error'] = $e->getMessage();
        }

        return $this->render('AMDockerBundle:Image:images.html.twig', $assignation);
    }

    public function searchAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $assignation = [];

        $form = $this->createFormBuilder()
                    ->add('search', 'text', [
                        'constraints' => [
                            new NotBlank()
                        ],
                        'label' => false,
                        'attr' => [
                            'placeholder' => 'Search term',
                        ]
                    ])
                    ->getForm();

        $assignation['searchForm'] = $form->createView();
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $docker = $this->get('docker');
                /** @var ImageManager $imageManager */
                $imageManager = $docker->getImageManager();
                $json = $imageManager->search([
                    'term' => $form->get('search')->getData(),
                ]);
                $assignation['json'] = $json;

            } catch (\Http\Client\Exception\RequestException $e) {
                $assignation['error'] = $e->getMessage();
            }
        } else {
            $assignation['json'] = [];
        }

        return $this->render('AMDockerBundle:Image:search.html.twig', $assignation);
    }
}