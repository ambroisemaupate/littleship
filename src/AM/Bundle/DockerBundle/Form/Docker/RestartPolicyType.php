<?php


namespace AM\Bundle\DockerBundle\Form\Docker;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RestartPolicyType
 * @package AM\Bundle\DockerBundle\Form\Docker
 */
class RestartPolicyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'choice', [
            'label' => false,
            'placeholder' => '-- Choose a restart policy --',
            'choices_as_values' => true,
            'choices' => [
                'Do not automatically restart the container' => 'no',
                'Restart only if the container exits with a non-zero exit status' => 'on-failure',
                'Always restart the container regardless of the exit status' => 'always',
            ],
            'multiple' => false,
            'required' => true,
        ])
        ->add('maximumRetryCount', 'integer', [
            'label' => 'maximumRetryCount',
            'required' => true,
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'Restart policy',
            'required'  => true,
            'data_class' => 'Docker\API\Model\RestartPolicy',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'restart_policy';
    }
}