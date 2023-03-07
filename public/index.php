<?php
declare(strict_types=1);

use DI\Container;
use MarkdownBlog\ContentAggregator\ContentAggregatorFactory;
use MarkdownBlog\ContentAggregator\ContentAggregatorInterface;
use Mni\FrontYAML\Parser;
use Psr\Http\Message\{
    ResponseInterface as Response,
    ServerRequestInterface as Request
};
use Slim\Factory\AppFactory;
use Slim\Views\{Twig,TwigMiddleware};
use Twig\Extra\Intl\IntlExtension;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('view', function($c) {
    $twig = Twig::create(__DIR__ . '/../resources/templates');
    $twig->addExtension(new IntlExtension());
    return $twig;
});
$container->set(
    ContentAggregatorInterface::class,
    fn() => (new ContentAggregatorFactory())->__invoke([
        'path' => __DIR__ . '/../data/posts',
        'parser' => new Parser(),
    ])
);

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->add(TwigMiddleware::createFromContainer($app));

$app->map(['GET'], '/', function (Request $request, Response $response, array $args) {
    
    $view = $this->get('view');
    /** @var ContentAggregatorInterface $contentAggregator */
    $contentAggregator = $this->get(ContentAggregatorInterface::class);
    $items = $contentAggregator->getItems();
    $sorter = new \MarkdownBlog\Sorter\SortByReverseDateOrder();
    usort($items, $sorter);
    $iterator = new \MarkdownBlog\Iterator\PublishedItemFilterIterator(
        new ArrayIterator($items)
    );
    return $view->render(
        $response,
        'index.html.twig',
        ['items' => $iterator]
    );
});

$app->map(['GET'], '/item/{slug}', function (Request $request, Response $response, array $args) {
    $view = $this->get('view');
    /** @var ContentAggregatorInterface $contentAggregator */
    $contentAggregator = $this->get(ContentAggregatorInterface::class);
    return $view->render(
        $response,
        'view.html.twig',
        ['item' => $contentAggregator->findItemBySlug($args['slug'])]
    );
});

$app->run();
