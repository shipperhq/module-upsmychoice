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

namespace ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice\Request;

class Action
{
    const ELIGIBILITY = 'eligibility';
    const ENROLLMENT = 'enrollment';

    /** @var string */
    private $value;

    /**
     * use Action::ELIGIBILITY or Action::ENROLLMENT as the possible vaulues.  Return true if value is assigned,
     * otherwise returns false.
     *
     * @param string $value
     * @return bool
     */
    public function setValue(string $value): bool
    {
        if ($this->isStringValidAction($value)) {
            $this->value = $value;
            return true;
        }
        $this->value = null;
        return false;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    public function isValid(): bool
    {
        return $this->isStringValidAction($this->value);
    }

    private function isStringValidAction(?string $str)
    {
        return $str === self::ELIGIBILITY || $str === self::ENROLLMENT;
    }
}
