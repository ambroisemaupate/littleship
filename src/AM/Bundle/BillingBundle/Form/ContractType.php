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
                'choices' => [
                    'EUR' => 'Euro',
                    'USD' => 'US Dollar',
                ]
            ])
            ->add('type', 'choice', [
                'choices' => [
                    Contract::ANNUAL => 'type.annual',
                    Contract::QUARTERLY => 'type.quarterly',
                    Contract::BIMONTHLY => 'type.bimonthly',
                    Contract::MONTHLY => 'type.monthly',
                    Contract::WEEKLY => 'type.weekly',
                ]
            ])
            ->add('submit', 'submit', [
                'label' => 'create.contract',
            ]);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AM\Bundle\BillingBundle\Entity\Contract'
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
