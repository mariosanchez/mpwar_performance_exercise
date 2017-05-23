<?php

namespace Performance\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Performance\Domain\UseCase\ReadArticle;
use Moust\Silex\Cache\RedisCache;
use Predis\Client as PredisClient;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

    /**
     * @var SessionInterface
     */
    private $session;

    private $rankingRedisClient;

    public function __construct(
        \Twig_Environment $templating,
        ReadArticle $useCase,
        RedisCache $cache,
        PredisClient $rankingRedisClient,
        SessionInterface $session
    ) {
        $this->template = $templating;
        $this->useCase = $useCase;
        $this->cache = $cache;
        $this->rankingRedisClient = $rankingRedisClient;
        $this->session = $session;
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

        $this->rankingRedisClient->zincrby('articleRanking', 1, $article_id);

        $authorId = $this->session->get('author_id');
        if (isset($authorId)) {
            $this->rankingRedisClient->zincrby(
                'currentUserArticleRanking:' . $this->session->get('author_id')
                , 1
                , $article_id
            );
        }

        return new Response(
            $this->template->render('article.twig', ['article' => $article])
            , 200
            , array(
                'Cache-Control' => 'public, max-age=1, min-fresh=1',
            )
        );
    }
}