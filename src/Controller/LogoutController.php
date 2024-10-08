<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\TokenService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class LogoutController extends AbstractController
{
    public function __construct()
    {
        $this->token = null;
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['GET'])]
    public function logout(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine, TokenService $tokenService): JsonResponse
    {

        $this->token = $tokenService->translateTokenFromCookie($request->cookies->get('refresh_token'));
        // Récupérer l'utilisateur en fonction du token
        $users = $userRepository->findAll();
        $userdb = null;
        foreach ($users as $user) {
            // Si le token est dans le tableau
            foreach ($user->getToken() as $token) {
                if ($token == $this->token) {
                    $userdb = $user;
                }
            }
        }
        if (!$userdb) {
            return $this->json([
                'message' => 'missing credentials',
            ], 401);
        }
        // Supprimer le token du cookie dans getToken
        foreach ($userdb->getToken() as $token) {
            if ($token != $this->token) {
                $userdb->setToken(array($token));
            }
        }
        $em = $doctrine->getManager();
        $em->persist($userdb);
        $em->flush();

        $response = new JsonResponse([ 'message' => 'Logged Out' ], 200);
        $response->headers->setCookie(
            new Cookie(
                'refresh_token',
                'delete',
                new \DateTime('now - 1 hour'),
                '/',
                $_ENV['COOKIE_DOMAIN'],
                true,
                true,
                false,
                'none'
            ));
            $response->headers->setCookie(
                new Cookie(
                    'refresh_token_sso',
                    'delete',
                    new \DateTime('now - 1 hour'),
                    '/',
                    $_ENV['COOKIE_DOMAIN'],
                    true,
                    true,
                    false,
                    'none'
                ));
                $response->headers->setCookie(
                    new Cookie(
                        't_refresh_token_sso',
                        'delete',
                        new \DateTime('now - 1 hour'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
                        true,
                        true,
                        false,
                        'none'
                    ));
                $response->headers->setCookie(
                    new Cookie(
                        'access_token_sso',
                        'delete',
                        new \DateTime('now - 1 hour'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
                        true,
                        true,
                        false,
                        'none'
                    ));
                    $response->headers->setCookie(
                        new Cookie(
                            'broadcaster_id',
                            'delete',
                            new \DateTime('now - 1 hour'),
                            '/',
                            $_ENV["COOKIE_DOMAIN"],
                            true,
                            true,
                            false,
                            'none'
                        ));
                    $response->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            'delete',
                            new \DateTime('now - 1 hour'),
                            '/',
                            $_ENV['COOKIE_DOMAIN'],
                            true,
                            true,
                            false,
                            'none'
                        ));
        return $response;
    }
}
