<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Application;
use App\Entity\User;
use App\Entity\References\Profil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez indiquer un libellé de service.',
                    ]),
                ]
            ])
            ->add('balf', EmailType::class, [
                'required' => true,
                'attr'  => [
                    'placeholder' => 'Recherche dans l\'annuaire LDAP ...',
                ],
            ])
            ->add('profil', EntityType::class, [
                'class' => Profil::class,
                'required' => true,
                'choice_label' => 'label',
                'multiple' => false,
                'expanded' => false,
                'label' => 'Profil :',
                'placeholder' => 'Sélectionnner un profil',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('LOWER(p.label)', 'ASC');
                }
            ])
            ->add('perimetreApplicatif', EntityType::class, [
                'class' => Application::class,
                'required' => false,
                'choice_label' => 'label',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Application :',
                'placeholder' => 'Saisir une application...',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.supprimeLe IS NULL')
                        ->orderBy('LOWER(a.label)', 'ASC');
                }
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'choice_label' => 'balp',
                'multiple' => true,
                'expanded' => false,
                'label' => 'balp :',
                'placeholder' => 'Saisir un utilisateur...',
                'by_reference' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.supprimeLe IS NULL')
                        ->orderBy('LOWER(u.nom)', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
