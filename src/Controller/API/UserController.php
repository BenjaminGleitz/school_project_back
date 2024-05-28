<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/user', name: 'api_user_')]
class UserController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(UserService $userService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $users = $userService->findAll();
            $data = $serializer->serialize($users, 'json', ['groups' => 'getUser']);
            return new JsonResponse($data, 200, [], true);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(UserService $userService, SerializerInterface $serializer, int $id): JsonResponse
    {
        try {
            $user = $userService->find($id);
            $jsonContent = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // update the user
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(int $id, Request $request, UserService $userService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $currentUser = $this->getUser();
            if ($currentUser->getId() !== $id) {
                return $this->json(['error' => 'You are not allowed to update this user'], 403);
            }

            $updatedUser = $userService->update($id, $request->getContent());
            $jsonContent = $serializer->serialize($updatedUser, 'json', ['groups' => 'getUser']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //get image of the user
    #[Route('/{id}/image', name: 'image', methods: ['GET'])]
    public function image(int $id, UserService $userService): JsonResponse
    {
        try {
            $user = $userService->find($id);
            $image = $user->getImageUrl();
            return new JsonResponse($image, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}