<?php

declare(strict_types=1);

namespace Ucp\Sdk\Model\Common;

/**
 * A product or variant description in one or more formats (`description.json`).
 *
 * Every format is optional in the schema, but a description with no format at all
 * carries nothing; callers that have no text should pass null to the owning model,
 * which then falls back to the title.
 */
final class Description
{
    public function __construct(
        public readonly ?string $plain = null,
        public readonly ?string $html = null,
        public readonly ?string $markdown = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->toArray() === [];
    }

    /**
     * @return array{plain?: string, html?: string, markdown?: string}
     */
    public function toArray(): array
    {
        return array_filter([
            'plain' => $this->plain,
            'html' => $this->html,
            'markdown' => $this->markdown,
        ], static fn (?string $value): bool => $value !== null && $value !== '');
    }
}
