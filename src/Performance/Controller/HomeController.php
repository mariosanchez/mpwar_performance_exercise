<?php

namespace Performance\Controller;

use Symfony\Component\HttpFoundation\Response;
use Performance\Domain\UseCase\ListArticles;
use Moust\Silex\Cache\RedisCache;

class HomeController
{
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
            $this->cache->store('articles', $articles, 60);
        }

        return new Response($this->template->render('home.twig', ['articles' => $articles]));

    }
}