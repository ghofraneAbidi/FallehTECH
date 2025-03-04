<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('verificationCode', TextType::class, [
                'label' => 'Verification Code',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the verification code',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'New Password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a new password',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}