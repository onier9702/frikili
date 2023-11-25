<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
// use App\Form\PostType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{

    #[Route('/post', name: 'app_post')]
    public function createPost(Request $request, 
                               ManagerRegistry $doctrine,
                               SluggerInterface $slugger): Response
    {   
        $em = $doctrine->getManager();
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $brochureFile = $form->get('photo')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setPhoto($newFilename);
            }

            $user = $this->getUser();
            $post->setUser($user);

            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
            'formPost'=>$form->createView()
        ]);
    }

    #[Route('/post/{id}', name: 'see_posts')]
    public function seeOnePostByID($id, ManagerRegistry $doctrine) {
        $em = $doctrine->getManager();
        $post = $em->getRepository(Post::class)->find($id); // this find method is from symfony and it uses all get and setter
        return $this->render('post/seePost.html.twig', [
            'single_post' => $post
        ]);
    }

    #[Route('/my_posts', name: 'my_posts')]
    public function myPosts(ManagerRegistry $doctrine) {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $posts = $em->getRepository(Post::class)->findBy(['user' => $user]);
        return $this->render('post/myPosts.html.twig', [
            'my_posts' => $posts
        ]);
    }

    #[Route('/likes', name: 'likes', options: ['expose' => true])]
    public function likes(Request $request, ManagerRegistry $doctrine) {
        if ($request->isXmlHttpRequest()) {
            $em = $doctrine->getManager();
            $user = $em->getRepository(User::class);
            $id = $request->request->get('id'); // parameter of ajax-call.js function
            $post = $em->getRepository(Post::class)->find($id);
            $likes = $post->getLikes();
            $likes .= $user->getId().',';
            $post->setLikes($likes);
            $em->flush();
            return new JsonResponse(['ikes' => $likes]);


        } else {
            throw new Exception('It is not permitted');
        };
    }
}
