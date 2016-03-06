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
 * @file ContainerType.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\DockerBundle\Form;

use Docker\Manager\ImageManager;
use Docker\Manager\ContainerManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * ContainerType.
 */
class ContainerType extends AbstractType
{
    protected $imageManager;
    protected $containerManager;

    public function __construct(ImageManager $imageManager, ContainerManager $containerManager)
    {
        $this->imageManager = $imageManager;
        $this->containerManager = $containerManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', [
            'label' => 'Name',
            'required'  => true,
            'constraints' => [
                new NotBlank(),
                new Regex([
                    'pattern' => '#^[a-zA-Z0-9_\-]+$#'
                ]),
            ]
        ])
        ->add('image', new AvailableImagesType($this->imageManager))
        ->add('env', 'collection', [
            'label' => 'Environment variables:',
            'allow_add' => true,
            'allow_delete' => true,
            'type' => 'text',
            'attr' => ['class' => 'add-delete-form-type'],
            'options'  => [
                'required'  => false,
                'label' => false,
                'attr'      => ['class' => 'environment-var']
            ]
        ])
        ->add('ports', 'collection', [
            'label' => 'Exposed ports:',
            'allow_add' => true,
            'allow_delete' => true,
            'type' => 'text',
            'attr' => ['class' => 'add-delete-form-type'],
            'options'  => [
                'required' => false,
                'label' => false,
                'attr' => ['class' => 'port']
            ]
        ])
        ->add('publish_ports', 'checkbox', [
            'label' => 'Publish all exposed ports',
            'required'  => false,
        ])
        ->add('volumes_from', new AvailableContainersType($this->containerManager))
        ->add('links', new AvailableLinksType($this->containerManager))
        ->add('restart_policy', 'choice', [
            'label' => 'Restart policy:',
            'placeholder' => '-- Choose a restart policy --',
            'choices' => [
                'no' => 'Do not automatically restart the container',
                'on-failure' => 'Restart only if the container exits with a non-zero exit status',
                'always' => 'Always restart the container regardless of the exit status'
            ],
            'multiple' => false,
            'required'  => true,
        ])
        ->add('submit', 'submit', [
            'label' => 'Create and run',
            'attr' => [
                'class' => 'btn btn-primary'
            ]
        ]);
    }

    public function getName()
    {
        return 'container';
    }
}
