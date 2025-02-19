<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommandeClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', TextType::class, [
                'label' => 'Nom d’utilisateur',
                'attr' => [
                    
                    'placeholder' => 'Saisir votre nom',
                ],
                'mapped' => false,
            ])
            ->add('adresseLivraison', TextType::class, [
                'label' => 'Adresse Livraison',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Saisir votre adresse',
                ],
            ])
            ->add('total', NumberType::class, [
                'label' => 'Total (TND)',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('ModePaiement', ChoiceType::class, [
                'choices' => [
                    'Espèce' => 'Espèces',
                    'Carte bancaire' => 'Carte_Bancaire',
                    'e-Dinar' => 'e_DINAR',
                ],
                'placeholder' => 'Selctioner votre mode de paiement',
                'disabled' => false, // Ensure it is always enabled
                'attr' => ['class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Passer Commande',
                'attr' => ['class' => 'btn btn-success btn-lg w-100 mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
