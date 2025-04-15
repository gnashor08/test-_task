<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Marvin255\RandomStringGenerator\Generator\RandomStringGenerator;
use Marvin255\RandomStringGenerator\Vocabulary\Vocabulary;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class UserController extends AbstractController
{
    const int DEFAULT_TOKEN_LENGTH = 20;

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'secondName' => $user->getSecondName(),
            'email' => $user->getEmail(),
            'birthdayDate' => $user->getBirthdayDate(),
        ];
    }

    #[Route('/users', name: 'user_index', methods:['get'] )]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {

        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        $data = array_map([$this, 'serializeUser'], $users);

        return $this->json($data);

    }

    #[Route('/user/{id}/', name: 'user_info', methods:['get'] )]
    public function info(EntityManagerInterface $entityManager, int $id): JsonResponse
    {

        $user = $entityManager
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        return $this->json($this->serializeUser($user));
    }

    #[Route('/user', name: 'user_create', methods:['post'] )]
    public function create(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Request $request,
    ): JsonResponse {
        $payload = $request->toArray();

        $user = new User();
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setSecondName($payload['secondName']);
        $user->setBirthdayDate(new \DateTime($payload['birthdayDate']));
        $user->setEmail($payload['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $payload['password']));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user->getId());
    }

    /**
     * @throws Exception
     */
    #[Route('/user/auth', name: 'user_auth', methods:['post'] )]
    public function auth(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Request $request,
        RandomStringGenerator $randomStringGenerator
    ): JsonResponse {
        $payload = $request->toArray();
        $email = $payload['email'];
        $password = $payload['password'];

        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$user) {
            throw new Exception('Incorrect email or password');
        }

        $isPasswordValid = $passwordHasher->isPasswordValid($user, $password);
        if (!$isPasswordValid) {
            throw new Exception('Incorrect email or password');
        }

        $token = $randomStringGenerator->string(self::DEFAULT_TOKEN_LENGTH, Vocabulary::ALPHA_NUMERIC);
        $user->setToken($token);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['token' => $token]);

    }

    #[Route('/user/{id}', name: 'user_update', methods: ['put'])]
    public function updateById(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $payload = $request->toArray();

        if (isset($payload['firstName'])) {
            $user->setFirstName($payload['firstName']);
        }
        if (isset($payload['lastName'])) {
            $user->setLastName($payload['lastName']);
        }
        if (isset($payload['secondName'])) {
            $user->setSecondName($payload['secondName']);
        }
        if (isset($payload['birthdayDate'])) {
            try {
                $date = new \DateTime($payload['birthdayDate']);
                $user->setBirthdayDate($date);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid date format.'], 400);
            }
        }

        $entityManager->flush();

        return $this->json($this->serializeUser($user));
    }

    #[Route('/user/{id}', name: 'user_delete', methods: ['delete'])]
    public function deleteById(
        int $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'The user was successfully deleted']);
    }
}
