<?php

namespace App\Entity;

use App\Repository\GroupedAddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GroupedAddressRepository::class)
 */
class GroupedAddress
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $postcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity=Elector::class, mappedBy="groupedAddress")
     */
    private $electors;

    public function __construct()
    {
        $this->electors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, Elector>
     */
    public function getElectors(): Collection
    {
        return $this->electors;
    }

    public function setElectors(array $electors): self
    {
        $this->electors->clear();
        foreach ($electors as $elector) {
            $this->addElector($elector);
        }

        return $this;
    }

    public function addElector(Elector $elector): self
    {
        if (!$this->electors->contains($elector)) {
            $this->electors[] = $elector;
            $elector->setGroupedAddress($this);
        }

        return $this;
    }

    public function removeElector(Elector $elector): self
    {
        if ($this->electors->removeElement($elector)) {
            // set the owning side to null (unless already changed)
            if ($elector->getGroupedAddress() === $this) {
                $elector->setGroupedAddress(null);
            }
        }

        return $this;
    }

    public function removeElectors()
    {
        $this->electors = null;
    }
}
