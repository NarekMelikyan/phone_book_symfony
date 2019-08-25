<?php

namespace App\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 * @UniqueEntity("email")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="first_name", type="text", length=20)
     */
    private $first_name;

    /**
     * @ORM\Column(name="last_name", type="text", length=20)
     */
    private $last_name;

    /**
     * @ORM\Column(name="email", type="string", unique=true, length=50)
     */
    private $email;

//    /**
//     * @ORM\ManyToOne(targetEntity="App\Entity\Phone", inversedBy="phones")
//     * @ORM\JoinColumn(name="id", referencedColumnName="person_id", nullable=false)
//     */
//    private $phones;
//
//    public function __construct()
//    {
//        $this->phones = new ArrayCollection();
//    }
//
//    public function getPhones(): ?Phone
//    {
//        return $this->phones;
//    }
//
//
//    public function setPhones(?Phone $phone): self
//    {
//        $this->phones = $phone;
//        return $this;
//    }

    // Getters and setters

    public function getId()
    {
        return $this->id;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }
}
