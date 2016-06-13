<?php

namespace AlanKent\AlexaOrderManagement\App;

use AlanKent\Alexa\App\AlexaApplicationInterface;
use AlanKent\Alexa\App\CustomerDataInterface;
use AlanKent\Alexa\App\ResponseData;
use AlanKent\Alexa\App\ResponseDataFactory;
use AlanKent\Alexa\App\SessionDataFactoryInterface;
use AlanKent\Alexa\App\SessionDataInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Application to provide various order management functionality.
 */
class OrderManagementAlexaApp implements AlexaApplicationInterface
{
    /** @var ResponseDataFactory */
    private $responseDataFactory;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * Constructor.
     * @param ResponseDataFactory $responseDataFactory
     */
    public function __construct(
        ResponseDataFactory $responseDataFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->responseDataFactory = $responseDataFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function launchRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData)
    {
        $response = $this->responseDataFactory->create();
        $response->setResponseText("What is your request?");
        $response->setShouldEndSession(false);
        return $response;
    }

    /**
     * @inheritdoc
     */
    public function intentRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData,
                                  $intentName,
                                  $slots)
    {
        if ($intentName == 'ReportOrderCount') {
            return $this->reportOrderCount();
        }

        if ($intentName == 'FindNextOrder') {
            return $this->findNextOrder($sessionData);
        }

        if ($intentName == 'FirstOrderItem') {
            $attributes = $sessionData->getSessionAttributes();
            $attributes['itemIndex'] = (string)0;
            return $this->nextOrderItem($sessionData);
        }

        if ($intentName == 'NextOrderItem') {
            return $this->nextOrderItem($sessionData);
        }

        if ($intentName == 'MarkOrderAsDone') {
            return $this->closeOrder($sessionData);
        }

        $response = $this->responseDataFactory->create();
        $response->setShouldEndSession(true);
        return $response;
    }

    /**
     * @inheritdoc
     */
    public function endSession(SessionDataInterface $sessionData,
                               CustomerDataInterface $customerData,
                               $reason)
    {
        $response = $this->responseDataFactory->create();
        $response->setResponseText("Goodbye.");
        $response->setShouldEndSession(true);
        return $response;
    }

    /**
     * Return the number of orders.
     * @return ResponseData
     */
    private function reportOrderCount()
    {
        $response = $this->responseDataFactory->create();
        $search = $this->searchCriteriaBuilder->create();
        $orders = $this->orderRepository->getList($search);
        $numOrders = $orders->getTotalCount();
        $response->setResponseText("You have $numOrders orders.");
        $response->setCardSimple("Store Status", "You currently have $numOrders orders.");
        $response->setShouldEndSession(true);
        return $response;
    }

    /**
     * Look for the next order to be processed.
     * @return ResponseData
     */
    private function findNextOrder($sessionData)
    {
        $response = $this->responseDataFactory->create();

        $search = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::STATUS, 'Pending')
            ->create();
        $orders = $this->orderRepository->getList($search);

        $numOrders = $orders->getTotalCount();
        if ($numOrders == 0) {
            $response->setResponseText("There are no orders ready for picking.");
        } else {
            $order = $orders->getItems()[0]; // TODO: Why is there no getItem(index)?
            $orderId = $order->getEntityId();
            $numOrderItems = $order->getTotalItemCount();
            $response->setResponseText("The next order is $orderId and contains $numOrderItems items to pick.");
            $response->setCardSimple("The next order is $orderId and contains $numOrderItems items to pick.");
            $sessionData['orderId'] = (string)$orderId;
            $sessionData['itemIndex'] = (string)0;
        }
        return $response;
    }

    /**
     * Look for the next order item to pick within current order.
     * @return ResponseData
     */
    private function nextOrderItem(SessionDataInterface $sessionData)
    {
        $response = $this->responseDataFactory->create();

        $attributes = $sessionData->getSessionAttributes();
        if ($attributes == null) {
            $attributes = [];
            $sessionData->setSessionAttributes($attributes);
        }
        if (!isset($attributes['orderId'])) {
            // orderId should *always* be set, but if not, redirect caller to start of flow.
            return $this->findNextOrder($sessionData);
        }
        $orderId = $attributes['orderId'];

        if (!isset($attributes['itemIndex'])) {
            // Should always be set
            return $this->findNextOrder($sessionData);
        }
        $itemIndex = $attributes['itemIndex'];

        // Fetch the order.
        $order = $this->orderRepository->get($orderId);
        $numOrderItems = $order->getTotalItemCount();

        if ($itemIndex >= $numOrderItems) {
            $response->setResponseText("There are no more items in order $orderId.");
            $response->setShouldEndSession(false);
            return $response;
        }

        $orderItem = $order->getItems()[$itemIndex]; // TODO: I expected getItem(idx)
        $itemIndex++;
        $attributes['itemIndex'] = (string)$itemIndex;

        $description = $orderItem->getDescription();
        $sku = $orderItem->getSku();
        $qty = $orderItem->getQtyOrdered();

        $response->setResponseText("Item $itemIndex of $numOrderItems. $qty of SKU $sku, $description.");
        $response->setShouldEndSession(false);
        return $response;
    }

    /**
     * Mark the order is done.
     * @param SessionDataInterface $sessionData
     * @return ResponseData
     */
    private function closeOrder(SessionDataInterface $sessionData) {
        $response = $this->responseDataFactory->create();

        $attributes = $sessionData->getSessionAttributes();
        if (!isset($attributes['orderId'])) {
            // orderId should *always* be set, but if not, redirect caller to start of flow.
            return $this->findNextOrder($sessionData);
        }
        $orderId = $attributes['orderId'];
        // Fetch the order.
        $order = $this->orderRepository->get($orderId);

        /** @var OrderInterface $order */
        $order->setStatus('Complete');
        $this->orderRepository->save($order);

        unset($attributes['orderId']);
        unset($attributes['itemIndex']);

        $response->setShouldEndSession(true);
        return $response;
    }

}