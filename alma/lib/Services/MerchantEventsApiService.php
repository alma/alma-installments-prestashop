<?php
namespace Alma\PrestaShop\Services;

use ContextCore as Context;

use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;

class MerchantEventsApiService
{
    const API_URL = 'https://a870-83-167-43-107.ngrok-free.app/merchant-events/merchant_123456789';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CartHelper
     */
    protected $cartHelper;
    /**
     * @var ProductHelper
     */
    protected $productHelper;
    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $insuranceProductRepository;

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    /**
     * @param string $eventType
     * @param array $eventDetails
     * @return array
     * @throws Exception
     */
    public function sendMerchantEvent($eventType, array $eventDetails)
    {
        // Initialisation de la session cURL
        $ch = curl_init();

        // Configuration des options cURL
        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        // Encode payload into JSON
        $jsonPayload = json_encode(['event_type' => $eventType, 'event_details' => $eventDetails]);
        $this->logger->info('$jsonPayload', [$jsonPayload]);

        // Add payload to the POST request body
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);

        // Send request / get response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Handle possible errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);

            Logger::instance()->error(
                sprintf(
                    '[Alma] Error sending merchant event %s, %s - data: %s',
                    $eventType,
                    $error_msg,
                    var_export($eventDetails, true)
                )
            );

            throw new \Exception("Erreur cURL : " . $error_msg);
        }

        curl_close($ch);

        // Decode JSON response
        $responseData = json_decode($response, true);

        if ($httpCode !== 200) {
            Logger::instance()->error(
                sprintf(
                    '[Alma] Error sending merchant event %s – HTTP status %s - data: %s – response: %s',
                    $eventType,
                    $httpCode,
                    var_export($eventDetails, true),
                    $response
                )
            );

            throw new \Exception("HTTP status {$httpCode} with response: $response");
        }

        return $responseData;
    }

}
