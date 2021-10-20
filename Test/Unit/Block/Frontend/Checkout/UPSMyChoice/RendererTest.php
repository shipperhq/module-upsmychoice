<?php
/**
 *
 * ShipperHQ UPS MyChoice Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category ShipperHQ
 * @package ShipperHQ_UPSMyChoice
 * @copyright Copyright (c) 2021 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\UPSMyChoice\Test\Unit\Block\Frontend\Checkout\UPSMyChoice;

use Fooman\PhpunitBridge\BaseUnitTestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ShipperHQ\UPSMyChoice\Block\Frontend\Checkout\UPSMyChoice\Renderer as UnderTest;

class RendererTest extends BaseUnitTestCase
{
    /**
     * @var \ShipperHQ\UPSMyChoice\Block\Frontend\Checkout\UPSMyChoice\Renderer
     */
    private $block;

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        // Easier to use the real jsonSerializer so we can see the results in test output
        $jsonSerializer = $objectManager->getObject(\Magento\Framework\Serialize\Serializer\Json::class);

        $this->block = $objectManager->getObject(
            UnderTest::class,
            ['serializer' => $jsonSerializer]
        );
    }

    public function tearDown()
    {
    }

    public function testGetSettings()
    {
        $settings = $this->block->getSettings();
        $this->assertArrayHasKey('eligible', $settings);
        $this->assertArrayHasKey('jsBundleUrl', $settings);
        $this->assertArrayHasKey('enrollmentUrl', $settings);
    }

    public function testGetSettingsAsJson() {
        $settingsJson = $this->block->getSettingsAsJson();
        $this->assertJson($settingsJson);
        $this->assertStringContainsString('eligible', $settingsJson);
        $this->assertStringContainsString('jsBundleUrl', $settingsJson);
        $this->assertStringContainsString('enrollmentUrl', $settingsJson);
    }
}
