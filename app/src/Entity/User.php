<?php

namespace App\Entity;

use App\Entity\Service;
use App\Entity\Traits\HorodatageTrait;
use App\Repository\UserRepository;
use App\Utils\ChaineDeCaracteres;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`", indexes={
 *      @ORM\Index(name="user_login_idx", columns={"login"}),
 *      @ORM\Index(name="user_supprime_le_idx", columns={"supprime_le"}),
 *      @ORM\Index(name="user_motdepasse_idx", columns={"motdepasse"}),
 *      @ORM\Index(name="user_nom_idx", columns={"nom"}),
 *      @ORM\Index(name="user_prenom_idx", columns={"prenom"}),
 *      @ORM\Index(name="user_balp_idx", columns={"balp"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
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
    private $login;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motdepasse;

    /**
     * @Assert\Email
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $balp;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="users")
     */
    private $services;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $supprimeLe;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getMotdepasse(): ?string
    {
        return $this->motdepasse;
    }

    public function setMotdepasse(?string $motdepasse): self
    {
        $this->motdepasse = $motdepasse;

        return $this;
    }

    public function getBalp(): ?string
    {
        return $this->balp;
    }

    public function setBalp(string $balp): self
    {
        $this->balp = $balp;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

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
            $service->addUser($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            $service->removeUser($this);
        }

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
     * UserInterface - L'identifiant unique d'un utilisateur "affichable" (donc autre que son id) - remplace l'ancienne méthode getUsername() dépreciée.
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }

    /**
     * UserInterface - Retourne un identifiant unique d'un utilisateur "affichable" - dépréciée au profit de la méthode getUserIdentifier()
     * @see UserInterface
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->login;
    }

    /**
     * UserInterface - Retourne un tableau contenant les rôles de l'utilisateurs (attention certains rôles peuvent en cacher d'autres => https://symfony.com/doc/current/security.html#hierarchical-roles)
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $meilleurService = null;
        foreach ($this->getServices() as $service) {
            if (null === $meilleurService || null === $meilleurService->getProfil()
                    || (null !== $service->getProfil() && $service->getProfil()->getPriorite() > $meilleurService->getProfil()->getPriorite())) {
                $meilleurService = $service;
            }
        }
        if (null !== $meilleurService && null !== ($profil = $meilleurService->getProfil())) {
            return $profil->getRoles();
        }
        return ['ROLE_INVITE'];
    }

    /**
     * PasswordAuthenticatedUserInterface - Retourne le mot de passe; hashé si il vient de la base de données.
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->getMotdepasse();
    }

    /**
     * UserInterface - Cette méthode renvoie null car elle n'est plus utilisée avec les générateurs de hashé modernes (style bcrypt, sodium, ...).
     *  (mais comme elle fait encore partie de l'interface nous sommes obligé de la déclarer)
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * UserInterface - Permet de supprimer des informations temporaires sensibles lorsque l'utilisateur est stocké (comme par exemple un mot de passe en clair).
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        //$this->motdepasse = null;
    }

    /**
     * Retourne le login à partir de l'adresse mail
     * @return string
     */
    public static function getLoginFromBalp(string $balp): string
    {
        $balp = strtolower($balp);
        if (false !==($pos = strpos($balp, '@'))) {
            return substr($balp, 0, $pos);
        }
        return $balp;
    }

    /**
     * Modifie les propriétés de l'objet User courant
     */
    public function normalize(): void
    {
        $this->nom = mb_strtoupper($this->nom);
        $this->prenom = ucwords(strtolower($this->prenom));
        $this->balp = strtolower($this->balp);
        $this->login = self::getLoginFromBalp($this->balp);
    }

    public function getNomCompletCourt(): string
    {
        return ChaineDeCaracteres::prenomNomAbrege($this->getPrenom(), $this->getNom());
    }
}
