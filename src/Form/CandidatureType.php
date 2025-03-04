<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\OffreEmploi;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Enum\StatutCandidature;
class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('statut', ChoiceType::class, [
            'choices' => [
                'En attente' => StatutCandidature::EN_ATTENTE,
                'Acceptée' => StatutCandidature::ACCEPTEE,
                'Refusée' => StatutCandidature::REFUSEE,
            ],
            'choice_value' => fn (?StatutCandidature $enum) => $enum?->value, // Convert Enum to string
            'choice_label' => fn (StatutCandidature $enum) => $enum->label(), // Display readable labels
            'expanded' => false, // Dropdown list (set to true for radio buttons)
            'multiple' => false, // Single selection
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
        ]);
    }
}
