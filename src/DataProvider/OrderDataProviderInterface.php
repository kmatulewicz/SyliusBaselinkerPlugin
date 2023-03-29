<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DataProvider;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderDataProviderInterface
{
    public function setOrder(OrderInterface $order): void;

    public function order_status_id(): int;

    public function custom_source_id(): int;

    public function date_add(): int;

    public function currency(): string;

    public function payment_method(): string;

    public function payment_method_cod(): bool;

    public function paid(): bool;

    public function user_comments(): string;

    public function admin_comments(): string;

    public function email(): string;

    public function phone(): string;

    public function user_login(): string;

    public function delivery_method(): string;

    public function delivery_price(): float;

    public function delivery_fullname(): string;

    public function delivery_company(): string;

    public function delivery_address(): string;

    public function delivery_postcode(): string;

    public function delivery_city(): string;

    public function delivery_state(): string;

    public function delivery_country_code(): string;

    public function delivery_point_id(): string;

    public function delivery_point_name(): string;

    public function delivery_point_address(): string;

    public function delivery_point_postcode(): string;

    public function delivery_point_city(): string;

    public function invoice_fullname(): string;

    public function invoice_company(): string;

    public function invoice_nip(): string;

    public function invoice_address(): string;

    public function invoice_postcode(): string;

    public function invoice_city(): string;

    public function invoice_state(): string;

    public function invoice_country_code(): string;

    public function want_invoice(): bool;

    public function extra_field_1(): string;

    public function extra_field_2(): string;

    public function custom_extra_fields(): array;

    public function products(): array;
}
