<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('last_name')
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
            ])
            ->add('role', ChoiceType::class, [
                'choices'  => [
                    'Agriculteur' => 'agriculteur',
                    'Client' => 'client',
                    'Ouvrier' => 'ouvrier',
                ],
                'expanded' => false, // Must be false for a select dropdown
                'multiple' => false, // Single choice
                'label' => 'Role',
                'attr' => ['class' => 'role-select'],
            ])
            ->add('carte_identite', TextType::class, [
                'label' => 'Numéro de CIN',
                'attr' => [
                    'maxlength' => 8,
                    'pattern' => '\d{8}', // Vérification HTML (8 chiffres uniquement)
                    'placeholder' => 'Entrer votre CIN',
                ],
                'required' => true,
            ])            
            ->add('disponibility', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('location', null, ['label' => 'Lieu'])
            ->add('experience', null, ['label' => 'Expérience']);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
    
}
