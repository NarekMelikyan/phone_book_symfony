<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Phone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        return $this->json(["person" => $person], 200);
    }

    /**
     * @Route("/persons", name="create_person")
     */
    public function save(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'first_name' => [new Assert\Length(['min' => 2, 'max' => '20']), new Assert\NotBlank],
            'last_name' => [new Assert\Length(['min' => 2, 'max' => '20']), new Assert\NotBlank],
            'email' => [new Assert\Email(), new Assert\notBlank],
            'phone' => [new Assert\All([new Assert\Regex('^[+]?\d+$^')]), new Assert\notBlank]
        ]);
        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $accessor = PropertyAccess::createPropertyAccessor();

            $errorMessages = [];

            foreach ($violations as $violation) {

                $accessor->setValue($errorMessages,
                    $violation->getPropertyPath(),
                    $violation->getMessage());
            }
            return $this->json($errorMessages, 400);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $person = new Person();
        $person->setFirstName($data["first_name"]);
        $person->setLastName($data["last_name"]);
        $person->setEmail($data["email"]);
        $entityManager->persist($person);
        $entityManager->flush();

        $phones = $data['phone'];
        foreach ($phones as $phone) {
            $phone_number = new Phone();
            $person_find = $entityManager->getRepository(Person::class)->find($person->getId());
            $phone_number->setPersonId($person->getId());
            $phone_number->setNumber($phone);
            $phone_number->setPerson($person_find);
            $entityManager->persist($phone_number);
            $entityManager->flush();
        }

        $createdPerson = $this->getDoctrine()->getRepository(Person::class)->find($person->getId());
        return $this->json($createdPerson, 201);
    }

    /**
     * @Route("/persons/{id}", methods={"PUT"}, name="update_person")
     */
    public function update(Request $request, ValidatorInterface $validator, $id)
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'first_name' => [new Assert\Length(['min' => 2, 'max' => '20']), new Assert\NotBlank],
            'last_name' => [new Assert\Length(['min' => 2, 'max' => '20']), new Assert\NotBlank],
            'phone' => [new Assert\All([new Assert\Regex('^[+]?\d+$^')]), new Assert\notBlank]
        ]);
        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $accessor = PropertyAccess::createPropertyAccessor();

            $errorMessages = [];

            foreach ($violations as $violation) {

                $accessor->setValue($errorMessages,
                    $violation->getPropertyPath(),
                    $violation->getMessage());
            }
            return $this->json($errorMessages, 400);
        }

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

        $existingPhoneNumbers = $this->getPersonExistingNumbers($person->getId());
        $newPhoneNumbers = $data['phone'];

        $phoneNumbersForDelete = array_diff($existingPhoneNumbers, $newPhoneNumbers);
        $phoneNumbersForCreate = array_diff($newPhoneNumbers, $existingPhoneNumbers);

        foreach ($phoneNumbersForDelete as $item) {
            $phoneNumber = $entityManager->getRepository(Phone::class)->findBy(['number' => $item])[0];
            $entityManager->remove($phoneNumber);
            $entityManager->flush();
        }

        foreach ($phoneNumbersForCreate as $phone) {
            $person_find = $entityManager->getRepository(Person::class)->find($person->getId());
            $phone_number = new Phone();
            $phone_number->setPersonId($person->getId());
            $phone_number->setNumber($phone);
            $phone_number->setPerson($person_find);
            $entityManager->persist($phone_number);
            $entityManager->flush();
        }

        $updatedPerson = $this->getDoctrine()->getRepository(Person::class)->find($person->getId());
        return $this->json(['person' => $updatedPerson], 201);
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
        $entityManager = $this->getDoctrine()->getManager();

        $person = $entityManager->getRepository(Person::class)->find($id);
        if (!$person) {
            throw $this->createNotFoundException(
                'No person found with id ' . $id
            );
        }

        $phoneNumbers = $entityManager->getRepository(Phone::class)->findBy(['person_id' => $id]);
        foreach ($phoneNumbers as $item){
            $itemPhone = $entityManager->getRepository(Phone::class)->find($item->getId());
            $entityManager->remove($itemPhone);
            $entityManager->flush();
        }

        return new Response("Person with id $id and his/her phone numbers are deleted successfully!", 204);
    }
}
