<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    private $userRepository;
    private $entityManager;
    private $validator;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function findAll(): array
    {
        return $this->userRepository->findAll();
    }

    public function find(int $id): User
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        return $user;
    }

    public function update(int $id, string $data): User
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        $userData = json_decode($data, true);

        if (isset($userData['email'])) {
            $user->setEmail($userData['email']);
        }

        if (isset($userData['password'])) {
            $user->setPassword($userData['password']);
        }

        if (isset($userData['roles'])) {
            $user->setRoles($userData['roles']);
        }

        if (isset($userData['firstname'])) {
            $user->setFirstname($userData['firstname']);
        }

        if (isset($userData['lastname'])) {
            $user->setLastname($userData['lastname']);
        }

        if (isset($userData['favoriteCity'])) {
            $city = $this->entityManager->getRepository(City::class)->find($userData['favoriteCity']);
            if (!$city) {
                throw new NotFoundHttpException('City not found.');
            }
            $user->setFavoriteCity($city);
        }

        $violations = $this->validator->validate($user);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new BadRequestHttpException(json_encode($errors));
        }

        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}