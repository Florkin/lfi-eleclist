<?php

namespace App\Entity;

use App\Repository\ElectorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ElectorRepository::class)
 */
class Elector
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
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $birthname;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $vote_office;

    /**
     * @ORM\OneToOne(targetEntity=Address::class, inversedBy="elector", cascade={"persist", "remove"})
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity=GroupedAddress::class, inversedBy="electors")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $groupedAddress;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthname(): ?string
    {
        return $this->birthname;
    }

    public function setBirthname(?string $birthname): self
    {
        $this->birthname = $birthname;

        return $this;
    }

    public function getVoteOffice(): ?string
    {
        return $this->vote_office;
    }

    public function setVoteOffice(string $vote_office): self
    {
        $this->vote_office = $vote_office;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getGroupedAddress(): ?GroupedAddress
    {
        return $this->groupedAddress;
    }

    public function setGroupedAddress(?GroupedAddress $groupedAddress): self
    {
        $this->groupedAddress = $groupedAddress;

        return $this;
    }
}
