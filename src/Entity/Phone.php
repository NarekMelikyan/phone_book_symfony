<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhoneRepository")
 */
class Phone
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="integer")
     */
    private $person_id;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="phones", cascade={"remove"})
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getPersonId(): ?int
    {
        return $this->person_id;
    }

    public function setPersonId(int $person_id): self
    {
        $this->person_id = $person_id;
        return $this;
    }
//    public function getPerson()
//    {
//        return $this->person;
//    }

    public function setPerson($person): self
    {
        $this->person = $person;
        return $this;
    }
}
