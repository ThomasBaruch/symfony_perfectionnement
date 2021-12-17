<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCategoryController extends AbstractController
{
    /**
     *@Route("/admin/categorys", name="admin_category_list")
     */
    public function listCategory(CategoryRepository $categoryRepository)
    {
        $categorys = $categoryRepository->findAll();

        return $this->render("admin/categorys.html.twig", ['categorys' => $categorys]);
    }


    /**
     * @Route("admin/category/{id}", name="admin_show_category")
     */
    public function showCategory(CategoryRepository $categoryRepository, $id)
    {
        $category = $categoryRepository->find($id);

        return $this->render("admin/category.html.twig", ['category' => $category]);
    }

    /**
     * @Route("admin/add/category/", name="admin_category_add")
     */
    public function addCategory(
        EntityManagerInterface $entityManagerInterface,
        Request $request
    ) {

        $category = new Category();

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $entityManagerInterface->persist($category);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_category_list");
        }

        return $this->render('admin/categoryupdate.html.twig', ['categoryForm' => $categoryForm->createView()]);
    }


    /**
     * @Route("admin/update/category/{id}", name="admin_category_update")
     */
    public function categorytUpdate(
        $id,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManagerInterface,
        Request $request
    ) {
        $category = $categoryRepository->find($id);

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $entityManagerInterface->persist($category);
            $entityManagerInterface->flush();

            return $this->redirectToRoute('admin/category_list');
        }

        return $this->render('admin/categoryupdate.html.twig', ['categoryForm' => $categoryForm->createView()]);
    }
}
