<?php

declare(strict_types=1);

namespace Ucp\Sdk\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ucp\Sdk\Internal\Validation\GeneratedSchemaValidator;
use Ucp\Sdk\Model\Catalog\CatalogProductResponse;
use Ucp\Sdk\Model\Catalog\Product;
use Ucp\Sdk\Model\Common\Description;

final class ProductTest extends TestCase
{
    #[Test]
    public function itCarriesTheDescriptionOnTheProductAndItsDefaultVariant(): void
    {
        $product = new Product(
            'prod-1',
            'AllWeather Scout',
            149.99,
            'https://shop.example/media/scout.png',
            [],
            'EUR',
            new Description(plain: 'A 3-season backpacking tent.', html: '<p>A <b>3-season</b> backpacking tent.</p>'),
        );

        $payload = $product->toArray();

        self::assertSame(
            ['plain' => 'A 3-season backpacking tent.', 'html' => '<p>A <b>3-season</b> backpacking tent.</p>'],
            $payload['description'],
        );
        self::assertSame($payload['description'], $payload['variants'][0]['description']);
        self::assertSame('AllWeather Scout', $payload['title']);
    }

    #[Test]
    public function itFallsBackToTheTitleWhenThereIsNoDescription(): void
    {
        // `description` is required by product.json and variant.json, so a product
        // without one still has to serialize a description. The title stands in.
        self::assertSame(['plain' => 'Trail Tee'], (new Product('prod-2', 'Trail Tee', 19.0))->toArray()['description']);
        self::assertSame(
            ['plain' => 'Trail Tee'],
            (new Product('prod-2', 'Trail Tee', 19.0, description: new Description()))->toArray()['variants'][0]['description'],
        );
    }

    #[Test]
    public function itDropsEmptyFormatsFromTheDescription(): void
    {
        $description = new Description(plain: '', html: null, markdown: 'A **tent**.');

        self::assertSame(['markdown' => 'A **tent**.'], $description->toArray());
        self::assertFalse($description->isEmpty());
        self::assertTrue((new Description(plain: ''))->isEmpty());
    }

    #[Test]
    public function itKeepsExtraAbleToOverrideTheDescription(): void
    {
        // Adapters that filled `description` through `extra` before this field existed
        // keep working: extra still wins, the same precedence Cart and Checkout use.
        $product = new Product('prod-3', 'Tee', 19.0, null, ['description' => ['plain' => 'From extra']], 'EUR', new Description(plain: 'From the field'));

        self::assertSame(['plain' => 'From extra'], $product->toArray()['description']);
    }

    #[Test]
    public function itValidatesAgainstThePinnedCatalogProductResponseSchema(): void
    {
        $validator = new GeneratedSchemaValidator(dirname(__DIR__, 2) . '/resources/schema/generated/2026-04-08');
        $response = new CatalogProductResponse(new Product(
            'prod-1',
            'AllWeather Scout',
            149.99,
            null,
            [],
            'EUR',
            new Description(plain: 'A 3-season backpacking tent.', html: '<p>A 3-season backpacking tent.</p>', markdown: 'A **3-season** backpacking tent.'),
        ));

        // The response envelope carries the protocol metadata; the transport adds it.
        $validator->validate('catalog.product.response', ['ucp' => ['version' => '2026-04-08', 'status' => 'success'], ...$response->toArray()]);

        $this->expectNotToPerformAssertions();
    }
}
