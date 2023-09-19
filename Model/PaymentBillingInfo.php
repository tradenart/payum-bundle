<?php

namespace Payum\Bundle\PayumBundle\Model;

use League\ISO3166\ISO3166;

class PaymentBillingInfo
{

    /**
     * @var string $firstName First name
     */
    public $firstName;

    /**
     * @var string $lastName Last name
     */
    public $lastName;

    /**
     * @var string $address1 Address line 1
     */
    public $address1;

    /**
     * @var string $address2 Address line 2 (optional)
     */
    public $address2;

    /**
     * @var string $zipCode Zip code
     */
    public $zipCode;

    /**
     * @var string $city City
     */
    public $city;

    /**
     * @var int $countryCode Country code (ISO 3166-1 numeric format)
     */
    public $countryCode;


    /**
     * @param $firstName
     * @param $lastName
     * @param $address1
     * @param $address2
     * @param $zipCode
     * @param $city
     * @param $countryCode
     *
     * @return PaymentBillingInfo
     *
     * Initialize the standard billing info object
     */
    public function __construct
    (
        $firstName,
        $lastName,
        $address1,
        $address2,
        $zipCode,
        $city,
        $countryCode
    )
    {
        $this->firstName = $this->enforceUtf8($firstName);
        $this->lastName = $this->enforceUtf8($lastName);
        $this->address1 = $this->enforceUtf8($address1);
        $this->address2 = $this->enforceUtf8($address2);
        $this->zipCode = $this->enforceUtf8($zipCode);
        $this->city = $this->enforceUtf8($city);
        is_numeric($countryCode) ? $this->countryCode = $countryCode : $this->setCountryCode($countryCode);

        return $this;
    }

    private function enforceUtf8(?string $string): ?string
    {
        return mb_detect_encoding($string) !== 'UTF-8' ? utf8_encode($string) : $string;
    }

    public static function unserialize(string $json): PaymentBillingInfo
    {
        $data = json_decode($json, true);

        return new PaymentBillingInfo(
            $data['firstName'],
            $data['lastName'],
            $data['address1'],
            $data['address2'],
            $data['zipCode'],
            $data['city'],
            $data['countryCode'],
        );
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
     * @return PaymentBillingInfo
     */
    public function setFirstName(string $firstName): PaymentBillingInfo
    {
        $this->firstName = utf8_encode($firstName);
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
     * @return PaymentBillingInfo
     */
    public function setLastName(string $lastName): PaymentBillingInfo
    {
        $this->lastName = utf8_encode($lastName);
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress1(): string
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     * @return PaymentBillingInfo
     */
    public function setAddress1(string $address1): PaymentBillingInfo
    {
        $this->address1 = utf8_encode($address1);
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2(): string
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     * @return PaymentBillingInfo
     */
    public function setAddress2(string $address2): PaymentBillingInfo
    {
        $this->address2 = utf8_encode($address2);
        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     * @return PaymentBillingInfo
     */
    public function setZipCode(string $zipCode): PaymentBillingInfo
    {
        $this->zipCode = utf8_encode($zipCode);
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
     * @return PaymentBillingInfo
     */
    public function setCity(string $city): PaymentBillingInfo
    {
        $this->city = utf8_encode($city);
        return $this;
    }

    /**
     * @return int
     */
    public function getCountryCode(): int
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return PaymentBillingInfo
     */
    public function setCountryCode(string $countryCode): PaymentBillingInfo
    {
        if (strlen($countryCode) == 2) {
            $data = (new ISO3166)->alpha2(strtoupper($countryCode));
        } elseif (strlen($countryCode) == 3) {
            $data = (new ISO3166)->alpha3(strtoupper($countryCode));
        } else {
            $data = (new ISO3166)->name($countryCode);
        }

        $this->countryCode = $data['numeric'];
        return $this;
    }

    public function serialize(): string
    {
        $data = [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'zipCode' => $this->zipCode,
            'city' => $this->city,
            'countryCode' => $this->countryCode
        ];

        return json_encode($data);
    }
}