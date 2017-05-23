<?php

namespace Performance\Domain\UseCase;

use Performance\Domain\ArticleRepository;
use Predis\Client as PredisClient;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ListCurrentUserTopVisitsArticles
{
    const SET_NAME = 'currentUserArticleRanking';
    const RANKING_FROM_THRESHOLD = 0;
    const RANKING_TO_THRESHOLD = 4;
    /**
     * @var ArticleRepository
     */
	private $articleRepository;

    /**
     * @var PredisClient
     */
    private $rankingRedisClient;

    /**
     * @var SessionInterface
     */
    private $session;


    public function __construct(
        ArticleRepository $articleRepository,
        PredisClient $rankingRedisClient,
        SessionInterface $session
    ) {
        $this->articleRepository = $articleRepository;
        $this->rankingRedisClient = $rankingRedisClient;
        $this->session = $session;
    }

    public function execute() {
        $topVisitsArticlesRanking = $this->rankingRedisClient->zRevRange(
            self::SET_NAME . ':' . $this->session->get('author_id'),
            self::RANKING_FROM_THRESHOLD,
            self::RANKING_TO_THRESHOLD,
            ['withscores' => false]
        );

        return array_map(function ($id) {
            return $this->articleRepository->findOneBy(['id' => $id]);
        }, $topVisitsArticlesRanking);
    }
}