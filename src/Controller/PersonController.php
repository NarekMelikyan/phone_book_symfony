<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Phone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;


class PersonController extends AbstractController
{
    /**
     * @Route("/persons", name="person_list")
     */
    public function index()
    {
        $persons = $this->getDoctrine()->getRepository(Person::class)->findAll();
        return $this->json($persons, 200);
    }

    /**
     * @Route("/persons/{id}", name="single_person")
     */
    public function show($id)
    {
        $person = $this->getDoctrine()->getRepository(Person::class)->find($id);
        if (!$person) {
            throw $this->createNotFoundException(
                'No person found with id ' . $id
            );
        }

        $conn = $this->getDoctrine()->getConnection();

        $sql = "SELECT * FROM phone WHERE person_id = " . $person->getId();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $numbers = $stmt->fetchAll();

        return $this->json(["person" => $person, "numbers" => $numbers], 200);
    }

    /**
     * @Route("/persons", name="create_person")
     */
    public function save(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $person = new Person();
        $person->setFirstName($data["first_name"]);
        $person->setLastName($data["last_name"]);
        $person->setEmail($data["email"]);
        $entityManager->persist($person);
        $entityManager->flush();

        $phones = $data['phone'];
        foreach ($phones as $phone) {
            $phone_number = new Phone();
            $phone_number->setPersonId($person->getId());
            $phone_number->setNumber($phone);
            $entityManager->persist($phone_number);
            $entityManager->flush();
        }

        return $this->json($person, 201);
    }

    /**
     * @Route("/persons/{id}", methods={"PUT"}, name="update_person")
     */
    public function update(Request $request, $id)
    {
        $data = json_decode($request->getContent(), true);
        $entityManager = $this->getDoctrine()->getManager();

        $person = $entityManager->getRepository(Person::class)->find($id);
        if (!$person) {
            throw $this->createNotFoundException(
                'No person found with id ' . $id
            );
        }

        $person->setFirstName($data['first_name']);
        $person->setLastName($data['last_name']);
        $entityManager->flush();

        $conn = $this->getDoctrine()->getConnection();

        $existingPhoneNumbers = $this->getPersonExistingNumbers($person->getId());
        $newPhoneNumbers = $data['phone'];

        $phoneNumbersForDelete = array_diff($existingPhoneNumbers, $newPhoneNumbers);
        $phoneNumbersForCreate = array_diff($newPhoneNumbers, $existingPhoneNumbers);

        foreach ($phoneNumbersForDelete as $item) {
            $sql2 = "DELETE FROM phone WHERE number = " . $item;
            $stmt = $conn->prepare($sql2);
            $stmt->execute();
        }

        foreach ($phoneNumbersForCreate as $phone) {
            $phone_number = new Phone();
            $phone_number->setPersonId($person->getId());
            $phone_number->setNumber($phone);
            $entityManager->persist($phone_number);
            $entityManager->flush();
        }

        $sql3 = "SELECT * FROM phone WHERE person_id = " . $person->getId();
        $stmt = $conn->prepare($sql3);
        $stmt->execute();
        $newNumbers = $stmt->fetchAll();

        return $this->json(['person' => $person, "numbers" => $newNumbers], 201);
    }

    /**
     * Endpoint for getting person existing phone numbers from DB.
     * @param $person_id
     * @return array
     */
    private function getPersonExistingNumbers($person_id)
    {
        $conn = $this->getDoctrine()->getConnection();

        $sql = "SELECT * FROM phone WHERE person_id = " . $person_id;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $existingNumbers = $stmt->fetchAll();
        $numbersArray = [];
        foreach ($existingNumbers as $number) {
            $numbersArray[] = $number['number'];
        }
        return $numbersArray;
    }

    /**
     * @Route("/persons/{id}", name="delete_person")
     */
    public function delete($id)
    {
        $conn = $this->getDoctrine()->getConnection();
        $entityManager = $this->getDoctrine()->getManager();

        $person = $entityManager->getRepository(Person::class)->find($id);
        if (!$person) {
            throw $this->createNotFoundException(
                'No person found with id ' . $id
            );
        }
        $entityManager->remove($person);
        $entityManager->flush();

        $sql = "DELETE FROM phone WHERE person_id = " . $id;
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return new Response("Person with id $id and his/her phone numbers are deleted successfully!", 204);
    }
}
