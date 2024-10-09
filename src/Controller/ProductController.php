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
    protected $serializer;
    protected $validator;
    protected $em;

    public function __construct(ProductRepository $productRepository, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
    }

    #[Route('/api/v1/products', name: 'get_products', methods: ['GET'])]
    /**
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->get('page') ?? 1;
        $limit = $request->query->get('limit') ?? 25;

        $products = $this->productRepository->findAllWithPagination($page, $limit);

        return  $this->successResponse('success', Response::HTTP_OK, 'The product list was returned successfully', $products);
    }

    #[Route('/api/v1/products', name: 'store_product', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "You do not have sufficient rights to create a Product")]
    /**
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json');

        $this->em->persist($product);
        $this->em->flush();

        return  $this->successResponse('success', Response::HTTP_CREATED, 'The product has been added successfully', $product);
    }


    #[Route('/api/v1/products/{id}', name: 'update_product', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: "You do not have sufficient rights to update a Product")]
    /**
     * @param Request $request
     * @param Product $product
     * 
     * @return JsonResponse
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $this->serializer->deserialize($request->getContent(), Product::class, 'json');

        // check errors exists
        $errors = $this->validator->validate($data);
        if ($errors->count() > 0) {
            $messages = [];
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return $this->errorResponse('error', Response::HTTP_BAD_REQUEST, 'Unable to update product', $messages);
        }

        $updatedProduct = $this->serializer->deserialize(
            $request->getContent(),
            Product::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $product]
        );

        $this->em->persist($updatedProduct);
        $this->em->flush();

        return  $this->successResponse('success', Response::HTTP_CREATED, 'The product has been updated successfully', $updatedProduct);
    }

    #[Route('/api/v1/products/{id}', name: 'get_product', methods: ['GET'])]
    /**
     * @param Product $product
     * 
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        return  $this->successResponse('success', Response::HTTP_OK, 'The product has been retrieved successfully', $product);
    }


    #[Route('/api/v1/products/{id}', name: 'delete_product', methods: ["DELETE"])]
    #[IsGranted('ROLE_ADMIN', message: "You do not have sufficient rights to delete a product")]
    /**
     * @param Product $product
     * 
     * @return JsonResponse
     */
    public function delete(Product $product): JsonResponse
    {
        $this->em->remove($product);
        $this->em->flush();

        return  $this->successResponse('success', Response::HTTP_NO_CONTENT, 'The product has been deleted successfully');
    }
}
