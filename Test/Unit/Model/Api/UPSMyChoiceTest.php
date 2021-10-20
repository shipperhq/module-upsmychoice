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

namespace ShipperHQ\UPSMyChoice\Test\Unit\Model\Api;

use Fooman\PhpunitBridge\BaseUnitTestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ShipperHQ\UPSMyChoice\Model\Api\UPSMyChoice as UnderTest;
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\Request;
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\Request\Action;

class UPSMyChoiceTest extends BaseUnitTestCase
{
    /**
     * @var UnderTest
     */
    private $model;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;
    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $emptyOrderMock;
    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validOrderMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice
     */
    private $upsMyChoiceConnectorMock;
    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ineligibleOrderMock;

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->generateMocks($objectManager);

        $this->model = $objectManager->getObject(
            UnderTest::class,
            [
                'checkoutSession' => $this->checkoutSessionMock,
                'upsMyChoiceConnector' => $this->upsMyChoiceConnectorMock
            ]
        );
    }

    public function tearDown()
    {
    }

    public function testThatEligibilityFailsWhenNoOrderExists()
    {
        $this->checkoutSessionMock->method('getLastRealOrder')
            ->willReturn($this->emptyOrderMock);
        $result = $this->model->eligibility();
        $this->assertNotTrue($result);
    }

    public function testThatEligibilityFailsWhenWrongCarrierUsed()
    {
        $this->checkoutSessionMock->method('getLastRealOrder')
            ->willReturn($this->ineligibleOrderMock);
        $result = $this->model->eligibility();
        $this->assertNotTrue($result);
    }

    public function testThatEligibilityFailsWhenRequestFails()
    {
        $this->checkoutSessionMock->method('getLastRealOrder')
            ->willReturn($this->validOrderMock);
        $this->upsMyChoiceConnectorMock->method('execute')
            ->willThrowException(new \Exception('failure'));
        $result = $this->model->eligibility();
        $this->assertNotTrue($result);
    }

    public function testThatEligibilitySucceedsWhenRequestSucceeds()
    {
        $this->checkoutSessionMock->method('getLastRealOrder')
            ->willReturn($this->validOrderMock);
        $this->upsMyChoiceConnectorMock->method('execute')
            ->willReturn(true);
        $result = $this->model->eligibility();
        $this->assertTrue($result);
    }

    public function testThatEnrollFailsWhenNoOrderExists()
    {
        $this->checkoutSessionMock->method('getLastRealOrder')
            ->willReturn($this->emptyOrderMock);
        $result = $this->model->enroll();
        $this->assertNotTrue($result);
    }

    public function testThatEnrollFailsWhenRequestFails()
    {
        $this->checkoutSessionMock->method('getLastRealOrder')
            ->willReturn($this->validOrderMock);
        $this->upsMyChoiceConnectorMock->method('execute')
            ->willThrowException(new \Exception('failure'));
        $result = $this->model->enroll();
        $this->assertNotTrue($result);
    }

    public function testThatEnrollSucceedsWhenRequestSucceeds()
    {
        $this->checkoutSessionMock->method('getLastRealOrder')
            ->willReturn($this->validOrderMock);
        $this->upsMyChoiceConnectorMock->method('execute')
            ->willReturn(true);
        $result = $this->model->enroll();
        $this->assertTrue($result);
    }

    private function generateMocks(ObjectManager $objectManager): void
    {
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);

        $this->upsMyChoiceConnectorMock = $this->createMock(\ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice::class);
        $this->upsMyChoiceConnectorMock->method('buildRequestForOrder')
            ->willReturn($this->generateRequest($objectManager));

        $this->emptyOrderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->emptyOrderMock->method('isEmpty')
            ->willReturn(true);

        $this->validOrderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->validOrderMock->method('isEmpty')
            ->willReturn(false);
        $this->validOrderMock->method('getShippingMethod')
            ->willReturn($this->generateMethodForCarrier('shqups1'));

        $this->ineligibleOrderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->ineligibleOrderMock->method('isEmpty')
            ->willReturn(false);
        $this->ineligibleOrderMock->method('getShippingMethod')
            ->willReturn($this->generateMethodForCarrier('shqfedex1'));
    }

    private function generateRequest(ObjectManager $objectManager): Request
    {
        /** @var Request $request */
        $request = $objectManager->getObject(Request::class);

        $request->setAction(Action::ENROLLMENT)
            ->setFirstName('testy')
            ->setLastName('McTester')
            ->setAddressLine1('123 Sesame St')
            ->setAddressLine2('')
            ->setCity('Austin')
            ->setPostalCode('78748')
            ->setStateOrProvince('TX')
            ->setCountry('US')
            ->setEmail('test@example.com')
            ->setPhoneNumber('1234567890');

        return $request;
    }

    private function generateMethodForCarrier($carrierCode)
    {
        $shippingMethod = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getCarrierCode']);
        $shippingMethod->method('getCarrierCode')
            ->willReturn($carrierCode);
        return $shippingMethod;
    }
}
