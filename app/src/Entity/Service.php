<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\References\Profil;
use App\Entity\Traits\HorodatageTrait;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ServiceRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *    fields={"label"},
 *    message="Ce libellé est déjà utilisé.",
 *    repositoryMethod="libelleServiceDejaUtilise"
 * )
 * @UniqueEntity(
 *    fields={"balf"},
 *    message="Cette BALF est déjà utilisée.",
 *    repositoryMethod="BalfDejaUtilise"
 * )
 */
class Service
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $balf;

    /**
     * @ORM\ManyToOne(targetEntity=Profil::class, inversedBy="services")
     * @ORM\JoinColumn(nullable=false)
     */
    private $profil;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $archiveLe;

    /**
     * @ORM\ManyToMany(targetEntity=Application::class, inversedBy="services")
     */
    private $perimetreApplicatif;

    /**
     * @ORM\OneToMany(targetEntity=Application::class, mappedBy="exploitant")
     */
    private $applicationsExploitant;

    /**
     * @ORM\OneToMany(targetEntity=Application::class, mappedBy="MOE")
     */
    private $applicationsMOE;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="services")
     */
    private $users;

    public function __construct()
    {
        $this->perimetreApplicatif = new ArrayCollection();
        $this->applicationsExploitant = new ArrayCollection();
        $this->applicationsMOE = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getBalf(): ?string
    {
        return $this->balf;
    }

    public function setBalf(?string $balf): self
    {
        $this->balf = $balf;

        return $this;
    }

    public function getProfil(): ?Profil
    {
        return $this->profil;
    }

    public function setProfil(?Profil $profil): self
    {
        $this->profil = $profil;

        return $this;
    }

    public function getArchiveLe(): ?\DateTimeInterface
    {
        return $this->archiveLe;
    }

    public function setArchiveLe(?\DateTimeInterface $archiveLe): self
    {
        $this->archiveLe = $archiveLe;

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getPerimetreApplicatif(): Collection
    {
        return $this->perimetreApplicatif;
    }

    public function addPerimetreApplicatif(Application $perimetreApplicatif): self
    {
        if (!$this->perimetreApplicatif->contains($perimetreApplicatif)) {
            $this->perimetreApplicatif[] = $perimetreApplicatif;
        }

        return $this;
    }

    public function removePerimetreApplicatif(Application $perimetreApplicatif): self
    {
        $this->perimetreApplicatif->removeElement($perimetreApplicatif);

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getApplicationsExploitant(): Collection
    {
        return $this->applicationsExploitant;
    }

    public function addApplicationExploitant(Application $applicationExploitant): self
    {
        if (!$this->applicationsExploitant->contains($applicationExploitant)) {
            $this->applicationsExploitant[] = $applicationExploitant;
            $applicationExploitant->setExploitant($this);
        }

        return $this;
    }

    public function removeApplicationExploitant(Application $application): self
    {
        if ($this->applicationsExploitant->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getExploitant() === $this) {
                $application->setExploitant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getApplicationsMOE(): Collection
    {
        return $this->applicationsMOE;
    }

    public function addApplicationsMOE(Application $applicationsMOE): self
    {
        if (!$this->applicationsMOE->contains($applicationsMOE)) {
            $this->applicationsMOE[] = $applicationsMOE;
            $applicationsMOE->setMOE($this);
        }

        return $this;
    }

    public function removeApplicationsMOE(Application $applicationsMOE): self
    {
        if ($this->applicationsMOE->removeElement($applicationsMOE)) {
            // set the owning side to null (unless already changed)
            if ($applicationsMOE->getMOE() === $this) {
                $applicationsMOE->setMOE(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }
}
