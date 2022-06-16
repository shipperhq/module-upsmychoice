<?php
/**
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_UPSMyChoice
 * @copyright Copyright (c) 2021 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\UPSMyChoice\Model\Connector;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;
use ShipperHQ\UPSMyChoice\Helper\LogAssist;
use ShipperHQ\WS\Client\WebServiceClient;
use ShipperHQ\WS\Shared\Credentials;
use ShipperHQ\WS\Shared\SiteDetails;

class UPSMyChoice
{
    const CONFIG_PATH_API_URL = 'shqupsmychoice/env/api_url';
    const CONFIG_PATH_API_TIMEOUT = 'shqupsmychoice/env/api_timeout';

    /**
     * @var WebServiceClient
     */
    private $webServiceClient;
    /**
     * @var MutableScopeConfigInterface
     */
    private $config;
    /**
     * @var LogAssist
     */
    private $shqLogger;
    /**
     * @var ProductMetadata
     */
    private $productMetadata;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * UPSMyChoice constructor.
     * @param WebServiceClient $webServiceClient
     * @param MutableScopeConfigInterface $config
     * @param LogAssist $shqLogger
     * @param ProductMetadata $productMetadata
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        WebServiceClient $webServiceClient,
        MutableScopeConfigInterface $config,
        LogAssist $shqLogger,
        ProductMetadata $productMetadata,
        StoreManagerInterface $storeManager
    ) {
        $this->webServiceClient = $webServiceClient;
        $this->config = $config;
        $this->shqLogger = $shqLogger;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
    }

    public function buildRequestForOrder(OrderInterface $order, string $action)
    {
        $request = new UPSMyChoice\Request();

        $request->setAction($action)
            ->setFirstName($order->getCustomerFirstname())
            ->setLastName($order->getCustomerLastname())
            ->setAddressLine1($order->getShippingAddress()->getStreetLine(1))
            ->setAddressLine2($order->getShippingAddress()->getStreetLine(2))
            ->setCity($order->getShippingAddress()->getCity())
            ->setStateOrProvince($order->getShippingAddress()->getRegionCode())
            ->setPostalCode($this->getSanitizedPostalCode($order))
            ->setCountry($order->getShippingAddress()->getCountryId())
            ->setEmail($order->getShippingAddress()->getEmail())
            ->setPhoneNumber($order->getShippingAddress()->getTelephone());
        $request->setCredentials($this->buildCredentials());
        $request->setSiteDetails($this->buildSiteDetails($order));

        return $request;
    }

    /**
     * @param UPSMyChoice\Request $request
     * @throws UPSMyChoice\BadRequestException
     * @throws UPSMyChoice\ConfigurationException
     * @throws UPSMyChoice\BadResponseException
     */
    public function execute(UPSMyChoice\Request $request): void
    {
        if (!$request->isValid()) {
            throw new UPSMyChoice\BadRequestException('Invalid or incomplete request body');
        }

        list($apiUrl, $apiTimeout) = $this->getApiConfig();

        $parsedResponse = $this->webServiceClient->sendAndReceive($request, $apiUrl, $apiTimeout);
        $result = $parsedResponse['result'];
        $debug = $parsedResponse['debug'] ?? '';

        if (!$this->verifyResult($result)) {
            $this->shqLogger->postCritical("ShipperHQ_UPSMyChoice", "Failed Request to UPSMyChoice API", $debug);
            throw new UPSMyChoice\BadResponseException("Invalid/Failed response from MyChoice service");
        }

        $this->shqLogger->postInfo("ShipperHQ_UPSMyChoice", "Successful Request to UPSMyChoice API", $debug);
    }

    /**
     * @return array
     * @throws UPSMyChoice\ConfigurationException
     */
    private function getApiConfig()
    {
        $apiUrl = $this->config->getValue(self::CONFIG_PATH_API_URL);
        if (!isset($apiUrl)) {
            throw new UPSMyChoice\ConfigurationException('API_URL is missing from module config');
        }

        $apiTimeout = $this->config->getValue(self::CONFIG_PATH_API_TIMEOUT);
        if (!isset($apiTimeout)) {
            throw new UPSMyChoice\ConfigurationException('API_TIMEOUT is missing from module config');
        }

        return [$apiUrl, $apiTimeout];
    }

    private function verifyResult($result)
    {
        return !empty($result) &&
            property_exists($result, 'responseSummary') &&
            property_exists($result->responseSummary, 'status') &&
            $result->responseSummary->status === 1;
    }

    private function buildCredentials(): Credentials
    {
        $apiKey = $this->getGenericSHQCarrierConfig('api_key') ?? '';
        $password = $this->getGenericSHQCarrierConfig('password') ?? '';
        return new Credentials($apiKey, $password);
    }

    private function buildSiteDetails(
        OrderInterface $order
    ): SiteDetails {
        $storeId = $order->getStoreId();
        $ipAddress = $order->getRemoteIp();
        $edition = $this->productMetadata->getEdition();
        $magentoVersion = $this->productMetadata->getVersion();
        try {
            $url = $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
        } catch (NoSuchEntityException $e) {
            $url = 'UNKNOWN';
        }

        return new SiteDetails(
            'Magento 2 ' . $edition,
            $magentoVersion,
            $url,
            $this->getGenericSHQCarrierConfig('environment_scope') ?? 'LIVE',
            $this->config->getValue('shqupsmychoice/env/extension_version'),
            $ipAddress ?? ''
        );
    }

    private function getGenericSHQCarrierConfig(string $field)
    {
        $shipperPath = "carriers/shipper/$field";
        $ecPath = "carriers/shqserver/$field";
        return $this->config->getValue($shipperPath) ?? $this->config->getValue($ecPath);
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function getSanitizedPostalCode(OrderInterface $order): string
    {
        $rawPostalCode = (string) $order->getShippingAddress()->getPostcode();
        $country = strtolower($order->getShippingAddress()->getCountryId());
        if ($country === 'us' && strlen($rawPostalCode) > 9 && strpos($rawPostalCode, '-') !== false) {
            return str_replace('-', '', $rawPostalCode);
        }
        return $rawPostalCode;
    }
}
