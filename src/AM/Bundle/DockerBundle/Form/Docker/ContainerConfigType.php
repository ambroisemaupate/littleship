<?php


namespace AM\Bundle\DockerBundle\Form\Docker;

use AM\Bundle\DockerBundle\Form\AvailableImagesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ContainerConfigType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', 'text', [
                'label' => 'Container name',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '#^[a-zA-Z0-9_\-]+$#'
                    ]),
                ]
            ])
            ->add('hostname', 'text', [
                'label' => 'Hostname',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '#^[a-zA-Z0-9\.\-]+$#'
                    ]),
                ]
            ])
            ->add('image', new AvailableImagesType($options['imageManager']))
            ->add('env', 'collection', [
                'label' => 'Environment',
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
            ->add('exposedPorts', 'collection', [
                'label' => 'Exposed ports',
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
            ->add('hostConfig', new HostConfigType(), [
                'containerManager' => $options['containerManager'],
            ]);

        $builder->get('exposedPorts')
        ->addModelTransformer(new CallbackTransformer(
            function (\ArrayObject $portsObject = null) {
                $exposedPorts = [];

                if (null !== $portsObject) {
                    foreach ($portsObject->getIterator() as $port => $object) {
                        $exposedPorts[] = $port;
                    }
                }
                return $exposedPorts;
            },
            function ($portsArray) {
                $exposedPorts = [];
                foreach ($portsArray as $port) {
                    $exposedPorts[$port] = new \ArrayObject();
                }
                return new \ArrayObject($exposedPorts);
            }
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'Container config',
            'required'  => true,
            'data_class' => 'Docker\API\Model\ContainerConfig',
        ));

        $resolver->setRequired('containerManager');
        $resolver->setRequired('imageManager');
        $resolver->setAllowedTypes(['containerManager' => 'Docker\Manager\ContainerManager']);
        $resolver->setAllowedTypes(['imageManager' => 'Docker\Manager\ImageManager']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'container_config';
    }
}