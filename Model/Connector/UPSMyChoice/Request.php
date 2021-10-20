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

namespace ShipperHQ\UPSMyChoice\Model\Connector\UPSMyChoice;

use ShipperHQ\WS\AbstractWebServiceRequest;

class Request extends AbstractWebServiceRequest implements \JsonSerializable
{
    /** @var Request\Action */
    private $action;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $addressLine1;

    /** @var string */
    private $addressLine2;

    /** @var string */
    private $city;

    /** @var string | null */
    private $stateOrProvince;

    /** @var string | null */
    private $postalCode;

    /** @var string */
    private $country;

    /** @var string */
    private $email;

    /** @var string */
    private $phoneNumber;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->action = new Request\Action();
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action->getValue();
    }

    /**
     * @param string $action
     * @return Request
     */
    public function setAction(string $action): Request
    {
        $this->action->setValue($action);
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return Request
     */
    public function setFirstName(string $firstName): Request
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return Request
     */
    public function setLastName(string $lastName): Request
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine1(): string
    {
        return $this->addressLine1;
    }

    /**
     * @param string $addressLine1
     * @return Request
     */
    public function setAddressLine1(string $addressLine1): Request
    {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine2(): string
    {
        return $this->addressLine2;
    }

    /**
     * @param string $addressLine2
     * @return Request
     */
    public function setAddressLine2(string $addressLine2): Request
    {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Request
     */
    public function setCity(string $city): Request
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string | null
     */
    public function getStateOrProvince(): ?string
    {
        return $this->stateOrProvince;
    }

    /**
     * @param string | null $stateOrProvince
     * @return Request
     */
    public function setStateOrProvince(?string $stateOrProvince): Request
    {
        $this->stateOrProvince = $stateOrProvince;
        return $this;
    }

    /**
     * @return string | null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param string | null $postalCode
     * @return Request
     */
    public function setPostalCode(?string $postalCode): Request
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return Request
     */
    public function setCountry(string $country): Request
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Request
     */
    public function setEmail(string $email): Request
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return Request
     */
    public function setPhoneNumber(string $phoneNumber): Request
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function isValid(): bool
    {
        return (
            $this->action->isValid() &&
            isset($this->firstName) &&
            isset($this->lastName) &&
            isset($this->addressLine1) &&
            isset($this->addressLine2) &&
            isset($this->city) &&
            (
                isset($this->stateOrProvince) ||
                isset($this->postalCode)
            ) &&
            isset($this->country) &&
            isset($this->email) &&
            isset($this->phoneNumber) &&
            isset($this->siteDetails) &&
            isset($this->credentials)
        );
    }

    public function jsonSerialize()
    {
        return [
            'action' => $this->getAction(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'addressLine1' => $this->getAddressLine1(),
            'addressLine2' => $this->getAddressLine2(),
            'city' => $this->getCity(),
            'stateOrProvince' => $this->getStateOrProvince(),
            'postalCode' => $this->getPostalCode(),
            'country' => $this->getCountry(),
            'email' => $this->getEmail(),
            'phoneNumber' => $this->getPhoneNumber(),
            'credentials' => $this->getCredentials(),
            'siteDetails' => $this->getSiteDetails()
        ];
    }
}
