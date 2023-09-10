<?php

namespace App\Services;

use App\Contracts\Repositories\GatewayProviderRepository;
use App\Contracts\Services\AdminSettingContract;
use App\Enums\GatewayProviderStatus;
use App\Helpers\Helper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminSettingService implements AdminSettingContract
{
    private GatewayProviderRepository $gatewayProviderRepository;

    public function __construct(GatewayProviderRepository $gatewayProviderRepository)
    {
        $this->gatewayProviderRepository = $gatewayProviderRepository;
    }


    /**
     * Gateway Provider Data Update
     * @param Request $request
     * @return array
     */
    public function updateGatewayProviderData(Request $request): array
    {
        try {
            $provider_type = $request->provider_type;
            $status = isset($request->status) ? $request->status : 1;

            // Make json array
            $provider_credentials = $this->gatewayProviderMakeJsonData($request);

            $response = $this->updateGatewayProUpdateOrCreate($provider_type, $provider_credentials, $status);
            $messageSuccess = "";
            $messageFailed = "";
            if ($response) {
                if ($request->provider_type == GATEWAY_PROVIDER_TYPE_IS_SENDGRID) {
                    $messageSuccess = "Sendgrid API key setup successfully updated";
                    $messageFailed = "Sendgrid API key data updated failed";
                }
                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, $messageSuccess);
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, $messageFailed);
        } catch (Exception $e) {
            Log::error('Gateway Provider data update failed', [$e->getMessage()]);
            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Convert gateway provider into json
     * @param string $api_key
     * @param int $providerType
     * @return false|string
     */
    private function gatewayProviderMakeJsonData(Request $request): bool|string
    {
        $api_key = $request->api_key;
        $api_name = $request->api_name;
        $provider_type = $request->provider_type;
        $status = isset($request->status) ? $request->status : 1;
        $sender_address = isset($request->sender_address) ? $request->sender_address : "";

        $jsonEncodeData = [];
        if ($provider_type == GATEWAY_PROVIDER_TYPE_IS_ONESIGNAL) {
            $jsonEncodeData = [
                'api_key' => $api_key,
                'api_name' => $api_name,
            ];
        } elseif ($provider_type == GATEWAY_PROVIDER_TYPE_IS_SENDGRID) {
            $jsonEncodeData = [
                'sender_address' => $sender_address,
                'api_key' => $api_key
            ];
        }

        return json_encode($jsonEncodeData, true);
    }

    /**
     * Gateway Provider UpdateOrCreate Function
     * @param Request $request
     * @return mixed
     */
    private function updateGatewayProUpdateOrCreate(int $provider_type, $provider_credentials, int $status)
    {
        $attributes = [
            'provider_credentials' => $provider_credentials,
            'status' => $status
        ];
        $where = [
            'provider_type' => $provider_type
        ];

        return $this->gatewayProviderRepository->updateOrCreateData($where, $attributes);
    }

    /**
     * Gateway Provider Data Delete
     * @param Request $request
     * @return array
     */
    public function deleteGatewayProviderData(int $id): array
    {
        try {

            if (empty($id)) {
                return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Gateway Provider delete failed, ID is missing!');
            }

            $checkDataCount = $this->gatewayProviderRepository->checkDataCount();
            if ($checkDataCount < 2) {
                return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'You can not delete this provider!');
            }
            $where = ['id' => $id];
            $response = $this->gatewayProviderRepository->deleteWhere($where);

            if ($response) {
                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'Gateway Provider data successfully delete');
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Gateway Provider data delete failed');
        } catch (Exception $e) {
            Log::error('Gateway Provider data delete failed', [$e->getMessage()]);

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED);
        }
    }


    /**
     * Get All Gateway Provider List Data
     * @param $userId
     * @return array
     */
    public function getAllGatewayProviderListData(Request $request): array
    {
        try {

            $select = ['id', 'provider_type', 'provider_credentials', 'status', 'created_at'];

            $responseData = $this->gatewayProviderRepository->getAllProviderListWithPaginate($select, $request->query('per_page') ?? 10);

            return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'All provider list data found', $responseData);

        } catch (Exception  $e) {
            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED, 'Failed to get provider list', []);
        }
    }


    public function getAllProviderData()
    {
        $select = ['id', 'provider_type', 'provider_credentials', 'status', 'created_at'];

        return $this->gatewayProviderRepository->getAllData($select);
    }

    /**
     * Status change for specific gateway provider
     * @param int $id
     * @return array
     */
    public function statusChangeForGatewayProviderData(int $provider_type, $status = GatewayProviderStatus::STATUS__ACTIVE): array
    {
        try {

            $where = ['provider_type' => $provider_type];
            $data = ['status' => $status];
            $response = $this->gatewayProviderRepository->updateByCondition($where, $data);

            if ($response) {
                return Helper::RETURN_SUCCESS_FORMAT(Response::HTTP_OK, 'Gateway Provider status change successfully ');
            }

            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_BAD_REQUEST, 'Gateway Provider status change failed');
        } catch (Exception $e) {
            Log::error('GGateway Provider status change failed', [$e->getMessage()]);
            return Helper::RETURN_ERROR_FORMAT(Response::HTTP_EXPECTATION_FAILED);
        }
    }

}
