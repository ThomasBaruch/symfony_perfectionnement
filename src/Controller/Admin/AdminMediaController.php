<?php

namespace App\Controller\Admin;

use App\Entity\Like;
use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\LikeRepository;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminMediaController extends AbstractController
{

    /**
     * @Route("admin/create/media", name="admin_create_media")
     */
    public function createMedia(Request $request, EntityManagerInterface $entityManagerInterface, SluggerInterface $sluggerInterface)
    {
        $media = new Media();

        $mediaForm = $this->createForm(MediaType::class, $media);

        $mediaForm->handleRequest($request);

        if($mediaForm->isSubmitted() && $mediaForm->isValid()){

            $mediaFile = $mediaForm->get('src')->getData();

            if($mediaFile){
                //On crée un nom unique avec le nom original de l'image pour éviter les pb
                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
                //On utilise slug sur le nom original de l'image pour avoir un nom valide
                $safeFilename = $sluggerInterface->slug($originalFilename);
                //On ajoute un id unique au nom de l'image
                $newFilename = $safeFilename . '-' . uniqid() . '.' .$mediaFile->guessExtension();


                //On déplace le fichier dans le dossier public/media
                //La destination du fichier est enregistré dans 'image_directory' qui est défini dans le fichier config/services.yaml
                $mediaFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                $media->setSrc($newFilename);
            }

            $media->setAlt($mediaForm->get('title')->getData());

            $entityManagerInterface->persist($media);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_category_list");

        }

        return $this->render('admin/mediaform.html.twig', ['mediaForm' => $mediaForm->createView()]);
    }

    /**
     *@Route("/admin/medias", name="admin_media_list")
     */
    public function listMedia(MediaRepository $mediaRepository)
    {
        $medias = $mediaRepository->findAll();

        return $this->render("admin/medias.html.twig", ['medias' => $medias]);
    }


    /**
     * @Route("admin/media/{id}", name="admin_show_media")
     */
    public function showMedia(MediaRepository $mediaRepository, $id)
    {
        $media = $mediaRepository->find($id);

        return $this->render("admin/media.html.twig", ['media' => $media]);
    }

    /**
     * @Route("admin/delete/media/{id}", name="admin_media_delete")
     */
    public function deleteMedia($id, MediaRepository $mediaRepository, EntityManagerInterface $entityManagerInterface)
    {
        $media = $mediaRepository->find($id);
        $entityManagerInterface->remove($media);
        $entityManagerInterface->flush();
        $this->addFlash(
            'notice',
            'Votre media a été supprimé'
        );

        return $this->redirectToRoute("admin_media_list");
    }


    /**
     * @Route("admin/like/media/{id}", name="media_like")
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