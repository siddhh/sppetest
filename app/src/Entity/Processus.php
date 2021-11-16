<?php

namespace App\Entity;

use App\Repository\ProcessusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HorodatageTrait;

/**
 * @ORM\Entity(repositoryClass=ProcessusRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Processus
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
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $objet;

    /**
     * @ORM\ManyToOne(targetEntity=Application::class, inversedBy="processuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $application;

    /**
     * @ORM\OneToMany(targetEntity=Job::class, mappedBy="processus")
     */
    private $jobs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $chaineDePlanification;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $descriptionPlanification;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $DebutValidite;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finValidite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $versionImportee;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateVersionImportee;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $libelleVersionImportee;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $supprimeLe;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(?string $objet): self
    {
        $this->objet = $objet;

        return $this;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return Collection|Job[]
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): self
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs[] = $job;
            $job->setProcessus($this);
        }

        return $this;
    }

    public function removeJob(Job $job): self
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getProcessus() === $this) {
                $job->setProcessus(null);
            }
        }

        return $this;
    }

    public function getChaineDePlanification(): ?string
    {
        return $this->chaineDePlanification;
    }

    public function setChaineDePlanification(?string $chaineDePlanification): self
    {
        $this->chaineDePlanification = $chaineDePlanification;

        return $this;
    }

    public function getDescriptionPlanification(): ?string
    {
        return $this->descriptionPlanification;
    }

    public function setDescriptionPlanification(?string $descriptionPlanification): self
    {
        $this->descriptionPlanification = $descriptionPlanification;

        return $this;
    }

    public function getDebutValidite(): ?\DateTimeInterface
    {
        return $this->DebutValidite;
    }

    public function setDebutValidite(?\DateTimeInterface $DebutValidite): self
    {
        $this->DebutValidite = $DebutValidite;

        return $this;
    }

    public function getFinValidite(): ?\DateTimeInterface
    {
        return $this->finValidite;
    }

    public function setFinValidite(?\DateTimeInterface $finValidite): self
    {
        $this->finValidite = $finValidite;

        return $this;
    }

    public function getVersionImportee(): ?string
    {
        return $this->versionImportee;
    }

    public function setVersionImportee(?string $versionImportee): self
    {
        $this->versionImportee = $versionImportee;

        return $this;
    }

    public function getDateVersionImportee(): ?\DateTimeInterface
    {
        return $this->dateVersionImportee;
    }

    public function setDateVersionImportee(?\DateTimeInterface $dateVersionImportee): self
    {
        $this->dateVersionImportee = $dateVersionImportee;

        return $this;
    }

    public function getLibelleVersionImportee(): ?string
    {
        return $this->libelleVersionImportee;
    }

    public function setLibelleVersionImportee(?string $libelleVersionImportee): self
    {
        $this->libelleVersionImportee = $libelleVersionImportee;

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
}
