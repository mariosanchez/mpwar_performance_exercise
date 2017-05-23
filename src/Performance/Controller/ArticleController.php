<?php

namespace Performance\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Performance\Domain\UseCase\ReadArticle;
use Moust\Silex\Cache\RedisCache;
use Predis\Client as PredisClient;

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

    private $rankingRedisClient;

    public function __construct(
        \Twig_Environment $templating,
        ReadArticle $useCase,
        RedisCache $cache,
        PredisClient $rankingRedisClient
    ) {
        $this->template = $templating;
        $this->useCase = $useCase;
        $this->cache = $cache;
        $this->rankingRedisClient = $rankingRedisClient;
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

        $this->rankingRedisClient->zincrby('articleRangking', 1, $article_id);

        return new Response(
            $this->template->render('article.twig', ['article' => $article])
            , 200
            , array(
                'Cache-Control' => 'public, max-age=31536000, min-fresh=60',
            )
        );
    }
}