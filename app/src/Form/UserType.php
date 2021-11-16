<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\User;
use App\Repository\ServiceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom', TextType::class, [
                'required'  => true,
                'constraints'   => [
                    new NotBlank([
                        'message'  => 'Vous devez indiquer le prénom de l\'utilisateur.',
                    ]),
                ]
            ])
            ->add('nom', TextType::class, [
                'required'  => true,
                'constraints'   => [
                    new NotBlank([
                        'message'  => 'Vous devez indiquer le nom de famille de l\'utilisateur.',
                    ]),
                ]
            ])
            ->add('balp', EmailType::class, [
                'required'  => true,
                'constraints'   => [
                    new NotBlank([
                        'message'  => 'Vous devez indiquer l\'adresse de la boite email de l\'utilisateur (balp).',
                    ]),
                ]
            ])
            ->add('motdepasseDisplayed', TextType::class, [
                'required'      => false,
                'mapped'        => false,
                'constraints'   => [
                    new Length([
                        'min'           => 6,
                        'max'           => 32,
                        'minMessage'    => 'Un mot de passe valide doit comporter au moins {{ limit }} caractères.',
                        'maxMessage'    => 'Un mot de passe valide ne doit pas dépasser {{ limit }} caractères.',
                    ])
                ],
            ])
            ->add('motdepasseUpdate', CheckboxType::class, [
                'required'  => false,
                'mapped'    => false,
                'data'      => false,
            ])
            ->add('services', EntityType::class, [
                'required'      => true,
                'class'         => Service::class,
                'choice_label'  => 'label',
                'multiple'      => true,
                'expanded'      => false,
                'by_reference'  => false,
                'query_builder' => function (ServiceRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.label', 'ASC');
                }
            ])
            ->add('actionSave', SubmitType::class)
            ->add('actionRemove', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => User::class,
        ]);
    }
}
