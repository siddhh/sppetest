<?php

namespace App\Entity;

use App\Entity\References\Domaine;
use App\Entity\References\Granularite;
use App\Repository\PlanProductionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlanProductionRepository::class)
 */
class PlanProduction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="planProductions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serviceExploitant;

    /**
     * @ORM\ManyToOne(targetEntity=Domaine::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $domaine;

    /**
     * @ORM\ManyToOne(targetEntity=Granularite::class)
     */
    private $granularite;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getServiceExploitant(): ?Service
    {
        return $this->serviceExploitant;
    }

    public function setServiceExploitant(?Service $serviceExploitant): self
    {
        $this->serviceExploitant = $serviceExploitant;

        return $this;
    }

    public function getDomaine(): ?Domaine
    {
        return $this->domaine;
    }

    public function setDomaine(?Domaine $domaine): self
    {
        $this->domaine = $domaine;

        return $this;
    }

    public function getGranularite(): ?Granularite
    {
        return $this->granularite;
    }

    public function setGranularite(?Granularite $granularite): self
    {
        $this->granularite = $granularite;

        return $this;
    }
}
