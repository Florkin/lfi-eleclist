<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $add1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $add2;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $postcode;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $city;

    /**
     * @ORM\OneToOne(targetEntity=Elector::class, mappedBy="address", cascade={"persist", "remove"})
     */
    private $elector;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $apptOccurences;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getAdd1(): ?string
    {
        return $this->add1;
    }

    public function setAdd1(?string $add1): self
    {
        $this->add1 = $add1;

        return $this;
    }

    public function getAdd2(): ?string
    {
        return $this->add2;
    }

    public function setAdd2(?string $add2): self
    {
        $this->add2 = $add2;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getElector(): ?Elector
    {
        return $this->elector;
    }

    public function setElector(?Elector $elector): self
    {
        // unset the owning side of the relation if necessary
        if ($elector === null && $this->elector !== null) {
            $this->elector->setAddress(null);
        }

        // set the owning side of the relation if necessary
        if ($elector !== null && $elector->getAddress() !== $this) {
            $elector->setAddress($this);
        }

        $this->elector = $elector;

        return $this;
    }

    public function getApptOccurences(): ?int
    {
        return $this->apptOccurences;
    }

    public function setApptOccurences(?int $apptOccurences): self
    {
        $this->apptOccurences = $apptOccurences;

        return $this;
    }
}
