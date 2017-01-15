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
 * @file AvailableLinksType.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\DockerBundle\Form;

use Docker\Manager\ContainerManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AvailableContainersType.
 */
class AvailableLinksType extends AbstractType
{
    protected $manager;

    public function __construct(ContainerManager $manager)
    {
        $this->manager = $manager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $containers = $this->manager->findAll([
            'all' => true
        ]);
        $options = [];

        foreach ($containers as $container) {
            $imageName = $container->getImage();
            $imageName = explode(':', $imageName);
            $repository = explode('/', $imageName[0]);

            $name = preg_replace('#/?([a-zA-Z\_\-0-9])#', '$1', $container->getNames()[0]);

            if (isset($repository[1])) {
                $linkName = $name . ':' . $repository[1];
            } else {
                $linkName = $name . ':' . $repository[0];
            }
            $options[$name] = $linkName;
        }

        $resolver->setDefaults(array(
            'choices' => $options,
            'choices_as_values' => true,
            'label' => 'Link container to:',
            'placeholder' => 'None',
            'multiple' => true,
            'required'  => false,
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'availablelinks';
    }
}
