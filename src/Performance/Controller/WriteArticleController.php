<?php

namespace Performance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Performance\Domain\UseCase\WriteArticle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Moust\Silex\Cache\RedisCache;

class WriteArticleController
{
    const REDIS_CACHE_TTL = 60;
    /**
     * @var \Twig_Environment
     */
    private $template;

    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var WriteArticle
     */
    private $useCase;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RedisCache
     */
    private $cache;

    public function __construct(
        \Twig_Environment $templating,
        UrlGeneratorInterface $url_generator,
        WriteArticle $useCase,
        SessionInterface $session,
        RedisCache $cache
    ) {
        $this->template = $templating;
        $this->url_generator = $url_generator;
        $this->useCase = $useCase;
        $this->session = $session;
        $this->cache = $cache;
    }

    public function get()
    {
        if (!$this->session->get('author_id')) {
            return new RedirectResponse($this->url_generator->generate('login'));
        }

        return new Response($this->template->render('writeArticle.twig'));
    }

    public function post(Request $request)
    {
    	$title = $request->request->get('title');
    	$content = $request->request->get('content');

    	$article = $this->useCase->execute($title, $content);

        $this->cache->store('articles:'. $article->getId(), $article, self::REDIS_CACHE_TTL);

        return new RedirectResponse($this->url_generator->generate('article', ['article_id' => $article->getId()]));
    }
}