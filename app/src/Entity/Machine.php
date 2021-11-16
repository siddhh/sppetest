<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HorodatageTrait;

/**
 * @ORM\Entity(repositoryClass=MachineRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Machine
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $libelle;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $SupprimeLe;

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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getSupprimeLe(): ?\DateTimeInterface
    {
        return $this->SupprimeLe;
    }

    public function setSupprimeLe(?\DateTimeInterface $SupprimeLe): self
    {
        $this->SupprimeLe = $SupprimeLe;

        return $this;
    }
}
