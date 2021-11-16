<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    /**
     * Ajoute de nouvelles fonctions à Twig
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('class', [$this, 'getClass']),
        ];
    }

    /**
     * Ajoute de nouveaux filtres à Twig
     */
    public function getFilters()
    {
        return [
        ];
    }

    /**
     * Retourne la class d'un objet
     */
    public function getClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}
