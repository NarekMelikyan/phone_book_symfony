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

//    /**
//     * @ORM\OneToMany(targetEntity="App\Entity\Person", mappedBy="phones")
//     */
//    private $phones;
//
//    public function __construct()
//    {
//        $this->phones = new ArrayCollection();
//    }

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

//    /**
//     * @return Collection|Phone[]
//     */
//    public function getPhones(): Collection
//    {
//        return $this->phones;
//    }
//
//    public function addPhone(Phone $phone): self
//    {
//        if (!$this->phones->contains($phone)) {
//            $this->phones[] = $phone;
//            $phone->setPhone($this);
//        }
//
//        return $this;
//    }
//
//    public function removePhone(Phone $phone): self
//    {
//        if ($this->phones->contains($phone)) {
//            $this->phones->removeElement($phone);
//            // set the owning side to null (unless already changed)
//            if ($phone->getPhone() === $this) {
//                $phone->setPhone(null);
//            }
//        }
//
//        return $this;
//    }
}
