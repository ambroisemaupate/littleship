<?php

namespace AM\Bundle\BillingBundle\Form;

use AM\Bundle\BillingBundle\Entity\Contract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContractType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount')
            ->add('startedAt', 'date')
            ->add('currency', 'choice', [
                'label' => 'Currency',
                'choices' => [
                    'EUR' => 'Euro',
                    'USD' => 'US Dollar',
                ]
            ])
            ->add('type', 'choice', [
                'label' => 'Type',
                'choices' => Contract::$typeToHuman,
            ])
            ->add('submit', 'submit', [
                'label' => $options['button_label'],
            ]);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AM\Bundle\BillingBundle\Entity\Contract',
            'button_label' => 'Create contract',
        ));

        $resolver->addAllowedTypes(array(
            'button_label' => 'string',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'am_bundle_billingbundle_contract';
    }
}
