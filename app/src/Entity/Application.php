<?php

namespace App\Entity;

use App\Entity\References\Domaine;
use App\Entity\Traits\HorodatageTrait;
use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ApplicationRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *      fields={"label","supprimeLe"},
 *      ignoreNull=false,
 *      errorPath="label",
 *      message="Ce libellé est déjà présent. Veuillez revoir votre saisie"
 * )
 */
class Application
{
    use HorodatageTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity=Domaine::class, inversedBy="applications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sousDomaine;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="applicationsExploitant")
     * @ORM\JoinColumn(nullable=false)
     */
    private $exploitant;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="applicationsMOE")
     */
    private $MOE;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $supprimeLe;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, mappedBy="perimetreApplicatif")
     */
    private $services;

    /**
    * @ORM\OneToMany(targetEntity=Processus::class, mappedBy="application")
    */
    private $processuses;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->processuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getlabel(): ?string
    {
        return $this->label;
    }

    public function setlabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getSousDomaine(): ?Domaine
    {
        return $this->sousDomaine;
    }

    public function setSousDomaine(?Domaine $sousDomaine): self
    {
        $this->sousDomaine = $sousDomaine;

        return $this;
    }

    public function getExploitant(): ?Service
    {
        return $this->exploitant;
    }

    public function setExploitant(?Service $exploitant): self
    {
        $this->exploitant = $exploitant;

        return $this;
    }

    public function getMOE(): ?Service
    {
        return $this->MOE;
    }

    public function setMOE(?Service $MOE): self
    {
        $this->MOE = $MOE;

        return $this;
    }

    public function getSupprimeLe(): ?\DateTimeInterface
    {
        return $this->supprimeLe;
    }

    public function setSupprimeLe(?\DateTimeInterface $supprimeLe): self
    {
        $this->supprimeLe = $supprimeLe;

        return $this;
    }

    /**
     * @return Collection|Service[]
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->addPerimetreApplicatif($this);
        }

        return $this;
    }

    /**
     * @return Collection|Processus[]
     */
    public function getProcessuses(): Collection
    {
        return $this->processuses;
    }

    public function addProcessus(Processus $processus): self
    {
        if (!$this->processuses->contains($processus)) {
            $this->processuses[] = $processus;
            $processus->setApplication($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            $service->removePerimetreApplicatif($this);
        }

        return $this;
    }

    public function removeProcessus(Processus $processus): self
    {
        if ($this->processuses->removeElement($processus)) {
            // set the owning side to null (unless already changed)
            if ($processus->getApplication() === $this) {
                $processus->setApplication(null);
            }
        }

        return $this;
    }
}
