<?php

namespace Performance\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Performance\Domain\UseCase\ReadArticle;
use Moust\Silex\Cache\RedisCache;

class ArticleController
{
    /**
     * @var \Twig_Environment
     */
    private $template;

    /**
     * @var ReadArticle
     */
    private $useCase;

    /**
     * @var RedisCache
     */
    private $cache;

    public function __construct(
        \Twig_Environment $templating,
        ReadArticle $useCase,
        RedisCache $cache
    ) {
        $this->template = $templating;
        $this->useCase = $useCase;
        $this->cache = $cache;
    }

    public function get($article_id)
    {

        $article = $this->cache->fetch('articles:' . $article_id);

        if (!$article) {
            $article = $this->useCase->execute($article_id);
        }

        if (!$article) {
            throw new HttpException(404, "Article $article_id does not exist.");
        }

        return new Response($this->template->render('article.twig', ['article' => $article]));
    }
}