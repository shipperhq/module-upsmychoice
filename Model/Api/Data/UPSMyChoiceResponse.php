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

namespace ShipperHQ\UPSMyChoice\Model\Api\Data;

use ShipperHQ\UPSMyChoice\Api\Data\UPSMyChoiceResponseInterface;

class UPSMyChoiceResponse implements UPSMyChoiceResponseInterface
{
    /** @var bool */
    private $success;

    /**
     * UPSMyChoiceResponse constructor.
     * @param bool $success
     */
    public function __construct(bool $success)
    {
        $this->success = $success;
    }

    /**
     * @return bool
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return \ShipperHQ\UPSMyChoice\Api\Data\UPSMyChoiceResponseInterface
     */
    public function setSuccess(bool $success): UPSMyChoiceResponseInterface
    {
        $this->success = $success;
        return $this;
    }
}
