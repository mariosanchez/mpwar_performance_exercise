<?php

namespace Performance\Domain\UseCase;

use Performance\Domain\ArticleRepository;
use Predis\Client as PredisClient;

class ListTopVisitsArticles
{
    const SET_NAME = 'articleRangking';
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

    public function __construct(
        ArticleRepository $articleRepository,
        PredisClient $rankingRedisClient
    ) {
        $this->articleRepository = $articleRepository;
        $this->rankingRedisClient = $rankingRedisClient;
    }

    public function execute() {
        $topVisitsArticlesRanking = $this->rankingRedisClient->zRevRange(
            self::SET_NAME,
            self::RANKING_FROM_THRESHOLD,
            self::RANKING_TO_THRESHOLD,
            ['withscores' => false]
        );

        return array_map(function ($id) {
            return $this->articleRepository->findOneBy(['id' => $id]);
        }, $topVisitsArticlesRanking);
    }
}