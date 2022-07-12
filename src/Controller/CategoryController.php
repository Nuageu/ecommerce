<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

class CategoryController extends AbstractController
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    public function renderMenuList()
    {
        // Affichage des catégories dans la navbar
        //1. aller chercher les catégories dans la base de données (repository)
        $categories = $this->categoryRepository->findAll();

        //2. Renvoyer le rendu HTML sous la forme d'une Response ($this->render)
        return $this->render('category/_menu.html.twig', [
            'categories' => $categories
        ]);
    }


    #[Route('/admin/category/create', name: 'category_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $category = new Category;

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug(strtolower($slugger->slug($category->getName())));

            $em->persist($category);
            $em->flush();


            return $this->redirectToRoute('homepage');
        }

        $formView = $form->createView();

        return $this->render('category/create.html.twig', [
            'formView' => $formView
        ]);
    }

    #[Route('/admin/category/{id}/edit', name: 'category_edit')]

    // #[isGranted("ROLE_ADMIN", message: "Vous n'avez pas le droit d'acceder à cette ressources")]
    // #[isGranted("CAN_EDIT", subject: 'id', message: "Vous n'êtes pas le propriètaire de cette catégory")]

    public function edit($id, CategoryRepository $categoryRepository, Request $request,  SluggerInterface $slugger, EntityManagerInterface $em, Security $security): Response
    {
        // $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'avez pas le droit d'accéder a cette ressource");

        // $user = $security->getUser();
        // // $user = $this->getuser();

        // if ($user === null){
        //     return $this->redirectToRoute('security_login');
        // }

        // if($this->isGranted("ROLE_ADMIN") === false){
        //     throw new AccessDeniedException("Vous n'avez pas le droit d'accéder a cette ressource");
        // }

        $category = $categoryRepository->find($id);

        // $security->isGranted('CAN_EDIT', $category);

        // $this->denyAccessUnlessGranted('CAN_EDIT', $category, "Vous n'êtes pas le propriètaire de cette catégory");


        // $this->denyAccessUnlessGranted('CAN_EDIT', $category, "Vous n'êtes pas le propriètaire de cette catégory");

        if (!$category) {
            throw new NotFoundHttpException("cette catégorie n'existe pas");
        }

        // $user = $this->getUser();

        // if (!$user) {
        //     return $this->redirectToRoute("security_login");
        // }

        // if ($user !== $category->getOwner()) {
        //     throw new AccessDeniedHttpException("Vous n'êtes pas le propriétaire de cette catégorie");
        // }

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug(strtolower($slugger->slug($category->getName())));
            $em->flush();

            return $this->redirectToRoute('category_edit', [
                'id' => $id
            ]);
        }

        $formView = $form->createView();

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'formView' => $formView
        ]);
    }
}
