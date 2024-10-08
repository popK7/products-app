<?php

namespace App\Controller;

use App\Entity\Product;
use App\Controller\ApiController;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends ApiController
{
    #[Route('/api/v1/products', name: 'get_products', methods: ['GET'])]
    public function index(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $products = $productRepository->findAll();

        return  $this->successResponse('success', Response::HTTP_OK, 'The product list was returned successfully', $products);
    }

    #[Route('/api/v1/products', name: 'store_product', methods: ['POST'])]
    public function store(Request $request, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $em->persist($product);
        $em->flush();

        return  $this->successResponse('success', Response::HTTP_CREATED, 'The product has been added successfully', $product);
    }

    #[Route('/api/v1/products/{id}', name: 'get_product', methods: ['GET'])]
    public function show(Product $product)
    {
        return  $this->successResponse('success', Response::HTTP_OK, 'The product has been retrieved successfully', $product);
    }

    #[Route('/api/v1/products/{id}', name: 'delete_product', methods: ["DELETE"])]
    public function delete(Product $product, EntityManagerInterface $em)
    {
        $em->remove($product);
        $em->flush();

        return  $this->successResponse('success', Response::HTTP_NO_CONTENT, 'The product has been deleted successfully');
    }
}
