<?php
/**
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Server
 * @copyright Copyright (c) 2021 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

declare(strict_types=1);

namespace ShipperHQ\UPSMyChoice\Api;

use ShipperHQ\UPSMyChoice\Api\Data\UPSMyChoiceResponseInterface;

interface UPSMyChoiceInterface
{
    /**
     * Check whether customer on this session is eligible to enroll in UPSMyChoice
     * @return \ShipperHQ\UPSMyChoice\Api\Data\UPSMyChoiceResponseInterface
     */
    public function eligibility() : UPSMyChoiceResponseInterface;

    /**
     * Invite customer on this session's last order to enroll in UPSMyChoice
     * @return \ShipperHQ\UPSMyChoice\Api\Data\UPSMyChoiceResponseInterface
     */
    public function enroll() : UPSMyChoiceResponseInterface;
}
