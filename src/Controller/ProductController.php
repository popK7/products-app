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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ProductController extends ApiController
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param ProductRepository $productRepository
     * 
     * @return JsonResponse
     */
    #[Route('/api/v1/products', name: 'get_products', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->get('page') ?? 1;
        $limit = $request->query->get('limit') ?? 25;

        $products = $this->productRepository->findAllWithPagination($page, $limit);

        return  $this->successResponse('success', Response::HTTP_OK, 'The product list was returned successfully', $products);
    }
    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * 
     * @return JsonResponse
     */
    #[Route('/api/v1/products', name: 'store_product', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "You do not have sufficient rights to create a Product")]
    public function store(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $em->persist($product);
        $em->flush();

        return  $this->successResponse('success', Response::HTTP_CREATED, 'The product has been added successfully', $product);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * 
     * @return JsonResponse
     */
    #[Route('/api/v1/products/{id}', name: 'update_product', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: "You do not have sufficient rights to update a Product")]
    public function update(Request $request, Product $product, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em): JsonResponse
    {
        $data = $serializer->deserialize($request->getContent(), Product::class, 'json');

        // check errors exists
        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            $messages = [];
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return $this->errorResponse('error', Response::HTTP_BAD_REQUEST, 'Unable to update product', $messages);
        }

        $updatedBook = $serializer->deserialize(
            $request->getContent(),
            Product::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $product]
        );

        $em->persist($updatedBook);
        $em->flush();

        return  $this->successResponse('success', Response::HTTP_CREATED, 'The product has been updated successfully', $updatedBook);
    }
    /**
     * @param Product $product
     * 
     * @return JsonResponse
     */
    #[Route('/api/v1/products/{id}', name: 'get_product', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return  $this->successResponse('success', Response::HTTP_OK, 'The product has been retrieved successfully', $product);
    }

    /**
     * @param Product $product
     * @param EntityManagerInterface $em
     * 
     * @return JsonResponse
     */
    #[Route('/api/v1/products/{id}', name: 'delete_product', methods: ["DELETE"])]
    #[IsGranted('ROLE_ADMIN', message: "You do not have sufficient rights to delete a book")]
    public function delete(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return  $this->successResponse('success', Response::HTTP_NO_CONTENT, 'The product has been deleted successfully');
    }
}
