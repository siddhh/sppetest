<?php

namespace App\Form\References;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\References\Domaine;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class DomaineType extends ReferenceType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('domaineParent', EntityType::class, [
                'class' => Domaine::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->where('d.supprimeLe is NULL')
                        ->orderBy('d.id', 'ASC');
                },
                'choice_label' => 'id',
            ])
        ;
    }

    // désactive le champ CSRF... à voir si nécessaire par la suite.
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Domaine::class,
            'csrf_protection'   => false,
        ]);
    }
}
