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

namespace ShipperHQ\UPSMyChoice\Block\Frontend\Checkout\UPSMyChoice;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use ShipperHQ\UPSMyChoice\Api\UPSMyChoiceInterface;

// TODO: RENAME ME
class Renderer extends Template
{
    /** @var Json */
    private $serializer;
    /**
     * @var UPSMyChoiceInterface
     */
    private $upsMyChoice;
    /**
     * @var MutableScopeConfigInterface
     */
    private $config;

    /**
     * Settings constructor.
     * @param UPSMyChoiceInterface $upsMyChoice
     * @param Json $serializer
     * @param MutableScopeConfigInterface $config
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        UPSMyChoiceInterface $upsMyChoice,
        Json $serializer,
        MutableScopeConfigInterface $config,
        Context $context,
        array $data = []
    ) {
        $this->upsMyChoice = $upsMyChoice;
        $this->serializer = $serializer;
        $this->config = $config;
        parent::__construct($context, $data);
        $this->setTemplate('ShipperHQ_UPSMyChoice::checkout/upsmychoice/renderer.phtml');
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        $eligible = $this->upsMyChoice->eligibility()->getSuccess();
        $jsBundleUrl = $this->config->getValue("shqupsmychoice/env/js_bundle_url");
        $enrollmentUrl = $this->getBaseUrl() . "rest/all/V2/shipperhq/upsmychoice/enroll";

        return compact("eligible", "jsBundleUrl", "enrollmentUrl");
    }

    /**
     * @return bool|false|string
     */
    public function getSettingsAsJson()
    {
        $data = $this->getSettings();

        return $this->serializer->serialize($data);
    }
}
