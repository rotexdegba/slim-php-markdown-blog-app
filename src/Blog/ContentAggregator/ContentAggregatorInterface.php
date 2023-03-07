<?php

declare(strict_types=1);

namespace MarkdownBlog\ContentAggregator;

use MarkdownBlog\Entity\BlogItem;

/**
 * Description of ContentAggregatorInterface
 *
 * @author rotimi
 */
interface ContentAggregatorInterface {

    public function findItemBySlug(string $slug): ?BlogItem;
    public function getItems(): array;
}
