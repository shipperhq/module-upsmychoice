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

declare(strict_types=1);

namespace ShipperHQ\UPSMyChoice\Model\Api;

use Magento\Checkout\Model\Session;
use ShipperHQ\UPSMyChoice\Helper\LogAssist;
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\Request\Action;
use ShipperHQ\UPSMyChoice\Api\Data\UPSMyChoiceResponseInterface;
use ShipperHQ\UPSMyChoice\Model\Api\Data\UPSMyChoiceResponseFactory;

class UPSMyChoice implements \ShipperHQ\UPSMyChoice\Api\UPSMyChoiceInterface
{

    /** @var Session */
    private $checkoutSession;

    /** @var LogAssist */
    private $shqLogger;

    /** @var \ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice */
    private $upsMyChoiceConnector;
    /**
     * @var UPSMyChoiceResponseFactory
     */
    private $upsMyChoiceConnectorResponseFactory;

    /**
     * UPSMyChoice constructor.
     * @param Session $checkoutSession
     * @param LogAssist $shqLogger
     * @param \ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice $upsMyChoiceConnector
     * @param UPSMyChoiceResponseFactory $upsMyChoiceConnectorResponseFactory
     */
    public function __construct(
        Session $checkoutSession,
        LogAssist $shqLogger,
        \ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice $upsMyChoiceConnector,
        UPSMyChoiceResponseFactory $upsMyChoiceConnectorResponseFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->shqLogger = $shqLogger;
        $this->upsMyChoiceConnector = $upsMyChoiceConnector;
        $this->upsMyChoiceConnectorResponseFactory = $upsMyChoiceConnectorResponseFactory;
    }

    public function eligibility(): UPSMyChoiceResponseInterface
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->isEmpty()) {
            $this->shqLogger->postDebug("ShipperHQ_UPSMyChoice", "Cannot check eligibility for UPSMyChoice. Session does not hold a valid Order.", '');
            return $this->upsMyChoiceConnectorResponseFactory->create(['success' => false]);
        }

        // MNB-1837 Will be null for virtual orders
        if (empty($order->getShippingMethod(true))) {
            $this->shqLogger->postDebug("ShipperHQ_UPSMyChoice", "Order appears to be virtual. Will not display UPSMyChoice", '');
            return $this->upsMyChoiceConnectorResponseFactory->create(['success' => false]);
        }

        // Ensure using a UPS carrier code - see https://regex101.com/r/oJN21j/1
        // TODO: In some VERY specific edge case configs this could fail us.  But there's no universal way to get carrierType here for both Standard and EC extensions
        $carrierCode = $order->getShippingMethod(true)->getCarrierCode();
        if (preg_match('/^(shq)?ups/i', $carrierCode) !== 1) {
            $this->shqLogger->postDebug("ShipperHQ_UPSMyChoice", "Order ineligible for UPS MyChoice; not shipping via UPS", '');
            return $this->upsMyChoiceConnectorResponseFactory->create(['success' => false]);
        }

        $request = $this->upsMyChoiceConnector->buildRequestForOrder($order, Action::ELIGIBILITY);
        try {
            $this->upsMyChoiceConnector->execute($request);
            $this->shqLogger->postInfo("ShipperHQ_UPSMyChoice", "Call for UPSMyChoice Eligibility succeeded", '');
            return $this->upsMyChoiceConnectorResponseFactory->create(['success' => true]);
        } catch (\Exception $e) {
            $this->shqLogger->postWarning(
                "ShipperHQ_UPSMyChoice",
                "Call for UPSMyChoice Eligibility failed with error",
                $e->getMessage()
            );
            return $this->upsMyChoiceConnectorResponseFactory->create(['success' => false]);
        }
    }

    public function enroll(): UPSMyChoiceResponseInterface
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->isEmpty()) {
            $this->shqLogger->postDebug("ShipperHQ_UPSMyChoice", "Cannot enroll in UPSMyChoice. Session does not hold a valid Order.", '');
            return $this->upsMyChoiceConnectorResponseFactory->create(['success' => false]);
        }

        $request = $this->upsMyChoiceConnector->buildRequestForOrder($order, Action::ENROLLMENT);
        try {
            $this->upsMyChoiceConnector->execute($request);
            $this->shqLogger->postInfo("ShipperHQ_UPSMyChoice", "Call to UPSMyChoice Enrollment succeeded", '');
            return $this->upsMyChoiceConnectorResponseFactory->create(['success' => true]);
        } catch (\Exception $e) {
            $this->shqLogger->postWarning(
                "ShipperHQ_UPSMyChoice",
                "Call to UPSMyChoice Enrollment failed with error",
                $e->getMessage()
            );
            return $this->upsMyChoiceConnectorResponseFactory->create(['success' => false]);
        }
    }
}
