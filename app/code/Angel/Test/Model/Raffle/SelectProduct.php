<?php
/**
 * Angel Raffle Raffles
 * Copyright (C) 2018 Mark Wolf
 *
 * This file included in Angel/Raffle is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Angel\Test\Model\Raffle;

use Angel\Raffle\Model\Product\Attribute\Source\RaffleStatus;
use Angel\Raffle\Model\Product\Type\Raffle;
use Magento\Catalog\Model\Product;

class SelectProduct extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    private $collectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ){
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('type_id', Raffle::TYPE_ID)
            ->addAttributeToFilter('raffle_status', ['in' => [RaffleStatus::PROCESSING, RaffleStatus::FINISHED]]);
        $result = [];
        /** @var Product $product */
        foreach ($collection as $product){
            $result[] = ['value' => $product->getId() , 'label' => $product->getName()];
        }
        return $result;
    }

    /**
     * get model option as array
     *
     * @return array
     */
    public function getOptionArray()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('type_id', Raffle::TYPE_ID)
            ->addAttributeToFilter('raffle_status', ['in' => [RaffleStatus::PROCESSING, RaffleStatus::FINISHED]]);
        $result = [];
        /** @var Product $product */
        foreach ($collection as $product){
            $result[$product->getId()] = $product->getName();
        }
        return $result;
    }

}
