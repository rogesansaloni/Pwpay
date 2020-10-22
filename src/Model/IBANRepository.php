<?php

declare(strict_types=1);

namespace  pwpay\group19\Model;

interface IBANRepository{
    public function iban(string $id): string;
    public function addIban(string $id, string $iban, string $name):void;
    }