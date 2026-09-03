<?php

declare(strict_types=1);

namespace Ucp\Sdk\Model\Catalog;

use Ucp\Sdk\Model\Common\Description;
use Ucp\Sdk\Model\Common\MonetaryAmount;

final class Product
{
    /**
     * @param array<string, bool|float|int|string|null|array<string, bool|float|int|string|null>|list<bool|float|int|string|null>> $extra
     * @param Description|null $description the product's own description; `description` is a required
     *                                      field in `product.json` and `variant.json`, so without one the
     *                                      title stands in for it
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly float $price,
        public readonly ?string $imageUrl = null,
        public readonly array $extra = [],
        public readonly string $currency = 'EUR',
        public readonly ?Description $description = null,
    ) {
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     description: array{plain?: string, html?: string, markdown?: string},
     *     price_range: array{min: array{amount: int, currency: string}, max: array{amount: int, currency: string}},
     *     image_url?: string,
     *     variants: list<array{id: string, title: string, description: array{plain?: string, html?: string, markdown?: string}, price: array{amount: int, currency: string}}>
     * }
     */
    public function toArray(): array
    {
        $price = MonetaryAmount::fromMajorUnits($this->price, $this->currency)->toPriceArray();
        $description = $this->description !== null && !$this->description->isEmpty()
            ? $this->description->toArray()
            : ['plain' => $this->title];

        $data = array_filter([
            'id' => $this->id,
            'title' => $this->title,
            'description' => $description,
            'price_range' => [
                'min' => $price,
                'max' => $price,
            ],
            'image_url' => $this->imageUrl,
            'variants' => [[
                'id' => $this->id,
                'title' => $this->title,
                'description' => $description,
                'price' => $price,
            ]],
        ], static fn (mixed $value): bool => $value !== null);

        /** @var array{id: string, title: string, description: array{plain?: string, html?: string, markdown?: string}, price_range: array{min: array{amount: int, currency: string}, max: array{amount: int, currency: string}}, image_url?: string, variants: list<array{id: string, title: string, description: array{plain?: string, html?: string, markdown?: string}, price: array{amount: int, currency: string}}>} $payload */
        $payload = array_merge($data, $this->extra);

        return $payload;
    }
}
