<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class PostController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    #[Route('/', name: 'app_post')]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post();

        $posts = $this->em->getRepository(Post::class)->findAllPosts();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {
            $file = $form->get('file')->getData();
            $url = str_replace(' ', '-', $form->get('title')->getData());

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('files_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Error al subir el archivo');
                }

                $post->setFile($newFilename);
            }

            $post->setUrl($url);

            $user = $this->em->getRepository(User::class)->find(1);
            $post->setUser($user);

            $this->em->persist($post);
            $this->em->flush();
            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts,
        ]);
    }

    /* #[Route('/insert/post', name: 'insert_post')]
    public function insert() {
        $post = new Post('Post insertado', 'Opinion', 'descripcion del post insertado',  'archivo.pdf', 'url-del-post.com');

        $user = $this->em->getRepository(User::class)->find(1);

        $post->setUser($user);

        $post->setTitle('Nuevo post')
            ->setDescription('Hola mundo')
            ->setType('Tutorial')
            ->setUrl('mi-url.com')
            ->setFile('mi-archivo.pdf')
            ->setCreationDate(new \DateTime())
            ->setUser($user);
        
        $this->em->persist($post);
        $this->em->flush();

        return new JsonResponse(['sussess' => true]);
    }

    #[Route('/update/post', name: 'update_post')]
    public function update() {
        $post = $this->em->getRepository(Post::class)->find(4);
        $post->setTitle('Post actualizado');
        $this->em->flush();
        return new JsonResponse(['sussess' => true]);
    }

    #[Route('/delete/post', name: 'delete_post')]
    public function delete() {
        $post = $this->em->getRepository(Post::class)->find(4);
        $this->em->remove($post);
        $this->em->flush();
        return new JsonResponse(['sussess' => true]);
    } */
}
