<?php

namespace App\Services;

use App\Contracts\Repositories\GatewayProviderRepository;
use App\Contracts\Services\MailContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Exception;
use SendGrid;
use SendGrid\Mail\Mail;

class MailService implements MailContract
{

    /**
     * @param $subject
     * @param $to
     * @param $name
     * @param $message
     * @return bool
     */
    public function sendMailViaSendGrid($subject, $to, $name, $message)
    {
        $fromEmail = config('api.from_address_sendgrid');
        $apiKey = "";

        $where = ['provider_type' => SENDGRID, 'status' => 1];// sendgrid provider
        $select = ['provider_credentials'];
        $sendgridData = App::make(GatewayProviderRepository::class)->getSpecificDataByWhere($where, $select);

        if ($sendgridData) {
            $provider_credentials = json_decode($sendgridData->provider_credentials);
            if (isset($provider_credentials)) {
                $fromEmail = isset($provider_credentials->sender_address) ? $provider_credentials->sender_address : "";
                $apiKey = isset($provider_credentials->api_key) ? $provider_credentials->api_key : "";
            }
        } else {
            $fromEmail = env('MAIL_FROM_ADDRESS_SENDGRID');
            $apiKey = env('MAIL_PASSWORD_SENDGRID');
        }

        return $this->sendMailWithSendgrid($fromEmail, $subject, $to, $name, $message, $apiKey);
    }


    private function sendMailWithSendgrid($from, $subject, $to, $userName, $message, $getKeyData)
    {
        $email = new Mail();
        $email->setFrom($from);
        $email->setSubject($subject);
        $email->addTo($to, $userName);
        $email->addContent(
            "text/html", $message
        );
        $sendgrid = new SendGrid($getKeyData);

        try {
            $response = $sendgrid->send($email);
            if ($response->statusCode() == 202) {
                return true;
            }
            return false;
        } catch (Exception $e) {

            Log::info(' Send mail error : ' . $e->getMessage());
            return false;
        }
    }
}
