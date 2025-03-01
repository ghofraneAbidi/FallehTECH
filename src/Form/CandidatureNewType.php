<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\OffreEmploi;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\StatutCandidature;

use App\Repository\UtilisateurRepository;

class CandidatureNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
            ->add('idTravailleur', EntityType::class, [
                'class' => Utilisateur::class,
                'query_builder' => function (UtilisateurRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->where('u.role LIKE :role')
                        ->setParameter('role', '%OUVRIER%');
                },
                'choice_label' => 'nom', // Display user names instead of emails
                'label' => 'Sélectionner un utilisateur',
                'placeholder' => 'Choisir un utilisateur',
                'required' => true,
            ])
            ->add('idOffre', EntityType::class, [
                'class' => OffreEmploi::class,
                'choice_label' => 'titre', // Adjust based on OffreEmploi entity
                'label' => 'Offre d\'emploi',
                'placeholder' => 'Choisir une offre',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => StatutCandidature::EN_ATTENTE,
                    'Acceptée' => StatutCandidature::ACCEPTEE,
                    'Refusée' => StatutCandidature::REFUSEE,
                ],
                'choice_value' => fn (?StatutCandidature $enum) => $enum?->value,
                'choice_label' => fn (StatutCandidature $enum) => $enum->label(),
                'label' => 'Statut de la candidature',
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
        ]);
    }
}
