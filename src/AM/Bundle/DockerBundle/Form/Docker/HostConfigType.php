<?php


namespace AM\Bundle\DockerBundle\Form\Docker;

use AM\Bundle\DockerBundle\Form\AvailableContainersType;
use AM\Bundle\DockerBundle\Form\AvailableLinksType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HostConfigType
 * @package AM\Bundle\DockerBundle\Form\Docker
 */
class HostConfigType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('links', new AvailableLinksType($options['containerManager']))
            ->add('volumesFrom', new AvailableContainersType($options['containerManager']))
            ->add('restartPolicy', new RestartPolicyType())
            ->add('publishAllPorts', 'checkbox', [
                'label' => 'Publish all ports',
                'required' => false,
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'Host config',
            'required'  => true,
            'data_class' => 'Docker\API\Model\HostConfig',
        ));

        $resolver->setRequired('containerManager');
        $resolver->setAllowedTypes(['containerManager' => 'Docker\Manager\ContainerManager']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'host_config';
    }
}