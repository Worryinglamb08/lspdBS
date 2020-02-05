<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom'
                ]
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'prenom'
                ]
            ])
            ->add('mail', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse mail'
                ]
            ])
            ->add('indicatif', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Indicatif'
                ]
            ])
            ->add('pwd', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Mot de passe'
                ]
            ])
            ->add('confPwd', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Confirmation de mot de passe'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
