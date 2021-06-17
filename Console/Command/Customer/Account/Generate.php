<?php
/**
 * Magenizr Faker
 *
 * @category    Magenizr
 * @copyright   Copyright (c) 2021 Magenizr (http://www.magenizr.com)
 * @license     https://www.magenizr.com/license Magenizr EULA
 */

namespace Magenizr\Faker\Console\Command\Customer\Account;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Generate
 *
 * Create customer accounts for testing purposes.
 */
class Generate extends Command
{
    const COMMAND_NAME = 'faker:account:create';

    /* Options name */
    const ARG_ACTION = 'action';

    const ARG_LIMIT = 'limit';

    const ARG_COLUMNS = 'columns';

    const ARG_FILTER = 'filter';

    const ARG_ACTION_DEFAULT = 'create';

    const ARG_COLUMNS_DEFAULT = 'first_name,last_name,email,optional>password';

    const ARG_LIMIT_DEFAULT = 5;

    /**
     * @var \Magenizr\Faker\Model\Customer\AccountManagement
     */
    protected $accountManagement;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var
     */
    protected $tableData;

    /**
     * @var
     */
    protected $options;

    /**
     * @var string[]
     */
    protected $actionAllowed = ['create', 'delete'];

    /**
     * @var \Magenizr\Faker\Helper\Tools\Arrays
     */
    protected $arraysCollection;

    /**
     * @var \Magenizr\Faker\Helper\Customer\Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(__('Create customer accounts for testing purposes.'));

        $this->addOption(
            self::ARG_ACTION,
            'a',
            InputOption::VALUE_OPTIONAL,
            __('(Optional) Action such as create or delete.'),
            self::ARG_ACTION_DEFAULT
        );

        $this->addOption(
            self::ARG_LIMIT,
            'l',
            InputOption::VALUE_OPTIONAL,
            __('(Optional) Limit the number of customers you want to create.'),
            self::ARG_LIMIT_DEFAULT
        );

        $this->addOption(
            self::ARG_COLUMNS,
            'c',
            InputOption::VALUE_OPTIONAL,
            __('(Optional) Display specific columns only.'),
            self::ARG_COLUMNS_DEFAULT
        );
        $this->addOption(
            self::ARG_FILTER,
            'f',
            InputOption::VALUE_OPTIONAL,
            __('(Optional) Filter customer data by a specific field.')
        );

        parent::configure();
    }

    /**
     * Generate constructor.
     *
     * @param \Magenizr\Faker\Helper\Tools\Arrays $arraysCollection
     * @param \Magenizr\Faker\Helper\Customer\Csv $csv
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magenizr\Faker\Model\Customer\AccountManagement $accountManagement
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magenizr\Faker\Helper\Tools\Arrays $arraysCollection,
        \Magenizr\Faker\Helper\Customer\Csv $csv,
        \Magento\Framework\Filesystem $filesystem,
        \Magenizr\Faker\Model\Customer\AccountManagement $accountManagement,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->arraysCollection = $arraysCollection;
        $this->csv = $csv;
        $this->filesystem = $filesystem;
        $this->accountManagement = $accountManagement;
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {

        $this->options = [
            'action' => $input->getOption(self::ARG_ACTION),
            'limit' => $input->getOption(self::ARG_LIMIT),
            'columns' => $input->getOption(self::ARG_COLUMNS),
            'filter' => $input->getOption(self::ARG_FILTER),
        ];

        $isEnabled = $this->scopeConfig->isSetFlag(
            'dev/magenizr_faker/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (! $isEnabled) {
            throw new LocalizedException(
                __(
                    'The module is disabled in Stores > Configuration > Advanced > Developer > Faker',
                    $this->options['action']
                )
            );
        }

        try {

            // Load CSV file
            $data = $this->csv->load();

            // Validate CSV file
            if (! $data->validate()) {
                throw new LocalizedException(__('Provided CSV file has invalid columns.', $this->options['action']));
            }

            // Load data into ArrayCollection
            $collection = $this->arraysCollection->merge($data->getAll());

            // Apply filter ( e.g --filter "address>country_id=CA" )
            $filter = explode('=', $this->options['filter']);

            if (count($filter) == 2) {

                $filter = [
                    'field' => $filter[0],
                    'value' => $filter[1],
                ];

                $collection->filter(function ($item, $key) use ($filter) {

                    if (isset($item[$filter['field']]) && ! empty($filter['value'])) {

                        if ($filter['value'][0] == '%') {

                            return strpos($item[$filter['field']], str_replace('%', '', $filter['value'])) !== false;
                        } else {

                            return strpos($item[$filter['field']], $filter['value']) === 0;
                        }
                    }
                });
            }

            // Apply limit ( e.g --limit 15 )
            if (!empty($this->options['limit'])) {
                $collection->slice(0, $this->options['limit']);
            }

            if (! $collection->count()) {

                throw new LocalizedException(__('No customer data available.'));
            }

            $header = $data->setFilterColumns($this->options['columns'])->getHeader();

            $table = new Table($output);
            $table->setHeaders($header);
            $this->tableData = [];

            $collection->each(function ($item) {

                $func = [$this->accountManagement, $this->options['action']];
                $row = $this->state->emulateAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML, $func, [
                    $item,
                    $this->options,
                ]);

                if ($row) {
                    array_push($this->tableData, $row);
                }
            });

            $table->setRows($this->tableData);
            $table->setFooterTitle(__('Result: %1', count($this->tableData)));
            $table->render();
        } catch (\Exception $e) {
            $output->writeln(__('<error>%1</error>', $e->getMessage()));
        }
    }
}
