<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Angel\Test\Model\Raffle;

use Angel\Raffle\Model\Data\Prize;
use Angel\Raffle\Model\Raffle;
use Angel\Raffle\Model\ResourceModel\Number\Collection;
use Angel\Raffle\Model\Data\Ticket;
use Angel\Test\Model\Test\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv
{
    private $filesystem;
    private $raffle;
    private $productRepository;
    protected $directory;
    private $numberCollection;
    private $productFactory;

    public function __construct(
        Filesystem $filesystem,
        Raffle $raffle,
        ProductRepository $productRepository,
        ProductFactory $productFactory,
        Collection $numberCollection
    ){
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->raffle = $raffle;
        $this->productRepository = $productRepository;
        $this->numberCollection = $numberCollection;
        $this->productFactory = $productFactory;
    }

    /**
     * @param $params
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCsvFile($params)
    {
        $name = md5(microtime());
        $file = 'export/random_numbers_' . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $product = $this->productRepository->getById($params['product_id']);
        $ticketCollection = $this->raffle->getTickets($product);
        $stream->writeCsv([__('Raffle Name'), $product->getName()]);
        $stream->writeCsv([__('Total Tickets'), (int)$product->getTotalTickets()]);
        $stream->writeCsv([__('Total Prizes'), (int)$this->raffle->getTotalPrizes($product)]);
        $stream->writeCsv([__('Total Ticket Pruchased'), (int)$ticketCollection->getLastItem()->getEnd()]);
        $randoms = $this->testGenerateRandomNumber($params['product_id'], $params['total_time']);
        if ($params['type']==Type::DISTRIBUTION){
            $randoms = $this->countWinningNumber($randoms);
        }
        foreach ($randoms as $item){
            if ($item)
                $stream->writeCsv($item);
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * @param array $winningNumbersAllTimes
     * @return array
     */
    public function countWinningNumber($winningNumbersAllTimes){
        $randoms = [];
        foreach ($winningNumbersAllTimes as $winningNumbers){
            foreach ($winningNumbers as $number){
                if (isset($randoms[$number])){
                    $randoms[$number]++;
                } else {
                    $randoms[$number] = 1;
                }
            }
        }
        $result = [];
        ksort($randoms);
        foreach ($randoms as $key => $item){
            $result[] = [$key, $item];
        }
        return $result;
    }

    /**
     * @param int $productId
     * @param int $totalTime
     * @return array
     */
    public function testGenerateRandomNumber($productId, $totalTime){
        try {
            $product = $this->productRepository->getById($productId);
            $ticketCollection = $this->raffle->getTickets($product);
            $prizes = $this->getPrizes($product);
            $result = [];
            for ($i = 0; $i<$totalTime; $i++) {
                $winningNumbers = [];
                foreach ($ticketCollection as $ticket) {
                    $this->generateWinningNumber($product, $ticket, $prizes, $winningNumbers);
                }
                $result[] = $winningNumbers;
            }
            return $result;
        } catch (\Exception $e){
            return [];
        }
    }

    public function getPrizes($product){
        $prizeCollection = $this->raffle->getPrizes($product);
        $prizes = [];
        /** @var Prize $_prize */
        foreach ($prizeCollection as $_prize){
            $prizes[] = (int)$_prize->getTotal();
        }
        return $prizes;
    }

    /**
     * @param Product $product
     * @param Ticket $ticket
     * @param array $prizes
     * @param array $winningNumbers
     * @return []
     */
    public function generateWinningNumber($product, $ticket, &$prizes, &$winningNumbers){
        $totalTickets = (int)$product->getTotalTickets();

        $existed = [];
        $totalTicketNumber = $ticket->getEnd() - $ticket->getStart() + 1;
        $count = 0;
        /** @var \Angel\Raffle\Model\Data\Prize $prize */
        foreach ($prizes as $totalPrizeLeft){
            for ($i=0; $i < $totalPrizeLeft; $i++){
                $number = $this->raffle->getRandomNumber($ticket->getStart(), $totalTickets, $existed);
                if ($number >= $ticket->getStart() && $number <= $ticket->getEnd()){
                    $winningNumbers[] = $number;
                    $count ++;
                    if ($totalTicketNumber <= $count){
                        return $winningNumbers;
                    }
                }
            }
        }
        return $winningNumbers;
    }
}
