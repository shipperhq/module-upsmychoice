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
use ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\Request\Action as UnderTest;

class ActionTest extends BaseUnitTestCase
{
    /**
     * @var UnderTest
     */
    private $model;

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            UnderTest::class,
        );
    }

    public function tearDown()
    {
    }

    public function testValueCanBeSetToEligibility()
    {
        $expect = UnderTest::ELIGIBILITY;
        $this->model->setValue($expect);
        $this->assertEquals($expect, $this->model->getValue());
    }

    public function testValueCanBeSetToEnrollment()
    {
        $expect = UnderTest::ENROLLMENT;
        $this->model->setValue($expect);
        $this->assertEquals($expect, $this->model->getValue());
    }

    public function testValueCanNotBeSetToUnexpectedValue()
    {
        $expect = 'foo';
        $this->model->setValue($expect);
        $this->assertNotEquals($expect, $this->model->getValue());
    }

    public function testIsValid()
    {
        $expect = UnderTest::ENROLLMENT;
        $this->model->setValue($expect);
        $this->assertTrue($this->model->isValid());

        $expect = 'foo';
        $this->model->setValue($expect);
        $this->assertNotTrue($this->model->isValid());
    }
}
