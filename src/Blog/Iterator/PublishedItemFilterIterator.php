<?php
declare(strict_types=1);

namespace MarkdownBlog\Iterator;

use DateTime, Iterator;
use MarkdownBlog\Entity\BlogItem;

/**
 * Description of PublishedItemFilterIterator
 *
 * @author rotimi
 */
class PublishedItemFilterIterator extends \FilterIterator
{
    public function __construct(Iterator $iterator)
    {
        parent::__construct($iterator);
        $this->rewind();
    }

    public function accept(): bool
    {
        /** @var BlogItem $episode */
        $episode = $this->getInnerIterator()->current();

        return $episode->getPublishDate() <= new DateTime();
    }
}
