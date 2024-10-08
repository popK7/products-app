<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    public function successResponse($status, $statusCode, $message, $data = null)
    {
        $response = [
            'response' => [
                'status'      => $status,
                'status_code' => $statusCode,
                'message'     => $message,
            ]
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->json($response, $statusCode);
    }

    public function errorResponse($status, $statusCode, $message, $errorDetails = null)
    {
        $response = [
            'response' => [
                'status'      => $status,
                'status_code' => $statusCode,
                'error'       => [
                    'message'   => $message,
                    'timestamp' => date('Y-m-d'),
                ],
            ]
        ];
        if ($errorDetails !== null) {
            $response['response']['error']['details'] = $errorDetails;
        }
        return $this->json($response, $statusCode);
    }
}
