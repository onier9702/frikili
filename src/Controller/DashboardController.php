<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function Dashboard(ManagerRegistry $doctrine, 
                            PaginatorInterface $paginator,
                            Request $request
                            ): Response
    {

        $em = $doctrine->getManager();
        $query = $em->getRepository(Post::class)->findAllPosts();
        
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            2 /*limit per page*/
        );
        // $logger->info($posts);

        return $this->render('dashboard/index.html.twig', [
            // 'posts' => $posts,
            // 'post' => $post
            'pagination' => $pagination

        ]);
    }
}
