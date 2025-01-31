<?php

namespace App\Controller\API;

use App\Entity\City;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route("/userRegister", name: "new", methods: ["POST"])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer, SluggerInterface $slugger): JsonResponse
    {
        try {
            // Pour les données JSON
            $data = $request->request->all();

            // Pour les fichiers
            $imageFile = $request->files->get('imageFile');

            $user = new User();
            $user->setEmail($data['email'] ?? '');
            $password = $data['password'] ?? '';
            $user->setPassword($password ? $passwordHasher->hashPassword($user, $password) : '');
            $user->setRoles($data['roles'] ?? []);
            $user->setFirstname($data['firstname'] ?? '');
            $user->setLastname($data['lastname'] ?? '');
            $user->setGender($data['gender'] ?? '');
            $user->setNationality($data['nationality'] ?? '');
            $user->setBirthdate(new \DateTimeImmutable($data['birthdate'] ?? ''));
            $user->setDescription($data['description'] ?? '');
            $user->setCreatedAt(new \DateTimeImmutable());

            $favoriteCity = $data['favoriteCity'] ?? null;
            if ($favoriteCity) {
                $city = $entityManager->getRepository(City::class)->find($favoriteCity);
                if (!$city) {
                    return new JsonResponse(['error' => 'City not found'], 404);
                }
                $user->setFavoriteCity($city);
            }

            // Gestion de l'upload de l'image
            if ($imageFile) {
                $user->setImageFile($imageFile);
            }

            $violations = $validator->validate($user);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return $this->json($errors, 400);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $userData = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
            return new JsonResponse($userData, 201, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
