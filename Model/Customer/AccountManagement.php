<?php
/**
 * Magenizr Faker
 *
 * @category    Magenizr
 * @copyright   Copyright (c) 2021 Magenizr (http://www.magenizr.com)
 * @license     https://www.magenizr.com/license Magenizr EULA
 */

namespace Magenizr\Faker\Model\Customer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Math\Random;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magenizr\Faker\Helper\Customer\Csv;

/**
 * Class AccountManagement'
 *
 *
 */
class AccountManagement
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * AccountManagement constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magenizr\Faker\Helper\Customer\Csv $csv
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository,
        OrderRepositoryInterface $orderRepository,
        CustomerRegistry $customerRegistry,
        Random $random,
        AccountManagementInterface $customerAccountManagement,
        AddressInterfaceFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        Encryptor $encryptor,
        RegionCollectionFactory $regionCollectionFactory,
        RegionInterfaceFactory $regionDataFactory,
        CustomerInterfaceFactory $customerFactory,
        Csv $csv
    ) {
        $this->registry = $registry;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->customerRegistry = $customerRegistry;
        $this->random = $random;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->encryptor = $encryptor;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->customerFactory = $customerFactory;
        $this->csv = $csv;
    }

    /**
     * @param string $email
     * @return false|\Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerByEmail(string $email)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('email', $email)->create();

        $customer = $this->customerRepository->getList($searchCriteria)->getItems();

        if (empty($customer)) {
            return $this->customerFactory->create();
        }

        return current($customer);
    }

    /**
     * Delete a single customer.
     *
     * @param $row
     * @param $options
     * @return array|\Magento\Framework\Phrase[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete($row, $options)
    {
        $this->registry->register('isSecureArea', true, true);

        $data = [];

        $columns = explode(',', $options['columns']);

        try {

            if (! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {

                throw new LocalizedException(__('Email address %1 is invalid.', $row['email']));
            }

            $customer = $this->getCustomerByEmail($row['email']);

            if (!$customer->getId()) {
                return;
            }

            $this->customerRepository->delete($customer);

            $searchCriteria = $this->searchCriteriaBuilder->addFilter('customer_id', $customer->getId())->create();
            $orders = $this->orderRepository->getList($searchCriteria)->getItems();

            if (!empty($orders)) {

                foreach ($orders as $order) {
                    $this->orderRepository->delete($order);
                }
            }

            foreach (array_keys($row) as $field) {

                if (in_array($field, $columns)) {
                    $data[$field] = $row[$field];
                }
            }

            $data = array_merge([__('Deleted')], $data);

        } catch (\Exception $e) {
            throw new LocalizedException(__('%1', $e->getMessage()));
        }

        return $data;
    }

    /**
     * Create a single customer and return an array with the customer details.
     *
     * @param $row
     * @param $options
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($row, $options)
    {
        $data = [];

        $columns = explode(',', $options['columns']);

        try {

            if (! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {

                throw new LocalizedException(__('Email address %1 is invalid.', $row['email']));
            }

            // Create customer
            $customer = $this->getCustomerByEmail($row['email']);

            $new = (!$customer->getId()) ? true : false;

            // Set customer related values
            foreach (array_keys($row) as $field) {
                if ($this->csv->strposa($field, 'address,optional')) {
                    continue;
                }

                $method = $this->csv->toCamelCase($field, 'set');
                $value = $row[$field];

                $customer->$method($value);
            }

            $customer = $this->customerRepository->save($customer);

            // Create customer related address
            if (! $customer->getAddresses()) {

                // Create customer address
                $customerAddress = $this->addressFactory->create();
                $customerAddress->setCustomerId($customer->getId());
                $region = $row['address>region_id'];

                $regionData = $this->getRegionByCode($region, $row['address>country_id']);

                $regionName = (! empty($regionData)) ? $regionData->getState() : $row['address>region'];
                $regionId = (! empty($regionData)) ? $regionData->getId() : $row['address>region_id'];
                $countryId = (! empty($regionData)) ? $regionData->getCountryId() : $row['address>country_id'];

                $region = $this->regionDataFactory->create();
                $region->setRegion($regionName);
                $region->setRegionId($regionId);

                $customerAddress->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setRegion($region)
                    ->setCountryId($countryId)
                    ->setPostcode($row['address>postcode'])
                    ->setCity($row['address>city'])
                    ->setTelephone($row['address>telephone'])
                    ->setStreet([$row['address>street']])
                    ->setIsDefaultBilling($row['address>is_default_billing'])
                    ->setIsDefaultShipping($row['address>is_default_shipping']);

                $this->addressRepository->save($customerAddress);
            }

            if ($customer->getId()) {

                $password = $row['optional>password'];

                if (empty($password)) {
                    $password = $this->generatePassword();
                }

                $this->changePassword($customer->getEmail(), $password);

                $row['optional>password'] = $password;
            }

            foreach (array_keys($row) as $field) {

                if (in_array($field, $columns)) {
                    $data[$field] = $row[$field];
                }
            }

            $data = array_merge([($new ? __('Created') : __('Updated'))], $data);

        } catch (\Exception $e) {
            throw new LocalizedException(__('%1', $e->getMessage()));
        }

        return $data;
    }

    /**
     * @param $regionCode
     * @param string $countryId
     * @return Region
     * @throws LocalizedException
     */
    private function getRegionByCode($regionCode, $countryId = 'US')
    {
        $region = null;

        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addRegionCodeFilter($regionCode);

        foreach ($regionCollection as $regionIn) {

            if ($regionIn->getCode() == $regionCode) {
                $region = $regionIn;
            }
        }
        if (empty($region) || ! $region->getId()) {
            throw new LocalizedException(__('Cannot find region by code %1 for country %2.', $regionCode, $countryId));
        }

        return $region;
    }

    /**
     * Retrieve random password
     *
     * @param int $length
     * @return  string
     */
    public function generatePassword($length = 10)
    {
        $chars = \Magento\Framework\Math\Random::CHARS_LOWERS.
            \Magento\Framework\Math\Random::CHARS_UPPERS.
            \Magento\Framework\Math\Random::CHARS_DIGITS;

        return $this->random->getRandomString($length, $chars);
    }

    /**
     * Change customer password by Email
     *
     * @param  $email
     * @param  $password
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function changePassword($email, $password)
    {
        $customer = $this->getCustomerByEmail($email);

        $customer = $this->customerRepository->get($customer->getEmail(), $customer->getWebsiteId());
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $passwordHash = $this->encryptor->getHash($password, true);
        $customerSecure->setPasswordHash($passwordHash);
        $this->customerRepository->save($customer);
    }
}
