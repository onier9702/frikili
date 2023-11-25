<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class RegisterController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function createUser(Request $request, 
                                ManagerRegistry $doctrine,
                                LoggerInterface $logger,
                                UserPasswordHasherInterface $passwordHasher,
    ): Response
    {
        $em = $doctrine->getManager();

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form['password']->getData()
            );
            $user->setPassword($hashedPassword);

            // $logger->info($hashedPassword); // console log
            
            //tell Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($user);

            // actually executes the queries (i.e. the INSERT query)
            $em->flush();

            $this->addFlash( 'success', 'You have registered successfully' );
            return $this->redirectToRoute('app_register');
            // return new Response('Saved new user with id '.$user->getId());
        }

        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
            'form'=>$form->createView()
        ]);
    }
}
