<?php

namespace App\Helpers;

use App\Services\S3ServiceAWS;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class Helper
{
    public static function RETURN_ERROR_FORMAT($status_code, $message = "Something is wrong !!", $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'status' => $status_code,
            'data' => $data
        ];
    }

    public static function randomNumber($min, $max): int
    {
        return rand($min, $max);
    }


    /**
     * Takes in "token ABCDEFG" and returns "ABCDEFG"
     * @param $request
     * @return string|null
     */
    public static function parseAuthorizationHeader($request): ?string
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if ($authorizationHeader != null && $authorizationHeader != "") {
            $tokenString = explode(' ', $authorizationHeader);
            return $tokenString[1];
        }

        return null;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateNumber(int $length = 6): string
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getCustomFileName($file): string
    {
        return self::generateRandomString(6) . time() . '.' . $file->getClientOriginalExtension();

    }

    public static function generateRandomString($length = 10, $pass = false)
    {
        $characters = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        if ($pass) {
            return $randomString;
        }
        return $randomString . time();
    }


    public static function Upload($file, $fileName, $customPath): array
    {
        $s3Service = new S3ServiceAWS();

        $response = $s3Service->setCustomUrl($customPath)->uploadFileToS3($file, $fileName);

        return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'File successfully upload', $response);

    }

    public static function RETURN_SUCCESS_FORMAT($statusCode, $message, $data = [], $extraData = []): array
    {
        return [
            'status' => $statusCode,
            'success' => true,
            'message' => $message,
            'data' => $data,
            'extra_data' => $extraData
        ];
    }
}
