<?php

namespace Performance\Controller;

use Symfony\Component\HttpFoundation\Response;
use Performance\Domain\UseCase\ListArticles;
use Moust\Silex\Cache\RedisCache;

class HomeController
{
    const REDIS_CACHE_TTL = 60;
    /**
     * @var \Twig_Environment
     */
	private $template;

    private $cache;

    public function __construct(\Twig_Environment $templating, ListArticles $useCase, RedisCache $cache) {
        $this->template = $templating;
        $this->useCase = $useCase;
        $this->cache = $cache;
    }

    public function get()
    {
        $articles = $this->cache->fetch('articles');

        if(empty($articles)) {
            $articles = $this->useCase->execute();

            array_map(function($article) {
                $this->cache->store('articles:' . $article->getId(), $article, self::REDIS_CACHE_TTL);
            }, $articles);
        }

        return new Response($this->template->render('home.twig', ['articles' => $articles]));

    }
}