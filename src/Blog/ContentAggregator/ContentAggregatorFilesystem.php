<?php
declare(strict_types=1);

namespace MarkdownBlog\ContentAggregator;

use MarkdownBlog\Iterator\MarkdownFileFilterIterator;
use MarkdownBlog\Entity\BlogItem;
use Mni\FrontYAML\Document;
use Mni\FrontYAML\Parser;

/**
 * Description of ContentAggregatorFilesystem
 *
 * @author rotimi
 */
class ContentAggregatorFilesystem implements ContentAggregatorInterface {
    
    protected Parser $fileParser;
    protected MarkdownFileFilterIterator $fileIterator;
    private array $items = [];

    public function __construct(
        MarkdownFileFilterIterator $fileIterator,
        Parser $fileParser
    ) {
        $this->fileParser = $fileParser;
        $this->fileIterator = $fileIterator;

        $this->buildItemsList();
    }

    public function getItems(): array
    {
        return $this->items;
    }

    protected function buildItemsList(): void
    {
        foreach ($this->fileIterator as $file) {
            $article = $this->buildItemFromFile($file);
            if (! is_null($article)) {
                $this->items[] = $article;
            }
        }
    }

    public function findItemBySlug(string $slug): ?BlogItem
    {
        foreach ($this->items as $article) {
            if ($article->getSlug() === $slug) {
                return $article;
            }
        }

        return null;
    }

    public function buildItemFromFile(\SplFileInfo $file): ?BlogItem
    {
        $fileContent = file_get_contents($file->getPathname());
        $document = $this->fileParser->parse($fileContent, false);

        $item = new BlogItem();
        $item->populate($this->getItemData($document));

        return $item;
    }

    public function getItemData(Document $document): array
    {
        return [
            'publishDate' => $document->getYAML()['publish_date'] ?? '',
            'slug' => $document->getYAML()['slug'] ?? '',
            'synopsis' => $document->getYAML()['synopsis'] ?? '',
            'title' => $document->getYAML()['title'] ?? '',
            'image' => $document->getYAML()['image'] ?? '',
            'categories' => $document->getYAML()['categories'] ?? [],
            'tags' => $document->getYAML()['tags'] ?? [],
            'content' => $document->getContent(),
        ];
    }
}
