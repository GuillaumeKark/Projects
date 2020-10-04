<?php

namespace Phinedo\OutilsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Phinedo\OutilsBundle\Form\SummernoteTransformer;

class MailingType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mail', 'text', array(
                'required' => false,
            ))
            ->add('objet', 'text', array(
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Mailing Phinedo - XXX'
                )
            ))
            ->add('content', 'textarea', array(
                'required' => false,
            ))
            ->add('dateEnvoi', 'datetime', array(
                'required' => true,
                'widget' => 'single_text',
                'attr' => array(
                    'placeholder' => '1984-06-25 12:15:30'
                )
            ))
            ->add('tous', 'checkbox', array(
                'required' => false,
            ))
            ->add('save', 'submit', array(
                    'attr' => array('label' => 'Envoyer'),
                    ));
        ;
        
        $builder->get('content')
            ->addModelTransformer(new SummernoteTransformer());
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Phinedo\OutilsBundle\Entity\Mailing'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'phinedo_outilsbundle_mailing';
    }
}
