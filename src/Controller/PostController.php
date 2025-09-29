<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    #[Route('/post/{id}', name: 'app_post')]
    public function index($id): Response
    {
        $post = $this->em->getRepository(Post::class)->find($id);
        $customPost = $this->em->getRepository(Post::class)->findPost($id);

        return $this->render('post/index.html.twig', [
            'post' => $post,
            'customPost' => $customPost,
        ]);
    }

    #[Route('/insert/post', name: 'insert_post')]
    public function insert() {
        $post = new Post('Post insertado', 'Opinion', 'descripcion del post insertado',  'archivo.pdf', 'url-del-post.com');

        $user = $this->em->getRepository(User::class)->find(1);

        $post->setUser($user);

        /* $post->setTitle('Nuevo post')
            ->setDescription('Hola mundo')
            ->setType('Tutorial')
            ->setUrl('mi-url.com')
            ->setFile('mi-archivo.pdf')
            ->setCreationDate(new \DateTime())
            ->setUser($user); */
        
        $this->em->persist($post);
        $this->em->flush();

        return new JsonResponse(['sussess' => true]);
    }
}
