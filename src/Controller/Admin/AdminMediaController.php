<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
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
}