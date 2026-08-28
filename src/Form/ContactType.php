<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom',
                'constraints' => [new NotBlank(message: 'Indiquez votre nom.')],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre e-mail',
                'constraints' => [
                    new NotBlank(message: 'Indiquez votre e-mail.'),
                    new Email(message: 'Adresse e-mail invalide.'),
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'constraints' => [new NotBlank(message: 'Indiquez un sujet.')],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'constraints' => [
                    new NotBlank(message: 'Écrivez votre message.'),
                    new Length(min: 10, minMessage: 'Votre message doit faire au moins {{ limit }} caractères.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Pas de data_class : on récupère un simple tableau via $form->getData().
        $resolver->setDefaults([]);
    }
}
