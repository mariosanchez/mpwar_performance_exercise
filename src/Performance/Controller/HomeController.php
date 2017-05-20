<?php

namespace Performance\Controller;

use Symfony\Component\HttpFoundation\Response;
use Performance\Domain\UseCase\ListArticles;
use Performance\Domain\UseCase\ListTopVisitsArticles;
use Moust\Silex\Cache\RedisCache;

class HomeController
{
    const REDIS_CACHE_TTL = 60;
    /**
     * @var \Twig_Environment
     */
	private $template;

    /**
     * @var RedisCache
     */
    private $cache;

    /**
     * @var ListTopVisitsArticles
     */
    private $listTopVisitsArticlesUseCase;

    public function __construct(
        \Twig_Environment $templating,
        ListArticles $useCase,
        ListTopVisitsArticles $listTopVisitsArticlesUseCase,
        RedisCache $cache
    ) {
        $this->template = $templating;
        $this->useCase = $useCase;
        $this->cache = $cache;
        $this->listTopVisitsArticlesUseCase = $listTopVisitsArticlesUseCase;
    }

    public function get()
    {
        $articles = $this->cache->fetch('articles');
        $topVisitsArticles = $this->cache->fetch('topVisitsArticles');

        if(empty($articles)) {
            $articles = $this->useCase->execute();

            foreach ($articles as $article) {
                $this->cache->store('articles:' . $article->getId(), $article, self::REDIS_CACHE_TTL);
            }
        }

        if(empty($topVisitsArticles)) {
            $topVisitsArticles = $this->listTopVisitsArticlesUseCase->execute();

            foreach ($topVisitsArticles as $topVisitsArticle) {
                $this->cache->store('topVisitsArticles:' . $topVisitsArticle->getId(), $topVisitsArticle, self::REDIS_CACHE_TTL);
            }
        }

        return new Response($this->template->render(
            'home.twig',
            [
                'articles' => $articles,
                'topVisitsArticles' => $topVisitsArticles,
            ]
        ));

    }
}