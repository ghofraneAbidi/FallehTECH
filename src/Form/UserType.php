<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('last_name')
            ->add('email')
            ->add('password')
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
            ->add('carte_identite')
            ->add('disponibility', null, [
                'widget' => 'single_text',
            ])
            ->add('location')
            ->add('experience')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
