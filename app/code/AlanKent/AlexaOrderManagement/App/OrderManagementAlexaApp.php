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
            $sessionData->setSessionAttributes($attributes);
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

        $searchAll = $this->searchCriteriaBuilder->create();
        $allOrders = $this->orderRepository->getList($searchAll);
        $numOrders = $allOrders->getTotalCount();

        $searchPending = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::STATUS, 'Pending')
            ->create();
        $pendingOrders = $this->orderRepository->getList($searchPending);

        $text = "You have " . $this->pluralize($numOrders, "order", "orders") . ".";

        if ($numOrders > 0) {
            $numPendingOrders = $pendingOrders->getTotalCount();
            if ($numOrders == $numPendingOrders) {
                $text .= " All orders are pending.";
            } else {
                $text .= " " . $this->pluralize($numPendingOrders, "is", "are") . " pending.";
            }
        }

        $response->setResponseText($text);
        $response->setCardSimple("Store Status", $text);
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
            $response->setShouldEndSession(true);
        } else {
            $order = array_values($orders->getItems())[0]; // TODO: Why is there no getItem(index)?
            $orderId = $order->getEntityId();
            $numOrderItems = $order->getTotalItemCount();
            $itemsText = $this->pluralize($numOrderItems, "item", "items");
            $response->setResponseText("The next order is order $orderId and contains $itemsText to pick.");
            $response->setCardSimple("Order $orderId", "The next order is order $orderId and contains $itemsText to pick.");

            $attributes = $sessionData->getSessionAttributes();
            if ($attributes == null) {
                $attributes = [];
            }
            $attributes['orderId'] = (string)$orderId;
            $attributes['itemIndex'] = (string)0;
            $sessionData->setSessionAttributes($attributes);
            $response->setShouldEndSession(false);
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

        $orderItem = array_values($order->getItems())[$itemIndex]; // TODO: I expected getItem(idx)
        $itemIndex++;
        $attributes['itemIndex'] = (string)$itemIndex;

        $name = $orderItem->getName();
        $sku = $orderItem->getSku();
        $qty = floatval($orderItem->getQtyOrdered());
        $qtyText = ($qty == 1) ? "" : "Quantity $qty of";

        $response->setResponseText("Item $itemIndex of $numOrderItems. $qtyText SKU $sku, $name.");
        $response->setShouldEndSession(false);
        $sessionData->setSessionAttributes($attributes);
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

        $response->setResponseText("Order $orderId has been moved from the 'pending' to 'completed' state.");
        $response->setShouldEndSession(true);
        return $response;
    }

    /**
     * Return the number as a string, followed by the singular or plural word. For example,
     * "1 item" vs "2 items".
     * @param int $n The number.
     * @param string $singular The suffix to add if the number is 1.
     * @param string $plural The suffix to add if the number is not 1. 
     * @return string The plurized text of the form "1 item", "2 items", etc.
     */
    private function pluralize($n, $singular, $plural)
    {
        return ((string)$n) . " " . ($n == 1) ? $singular : $plural;
    }
}