<?php
/**
 * Magenizr Faker
 *
 * @category    Magenizr
 * @copyright   Copyright (c) 2021 Magenizr (http://www.magenizr.com)
 * @license     https://www.magenizr.com/license Magenizr EULA
 */

namespace Magenizr\Faker\Helper\Customer;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Csv
 *
 * Load CSV and manipulate the output.
 */
class Csv extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $header = [];

    protected $column;

    protected $data;

    protected $columns;

    const FIELDS_VALIDATE = 'first_name,last_name,address>street,address>country_id,address>city,
    address>region,address>region_id,address>postcode,address>telephone,email,optional>password,
    website_id,group_id,store_id,address>is_default_billing,address>is_default_shipping';

    /**
     * Csv constructor.
     *
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->moduleReader = $moduleReader;
        $this->driverFile = $driverFile;
        $this->dir = $dir;

        parent::__construct($context);
    }

    /**
     * @param $file
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load()
    {
        // Module directory ( app or vendor )
        $controllerDir = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_CONTROLLER_DIR,
            'Magenizr_Faker'
        );

        // Data directory inside the module folder
        $dataDir = str_replace('Controller', 'Data', $controllerDir);

        $filePath = implode(DIRECTORY_SEPARATOR, [$dataDir, 'customers.csv']);

        // Override with filepath from system.xml if CSV is readable
        $fileName = $this->scopeConfig->getValue(
            'dev/magenizr_faker/csv_customers',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (! empty($fileName)) {

            $filePath = $this->driverFile->getAbsolutePath($this->dir->getRoot().DIRECTORY_SEPARATOR, $fileName);

            if (!$this->driverFile->isReadable($filePath)) {
                throw new LocalizedException(
                    __('Can\'t find provided CSV file %1.
                    Please check the field "CSV file" in the backend settings.', $filePath)
                );
            }
        }

        $this->data = $this->csvProcessor->getData($filePath);

        return $this;
    }

    public function validate()
    {
        $errors = 0;
        $fields = $this->data[0];

        foreach (explode(',', self::FIELDS_VALIDATE) as $field) {

            if (!in_array(trim($field), $fields)) {

                $errors++;
            }
        }

        return ($errors) ? false : true;
    }

    /**
     * @return array|\Magento\Framework\Phrase[]
     */
    public function getHeader()
    {
        $data = [];

        $this->header = array_merge($this->header, $this->data[0]);

        $columns = explode(',', $this->getFilterColumns());

        foreach (array_values($this->header) as $field) {

            if (in_array($field, $columns)) {
                $data[$field] = $field;
            }
        }

        $data = array_merge([__('Action')], $data);

        return $data;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $data = [];
        $header = $this->data[0];

        $i = 0;

        foreach ($this->data as $row) {

            if ($i == 0) {
                $i++;
                continue;
            }

            $data[] = array_combine($header, $row);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function getFilterColumns()
    {
        return $this->columns;
    }

    /**
     * @param $columns
     * @return $this
     */
    public function setFilterColumns($columns)
    {
        $this->columns = str_replace(' ', '', $columns);

        return $this;
    }

    /**
     * @param $str
     * @param string $method
     * @return array|string|string[]
     */
    public function toCamelCase($str, $method = '')
    {
        $str = str_replace(' ', '', ucwords(strtr($str, '_-', ' ')));

        return (! empty($method)) ? sprintf('%s%s', $method, $str) : $str;
    }

    /**
     * @param string $value
     * @param string $needles
     * @return bool
     */
    public function strposa(string $value, string $needles)
    {
        $found = false;
        $needles = explode(',', $needles);

        if (count($needles)) {

            foreach ($needles as $needle) {
                if (strpos($value, $needle) !== false) {
                    $found = true;
                    break;
                }
            }
        }

        return $found;
    }
}
