<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Angel\Test\Model\Fifty;

use Angel\Test\Model\Fifty\Type;
use Magento\Catalog\Model\Product;
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
    private $productRepository;

    public function __construct(
        Filesystem $filesystem,
        ProductRepository $productRepository
    ){
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->productRepository = $productRepository;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile($params)
    {
        $name = md5(microtime());
        $file = 'export/random_numbers_' . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $product = $this->productRepository->getById($params['product_id']);
        $stream->writeCsv([__('50/50 Raffle Name'), $product->getName()]);
        $stream->writeCsv([__('Total Ticket Pruchased'), $product->getTypeInstance()->getLastTicketNumberByProduct($product)]);

        if ($params['type'] == Type::RANDOM_NUMBER) {
            $randoms = $this->getWinningNumbers($product, $params['total_time']);
            foreach ($randoms as $item){
                if ($item)
                    $stream->writeCsv([$item]);
            }
        } else {
            $randoms = $this->getDestribution($product, $params['total_time']);
            foreach ($randoms as $item){
                if ($item)
                    $stream->writeCsv($item);
            }
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
     * @param Product $product
     * @param int $times
     * @return array
     */
    public function getWinningNumbers($product, $times){
        $latTicketNumber = $product->getTypeInstance()->getLastTicketNumberByProduct($product);
        $winningNumbers = [];
        if ($latTicketNumber){
            for ($i = 0; $i < $times; $i++){
                $winningNumbers[] = mt_rand(1, $latTicketNumber);
            }
        }
        return $winningNumbers;
    }

    public function getDestribution($product, $time){
        $winningNumbers = $this->getWinningNumbers($product, $time);
        $randoms = [];
        foreach ($winningNumbers as $number){
            if (isset($randoms[$number])){
                $randoms[$number]++;
            } else {
                $randoms[$number] = 1;
            }
        }
        $result = [];
        ksort($randoms);
        foreach ($randoms as $key => $item){
            $result[] = [$key, $item];
        }
        return $result;
    }
}
