<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // pas stocké tel quel : on le hashe dans le contrôleur
                'constraints' => [
                    new NotBlank(message: 'Choisissez un mot de passe.'),
                    new Length(min: 6, minMessage: 'Le mot de passe doit faire au moins {{ limit }} caractères.', max: 4096),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
