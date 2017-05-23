<?php

namespace Performance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Performance\Domain\UseCase\EditArticle;
use Performance\Domain\UseCase\ReadArticle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Moust\Silex\Cache\RedisCache;

class EditArticleController
{
    /**
     * @var \Twig_Environment
     */
    private $template;

    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var ReadArticle
     */
    private $readArticle;

    /**
     * @var EditArticle
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
        EditArticle $useCase,
        ReadArticle $readArticle,
        SessionInterface $session,
        RedisCache $cache
    ) {
        $this->template = $templating;
        $this->url_generator = $url_generator;
        $this->readArticle = $readArticle;
        $this->useCase = $useCase;
        $this->session = $session;
        $this->cache = $cache;
    }

    public function get($article_id)
    {
        if (!$this->session->get('author_id')) {
            return new RedirectResponse($this->url_generator->generate('login'));
        }

        $article = $this->readArticle->execute($article_id);
        return new Response($this->template->render('editArticle.twig', ['article' => $article])
            , 200
            , array(
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            )
        );
    }

    public function post(Request $request)
    {
        $article   = $request->request->get('article_id');
        $title     = $request->request->get('title');
        $content   = $request->request->get('content');

        $this->useCase->execute($article, $title, $content);

        $this->cache->delete('articles:' . $request->get('article_id'));

        return new RedirectResponse(
            $this->url_generator->generate('article', ['article_id' => $request->get('article_id')])
            , 302
            , array(
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            )
        );
    }
}