<?php


namespace pwpay\group19\Model;


final class Request{

    private int $request_id;
    private int $u_requester_id;
    private int $u_requested_id;
    private float $money_requested;
    private bool $already_paid;

    /**
     * Request constructor.
     * @param int $request_id
     * @param int $u_requester_id
     * @param int $u_requested_id
     * @param float $money_requested
     * @param bool $already_paid
     */
    public function __construct(int $request_id, int $u_requester_id, int $u_requested_id, float $money_requested, bool $already_paid)
    {
        $this->request_id = $request_id;
        $this->u_requester_id = $u_requester_id;
        $this->u_requested_id = $u_requested_id;
        $this->money_requested = $money_requested;
        $this->already_paid = $already_paid;
    }

    /**
     * @return int
     */
    public function getRequestId(): int
    {
        return $this->request_id;
    }

    /**
     * @param int $request_id
     */
    public function setRequestId(int $request_id): void
    {
        $this->request_id = $request_id;
    }

    /**
     * @return int
     */
    public function getURequesterId(): int
    {
        return $this->u_requester_id;
    }

    /**
     * @param int $u_requester_id
     */
    public function setURequesterId(int $u_requester_id): void
    {
        $this->u_requester_id = $u_requester_id;
    }

    /**
     * @return int
     */
    public function getURequestedId(): int
    {
        return $this->u_requested_id;
    }

    /**
     * @param int $u_requested_id
     */
    public function setURequestedId(int $u_requested_id): void
    {
        $this->u_requested_id = $u_requested_id;
    }

    /**
     * @return float
     */
    public function getMoneyRequested(): float
    {
        return $this->money_requested;
    }

    /**
     * @param float $money_requested
     */
    public function setMoneyRequested(float $money_requested): void
    {
        $this->money_requested = $money_requested;
    }

    /**
     * @return bool
     */
    public function isAlreadyPaid(): bool
    {
        return $this->already_paid;
    }

    /**
     * @param boolean $already_paid
     */
    public function setAlreadyPaid(bool $already_paid): void
    {
        $this->already_paid = $already_paid;
    }
}