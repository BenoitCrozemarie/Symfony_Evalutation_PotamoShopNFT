<?php
namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController 
{
    /////////////////////APP//////////////////////
    #[Route('/app/add-product',name:"add-product")]
    public function addProduct(Request $request, EntityManagerInterface $entityManager) : Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $product->setCreateAt(new DateTime('now'));
            $product->setUser($this->getUser());

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard-user');
        }

        return $this->render('app/formProduct.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/app/toogle-product/{productId}',name:"toggle-product")]
    public function toggleProduct($productId,ProductRepository $productRepository, EntityManagerInterface $entityManager):Response
    {
        $product = $productRepository->find($productId);
        $product->setIsOnSale(!$product->getIsOnSale());

            $entityManager->persist($product);
            $entityManager->flush();
        return $this->redirectToRoute('dashboard-user');
    }
     ////////////////////////////////////////////////

      /////////////////////PUBLIC//////////////////////
    #[Route('/public/home',name:"home")]
    public function showProduct(ProductRepository $productRepository):Response
    {
        $products = $productRepository->findBy(['isOnSale'=>true]);
        
        return $this->render('public/home.html.twig',[
            'products'=>$products
        ]);
    }
    //////////////////////////////////////////////
    /////////////////////API//////////////////////
    #[Route('/api/products',name:"api-product")]
    public function ApiProduct(ProductRepository $productRepository, SerializerInterface $serializer):Response
    {
        $products = $productRepository->findBy(['isOnSale'=>true]);

        $productsData = $serializer->serialize($products,'json',[
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH=>true,
            AbstractObjectNormalizer::CIRCULAR_REFERENCE_HANDLER=>function($object,$format,$context){
                return $object->getId();
            },
            AbstractObjectNormalizer::CIRCULAR_REFERENCE_LIMIT=>0
        ]);
        
        return new JsonResponse($productsData,200,[
            "AbstractObjectNormalizer"=>true
        ],true); //throw CircularReferenceException       
        
    }

    #[Route('/api/products/{page}',name:"api-product-pageable")]
    public function ApiProductPageable($page=1,ProductRepository $productRepository, SerializerInterface $serializer):Response
    {
        $products = $productRepository->findByExampleField(true,($page-1)*20);

        $productsData = $serializer->serialize($products,'json',[
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH=>true,
            AbstractObjectNormalizer::CIRCULAR_REFERENCE_HANDLER=>function($object,$format,$context){
                return $object->getId();
            },
            AbstractObjectNormalizer::CIRCULAR_REFERENCE_LIMIT=>0
        ]);
        
        return new JsonResponse($productsData,200,[
            "AbstractObjectNormalizer"=>true
        ],true); //throw CircularReferenceException       
        
    }
}
