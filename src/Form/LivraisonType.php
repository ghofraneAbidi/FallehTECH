<?php
namespace App\Form;

use App\Entity\Livraison;
use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
class LivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit']; // Determine if it's an edit form

        $builder
            ->add('commande', EntityType::class, [
                'class' => Commande::class,
                'choice_label' => 'id',
                'label' => 'Commande associée',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En Attente' => 'En Attente',
                    'En Cours' => 'En Cours',
                    'Livrée' => 'Livrée',
                ],
                'label' => 'Statut de la livraison',
                'placeholder' => 'Sélectionner un statut',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('transporteur', TextType::class, [
                'label' => 'Transporteur',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('numTelTransporteur', TelType::class, [
                'label' => 'Numéro de téléphone du transporteur',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\d+$/', // Seulement des chiffres
                        'message' => 'Le numéro de téléphone ne peut contenir que des chiffres.'
                    ])
                ]
            ])
            ->add('dateLivraison', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de livraison prévue',
                'attr' => ['class' => 'form-control'],
            ]);
            $builder->add('submit', SubmitType::class, [
                'label' => $isEdit ? 'Valider Les  Changements' : 'Créer livraison',
                'attr' => ['class' => 'btn btn-success btn-lg w-100 mt-3'],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livraison::class,
            'is_edit' => false,
        ]);
    }
}
