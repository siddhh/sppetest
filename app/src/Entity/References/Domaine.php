<?php

namespace App\Entity\References;

use App\Entity\Application;
use App\Repository\References\DomaineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DomaineRepository::class)
 */
class Domaine extends Reference
{
    /**
     * @ORM\ManyToOne(targetEntity=Domaine::class, inversedBy="sousDomaines")
     */
    private $domaineParent;

    /**
     * @ORM\OneToMany(targetEntity=Domaine::class, mappedBy="domaineParent")
     */
    private $sousDomaines;

    /**
     * @ORM\OneToMany(targetEntity=Application::class, mappedBy="sousDomaine")
     */
    private $applications;

    public function __construct()
    {
        $this->sousDomaines = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    public function getDomaineParent(): ?self
    {
        return $this->domaineParent;
    }

    public function setDomaineParent(?self $domaine): self
    {
        $this->domaineParent = $domaine;

        return $this;
    }

    /**
     * @return Collection|Domaine[]
     */
    public function getSousDomaines(): Collection
    {
        $sousDomaines = new ArrayCollection();
        foreach ($this->sousDomaines as $sousDomaine) {
            if ($sousDomaine->getSupprimele() == null) {
                $sousDomaines->add($sousDomaine);
            }
        }
        return $sousDomaines;
    }

    public function addSousDomaine(self $domaine): self
    {
        if (!$this->sousDomaines->contains($domaine)) {
            $this->sousDomaines[] = $domaine;
            $domaine->setDomaineParent($this);
        }

        return $this;
    }
    public function removeSousDomaine(self $domaine): self
    {
        if ($this->sousDomaines->removeElement($domaine)) {
            // set the owning side to null (unless already changed)
            if ($domaine->getDomaineParent() === $this) {
                $domaine->setDomaineParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setDomaine($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): self
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getDomaine() === $this) {
                $application->setDomaine(null);
            }
        }

        return $this;
    }
}
