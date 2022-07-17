<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/{slug}', name: 'product_category', priority: -1)]
    public function category($slug, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$category) {
            throw $this->createNotFoundException("La catégorie demandée n'existe pas !");
        }

        return $this->render('product/category.html.twig', [
            'slug' => $slug,
            'category' => $category
        ]);
    }

    #[Route('/{category_slug}/{slug}', name: 'product_show', priority: -1)]
    public function show($slug, ProductRepository $productRepository)
    {
        $product = $productRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$product) {
            throw $this->createNotFoundException("La produit demandé n'existe pas !");
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/admin/product/{id}/edit', name: 'product_edit')]

    public function edit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ValidatorInterface $validator)
    {
        // $client = [
        //     'nom' => 'tert',
        //     'prenom' => 'Lior',
        //     'voiture' => [
        //         'marque' => 'reter',
        //         'couleur' => 'Noire'
        //     ]
        // ];


        // $collection = new Collection([
        //     'nom' => new NotBlank(['message' => 'le nom ne doit pas être vide!']),
        //     'prenom' => [
        //         new NotBlank(['message' => "le prénom ne doit pas être vide!"]),
        //         new Length(['min' => 3, 'minMessage' => "le prénom ne doit pas moins de 3 caratères"])
        //     ],
        //     'voiture' => new Collection([
        //         'marque' => new NotBlank(['message' => " la marque de la voiture est obligatoire"]),
        //         'couleur' => new NotBlank(['message' => "la couleur de la voiture est obligatoire"])
        //     ])
        // ]);

        // $resultat = $validator->validate($client, $collection);


        // Validation via validator\product.yaml

        // $product = new Product;

        // $product->setName("l0l");

        // $resultat = $validator->validate($product);

        // dd($resultat);


        //validation des produits via les annotations ajout de groupe voir comment annot , une contrainte 
        // nom fait automatiquement parti du groupe "Default" , maj obligatoire
        // $product = new Product;

        // $resultat = $validator->validate($product, null, ["Default", "with-price"]);

        // dd($resultat);

        $product = $productRepository->find($id);

        $form = $this->createForm(ProductType::class, $product);

        //group validation via annot
        // $form = $this->createForm(ProductType::class, $product, [
        //     "validation_groups" => ["with-price", "Default"]
        // ]);

        // $form->setData($product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            // $product = $form->getData();
            $product->setSlug(strtolower($slugger->slug($product->getName())));
            $em->flush();

            // $url = $urlGenertator->generate('product_show', [
            //     'category_slug' => $product->getCategory()->getSlug(),
            //     'slug' => $product->getSlug()
            // ]);=>

            // $response->headers->set('location', $url);
            // $response->setStatusCode(302); =>

            // $response = new RedirectResponse($url);
            // return $response; =>

            // return $this->redirect($url); =>

            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug()
            ]);
            // dd($product);
        }

        $formView = $form->createView();

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'formView' => $formView
        ]);
    }

    #[Route('/admin/product/create', name: 'product_create')]
    public function create(FormFactoryInterface $factory, Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $product = new Product;

        $builder = $factory->createBuilder(ProductType::class, $product);

        // $builder->setAction();
        // $builder->setMethod();
        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $product = $form->getData();
            $product->setSlug(strtolower($slugger->slug($product->getName())));

            // $product = new Product;

            // $product->setName($data['name'])
            //     ->setShortDescription($data['shortDescription'])
            //     ->setPrice($data['price'])
            //     ->setCategory($data['category']);
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug()
            ]);
            // dd($product);
        }



        $formView = $form->createView();

        return $this->render('product/create.html.twig', [
            'formView' => $formView
        ]);
    }
}
