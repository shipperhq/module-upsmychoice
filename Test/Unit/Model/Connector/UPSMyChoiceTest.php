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

namespace ShipperHQ\UPSMyChoice\Test\Unit\Model\Connector;

use Fooman\PhpunitBridge\BaseUnitTestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice as UnderTest;
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\BadRequestException;
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\BadResponseException;
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\ConfigurationException;
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\Request;

class UPSMyChoiceTest extends BaseUnitTestCase
{
    /**
     * @var UnderTest
     */
    private $model;
    /**
     * @var Request
     */
    private $emptyRequest;
    /**
     * @var Request
     */
    private $validEnrollmentRequest;
    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\ShipperHQ\WS\Client\WebServiceClient
     */
    private $webServiceClientMock;

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->generateMocks($objectManager);

        $this->model = $objectManager->getObject(
            UnderTest::class,
            [
                'config' => $this->configMock,
                'webServiceClient' => $this->webServiceClientMock
            ]
        );
    }

    public function tearDown()
    {
    }

    public function testExecuteThrowsIfRequestIsInvalid()
    {
        $this->expectException(BadRequestException::class);
        $this->model->execute($this->emptyRequest);
    }

    public function testExecuteThrowsIfApiUrlMissing()
    {
        $this->expectException(ConfigurationException::class);
        $this->model->execute($this->validEnrollmentRequest);
    }

    public function testExecuteThrowsIfApiTimeoutMissing()
    {
        $this->configMock->method('getValue')->willReturn("http://example.com", null);
        $this->expectException(ConfigurationException::class);
        $this->model->execute($this->validEnrollmentRequest);
    }

    public function testExecuteThrowsIfWSCallFails()
    {
        $this->configMock->method('getValue')->willReturn("http://example.com", "30");
        $this->webServiceClientMock->method('sendAndReceive')->willReturn([
            'result' => ''
        ]);
        $this->expectException(BadResponseException::class);
        $this->model->execute($this->validEnrollmentRequest);
    }

    public function testExecuteDoesNotThrowIfSucceeds()
    {
        $this->configMock->method('getValue')->willReturn("http://example.com", "30");
        $this->webServiceClientMock->method('sendAndReceive')->willReturn([
            'result' => [
                'responseSummary' => [
                    'status' => 1
                ]
            ]
        ]);
        try {
            $this->model->execute($this->validEnrollmentRequest);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail("Unexpected throw: " . $e->getMessage());
        }
    }

    private function generateMocks(ObjectManager $objectManager): void
    {
        $this->configMock = $this->createMock(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $this->webServiceClientMock = $this->createMock(\ShipperHQ\WS\Client\WebServiceClient::class);

        $this->emptyRequest = $this->generateEmptyRequest($objectManager);
        $this->validEnrollmentRequest = $this->generateValidEnrollmentRequest($objectManager);
    }

    private function generateValidEnrollmentRequest(ObjectManager $objectManager): Request
    {
        $request = $this->generateEmptyRequest($objectManager);

        $request->setAction(Request\Action::ENROLLMENT)
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

    private function generateEmptyRequest(ObjectManager $objectManager): Request
    {
        /** @var Request $request */
        $request = $objectManager->getObject(Request::class);
        /** @var \ShipperHQ\WS\Shared\Credentials $credentials */
        $credentials = $objectManager->getObject(\ShipperHQ\WS\Shared\Credentials::class);
        /** @var \ShipperHQ\WS\Shared\SiteDetails $siteDetails */
        $siteDetails = $objectManager->getObject(\ShipperHQ\WS\Shared\SiteDetails::class);

        $request->setCredentials($credentials);
        $request->setSiteDetails($siteDetails);

        return $request;
    }
}
