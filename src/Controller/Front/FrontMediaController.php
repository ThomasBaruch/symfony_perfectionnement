<?php

namespace App\Controller\Front;

use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontMediaController extends AbstractController
{
    /**
     *@Route("/front/medias", name="front_media_list")
     */
    public function listMedia(MediaRepository $mediaRepository)
    {
        $medias = $mediaRepository->findAll();

        return $this->render("front/medias.html.twig", ['medias' => $medias]);
    }


    /**
     * @Route("front/media/{id}", name="front_show_media")
     */
    public function showMedia(MediaRepository $mediaRepository, $id)
    {
        $media = $mediaRepository->find($id);

        return $this->render("front/media.html.twig", ['media' => $media]);
    }

    /**
     * @Route("front/like/media/{id}", name="media_like")
     */
    public function likeMedia($id, MediaRepository $mediaRepository, EntityManagerInterface $entityManagerInterface, LikeRepository $likeRepository)
    {
        $media = $mediaRepository->find($id);
        $user = $this->getUser();

        if(!$user){
            return $this->json([
                'code' => 403,
                'message' => "Vous devez être connecté pour aimer une publication"
            ], 403);
        }

        if($media->isLikedByUser($user)){
            $like = $likeRepository->findOneBy([
                'media' => $media,
                'user' => $user
            ]);

            $entityManagerInterface->remove($like);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Le like a été supprimé",
                'likes' => $likeRepository->count(['media' => $media])
            ], 200);
        }

        $like = new Like();
        $like->setMedia($media);
        $like->setUser($user);

        $entityManagerInterface->persist($like);
        $entityManagerInterface->flush();

        return $this->json([
            'code' => 200,
            'message' => "Le like a été enregistré",
            'likes' => $likeRepository->count(['media' => $media])
        ], 200);
    }
}
